<?php

namespace App\Services\Llm;

interface LlmProviderInterface
{
    /**
     * Send a chat completion request and get back the raw assistant text.
     *
     * @param array $messages [['role' => 'system'|'user'|'assistant', 'content' => string], ...]
     * @param array $options e.g. ['temperature' => 0.4, 'response_format' => 'json', 'model' => '...']
     */
    public function chat(array $messages, array $options = []): string;

    /**
     * Return an embedding vector for the given text.
     *
     * @return float[]
     */
    public function embed(string $text): array;

    public function getName(): string;
}
