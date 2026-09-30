<?php

namespace App\Services\Llm;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AnthropicProvider implements LlmProviderInterface
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $chatModel;

    public function __construct(?array $config = null)
    {
        $config ??= config('llm.providers.anthropic');

        $this->baseUrl = rtrim($config['base_url'] ?? 'https://api.anthropic.com/v1', '/');
        $this->apiKey = $config['api_key'] ?? '';
        $this->chatModel = $config['chat_model'] ?? 'claude-sonnet-5';
    }

    public function chat(array $messages, array $options = []): string
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('Anthropic API key is not configured.');
        }

        @ini_set('max_execution_time', '90');

        $system = '';
        $conversational = [];
        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                $system .= ($system ? "\n" : '').$message['content'];
                continue;
            }
            $conversational[] = $message;
        }

        $payload = [
            'model' => $options['model'] ?? $this->chatModel,
            'system' => $system,
            'messages' => $conversational,
            'max_tokens' => $options['max_tokens'] ?? 1024,
            'temperature' => $options['temperature'] ?? 0.4,
        ];

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
        ])->timeout(25)->post("{$this->baseUrl}/messages", $payload); // See NvidiaNimProvider: kept under PHP's max_execution_time.

        if ($response->failed()) {
            throw new RuntimeException('Anthropic chat request failed: '.$response->body());
        }

        return $response->json('content.0.text') ?? '';
    }

    public function embed(string $text): array
    {
        // Anthropic has no native embeddings endpoint; embeddings must come from
        // another configured provider (see EmbeddingProviderInterface fallback in LlmManager).
        throw new RuntimeException('Anthropic provider does not support embeddings. Configure a separate embedding provider.');
    }

    public function getName(): string
    {
        return 'anthropic';
    }
}
