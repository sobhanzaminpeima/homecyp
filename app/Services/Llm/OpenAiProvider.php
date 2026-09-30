<?php

namespace App\Services\Llm;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiProvider implements LlmProviderInterface
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $chatModel;
    protected string $embedModel;

    public function __construct(?array $config = null)
    {
        $config ??= config('llm.providers.openai');

        $this->baseUrl = rtrim($config['base_url'] ?? 'https://api.openai.com/v1', '/');
        $this->apiKey = $config['api_key'] ?? '';
        $this->chatModel = $config['chat_model'] ?? 'gpt-4o-mini';
        $this->embedModel = $config['embed_model'] ?? 'text-embedding-3-small';
    }

    public function chat(array $messages, array $options = []): string
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('OpenAI API key is not configured.');
        }

        @ini_set('max_execution_time', '90');

        $payload = [
            'model' => $options['model'] ?? $this->chatModel,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.4,
            'max_tokens' => $options['max_tokens'] ?? 1024,
        ];

        if (($options['response_format'] ?? null) === 'json') {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(25) // See NvidiaNimProvider: kept under PHP's max_execution_time.
            ->post("{$this->baseUrl}/chat/completions", $payload);

        if ($response->failed()) {
            throw new RuntimeException('OpenAI chat request failed: '.$response->body());
        }

        return $response->json('choices.0.message.content') ?? '';
    }

    public function embed(string $text): array
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('OpenAI API key is not configured.');
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(30)
            ->post("{$this->baseUrl}/embeddings", [
                'model' => $this->embedModel,
                'input' => $text,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('OpenAI embedding request failed: '.$response->body());
        }

        return $response->json('data.0.embedding') ?? [];
    }

    public function getName(): string
    {
        return 'openai';
    }
}
