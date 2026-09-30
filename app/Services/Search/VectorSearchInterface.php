<?php

namespace App\Services\Search;

interface VectorSearchInterface
{
    /**
     * @param float[] $queryEmbedding
     * @return array<int, array{chunk: \App\Models\KnowledgeChunk, score: float}>
     */
    public function similarChunks(array $queryEmbedding, int $limit = 8): array;
}
