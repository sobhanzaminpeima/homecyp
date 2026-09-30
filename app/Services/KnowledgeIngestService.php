<?php

namespace App\Services;

use App\Models\KnowledgeChunk;
use App\Models\KnowledgeSource;
use App\Services\Llm\LlmManager;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Extracts text, chunks it, and embeds each chunk (spec 5.2). Runs synchronously —
 * fine for the document sizes an admin uploads one at a time; move to a queued job
 * later if volume grows (the interface here doesn't need to change for that).
 */
class KnowledgeIngestService
{
    protected const CHUNK_SIZE = 1000; // characters, roughly a few hundred tokens
    protected const CHUNK_OVERLAP = 150;

    public function __construct(protected LlmManager $llm)
    {
    }

    public function process(KnowledgeSource $source): void
    {
        $source->update(['status' => 'processing', 'error_message' => null]);

        try {
            $text = $this->extractText($source);

            if (trim($text) === '') {
                throw new \RuntimeException('No text could be extracted from this source.');
            }

            $source->chunks()->delete();

            foreach ($this->chunk($text) as $i => $chunkText) {
                $embedding = [];
                try {
                    $embedding = $this->llm->embeddingProvider()->embed($chunkText);
                } catch (Throwable $e) {
                    // Keep the chunk for FULLTEXT keyword search even if embeddings are
                    // unavailable right now (e.g. no API key configured yet) — hybrid
                    // search degrades to keyword-only for this chunk rather than failing.
                }

                KnowledgeChunk::create([
                    'knowledge_source_id' => $source->id,
                    'full_text' => $chunkText,
                    'embedding' => $embedding ?: null,
                    'embedding_model' => $embedding ? $this->llm->embeddingProvider()->getName() : null,
                    'chunk_index' => $i,
                ]);
            }

            $source->update(['status' => 'indexed']);
        } catch (Throwable $e) {
            $source->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
        }
    }

    protected function extractText(KnowledgeSource $source): string
    {
        return match ($source->type) {
            'manual' => $source->content ?? '',
            'url' => $this->fetchUrl($source->source_url),
            'pdf', 'doc' => throw new \RuntimeException(
                'PDF/Word text extraction requires a parser package (not installed in this environment). '
                .'Use the "Manual text" or "Web page" source type for now, or add a PDF/DOC parser and re-process.'
            ),
            default => '',
        };
    }

    protected function fetchUrl(?string $url): string
    {
        if (!$url) {
            return '';
        }

        $response = \Illuminate\Support\Facades\Http::timeout(15)->get($url);
        $html = $response->body();

        $text = preg_replace('/<script\b[^>]*>.*?<\/script>/is', ' ', $html);
        $text = preg_replace('/<style\b[^>]*>.*?<\/style>/is', ' ', $text);
        $text = strip_tags($text);
        $text = preg_replace('/\s+/u', ' ', $text);

        return trim($text);
    }

    /**
     * @return string[]
     */
    protected function chunk(string $text): array
    {
        $chunks = [];
        $length = mb_strlen($text);
        $start = 0;

        while ($start < $length) {
            $chunks[] = trim(mb_substr($text, $start, self::CHUNK_SIZE));
            $start += self::CHUNK_SIZE - self::CHUNK_OVERLAP;
        }

        return array_values(array_filter($chunks, fn ($c) => $c !== ''));
    }
}
