<?php

namespace App\Services\Search;

use App\Models\KnowledgeChunk;
use App\Services\Llm\LlmManager;
use Illuminate\Support\Facades\DB;

class HybridSearchService
{
    public function __construct(
        protected LlmManager $llm,
        protected VectorSearchInterface $vectorSearch,
    ) {
    }

    /**
     * Merge vector similarity and MySQL FULLTEXT keyword search, re-rank, and
     * return the top $limit knowledge chunks as context for the LLM prompt.
     *
     * @return array<int, array{text: string, source_id: int, score: float}>
     */
    public function search(string $query, int $limit = 6): array
    {
        $vectorResults = $this->vectorResults($query);
        $keywordResults = $this->keywordResults($query);

        $merged = [];

        foreach ($vectorResults as $r) {
            $id = $r['chunk']->id;
            $merged[$id] = [
                'chunk' => $r['chunk'],
                // vector similarity weighted higher: it captures semantic intent
                'score' => $r['score'] * 0.7,
            ];
        }

        foreach ($keywordResults as $r) {
            $id = $r['chunk']->id;
            if (isset($merged[$id])) {
                $merged[$id]['score'] += $r['score'] * 0.3;
            } else {
                $merged[$id] = [
                    'chunk' => $r['chunk'],
                    'score' => $r['score'] * 0.3,
                ];
            }
        }

        uasort($merged, fn ($a, $b) => $b['score'] <=> $a['score']);

        return collect(array_slice($merged, 0, $limit))
            ->map(fn ($r) => [
                'text' => $r['chunk']->full_text,
                'source_id' => $r['chunk']->knowledge_source_id,
                'score' => round($r['score'], 4),
            ])
            ->values()
            ->all();
    }

    protected function vectorResults(string $query): array
    {
        try {
            $embedding = $this->llm->embeddingProvider()->embed($query);
        } catch (\Throwable $e) {
            report($e);
            return [];
        }

        return $this->vectorSearch->similarChunks($embedding, 10);
    }

    protected function keywordResults(string $query): array
    {
        $rows = KnowledgeChunk::query()
            ->select(['id', 'knowledge_source_id', 'full_text', 'chunk_index'])
            ->selectRaw('MATCH(full_text) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance', [$query])
            ->whereRaw('MATCH(full_text) AGAINST(? IN NATURAL LANGUAGE MODE)', [$query])
            ->orderByDesc('relevance')
            ->limit(10)
            ->get();

        $maxRelevance = $rows->max('relevance') ?: 1;

        return $rows->map(fn ($chunk) => [
            'chunk' => $chunk,
            'score' => $chunk->relevance / $maxRelevance,
        ])->all();
    }
}
