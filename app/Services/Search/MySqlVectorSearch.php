<?php

namespace App\Services\Search;

use App\Models\KnowledgeChunk;

/**
 * Cosine-similarity search over embeddings stored as JSON in MySQL.
 *
 * Rationale: at HomeCyp's expected knowledge-base size (hundreds to low
 * thousands of chunks) computing similarity in the application layer is
 * fast enough (sub-100ms) and avoids depending on an external vector DB
 * that may be unreachable from a bare cPanel host. Swap this for a real
 * vector DB later by implementing VectorSearchInterface.
 */
class MySqlVectorSearch implements VectorSearchInterface
{
    public function similarChunks(array $queryEmbedding, int $limit = 8): array
    {
        if (empty($queryEmbedding)) {
            return [];
        }

        $queryNorm = $this->norm($queryEmbedding);
        if ($queryNorm === 0.0) {
            return [];
        }

        $scored = [];

        KnowledgeChunk::query()
            ->whereNotNull('embedding')
            ->select(['id', 'knowledge_source_id', 'full_text', 'embedding', 'chunk_index'])
            ->chunkById(200, function ($chunks) use (&$scored, $queryEmbedding, $queryNorm) {
                foreach ($chunks as $chunk) {
                    $vector = $chunk->embedding;
                    if (!is_array($vector) || empty($vector)) {
                        continue;
                    }

                    $score = $this->cosineSimilarity($queryEmbedding, $vector, $queryNorm);
                    $scored[] = ['chunk' => $chunk, 'score' => $score];
                }
            });

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, $limit);
    }

    protected function cosineSimilarity(array $a, array $b, float $normA): float
    {
        $dot = 0.0;
        $normB = 0.0;
        $len = min(count($a), count($b));

        for ($i = 0; $i < $len; $i++) {
            $dot += $a[$i] * $b[$i];
            $normB += $b[$i] * $b[$i];
        }

        $normB = sqrt($normB);
        if ($normB === 0.0) {
            return 0.0;
        }

        return $dot / ($normA * $normB);
    }

    protected function norm(array $vector): float
    {
        $sum = 0.0;
        foreach ($vector as $v) {
            $sum += $v * $v;
        }
        return sqrt($sum);
    }
}
