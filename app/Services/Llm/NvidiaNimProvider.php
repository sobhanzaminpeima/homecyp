<?php

namespace App\Services\Llm;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class NvidiaNimProvider implements LlmProviderInterface
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $chatModel;
    protected string $embedModel;

    public function __construct(?array $config = null)
    {
        $config ??= config('llm.providers.nvidia_nim');

        $this->baseUrl = rtrim($config['base_url'] ?? 'https://integrate.api.nvidia.com/v1', '/');
        $this->apiKey = $config['api_key'] ?? '';
        $this->chatModel = $config['chat_model'] ?? 'meta/llama-3.1-70b-instruct';
        $this->embedModel = $config['embed_model'] ?? 'nvidia/nv-embedqa-e5-v5';
    }

    public function chat(array $messages, array $options = []): string
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('NVIDIA NIM API key is not configured.');
        }

        // Defense in depth alongside the shortened HTTP timeout below: give PHP's own
        // execution limit headroom so it never races the HTTP client's timeout again.
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
            ->timeout(25) // Kept well under PHP's max_execution_time (60s) so a slow/exhausted
            // API throws a catchable Guzzle timeout instead of racing into an uncatchable
            // fatal execution-time crash (both were ~60s, which caused blank 500 responses).
            ->post("{$this->baseUrl}/chat/completions", $payload);

        if ($response->failed()) {
            throw new RuntimeException('NVIDIA NIM chat request failed: '.$response->body());
        }

        return $response->json('choices.0.message.content') ?? '';
    }

    public function embed(string $text): array
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('NVIDIA NIM API key is not configured.');
        }

        @ini_set('max_execution_time', '90');

        $response = Http::withToken($this->apiKey)
            ->timeout(25)
            ->post("{$this->baseUrl}/embeddings", [
                'model' => $this->embedModel,
                'input' => [$text],
                'input_type' => 'passage',
            ]);

        if ($response->failed()) {
            throw new RuntimeException('NVIDIA NIM embedding request failed: '.$response->body());
        }

        return $response->json('data.0.embedding') ?? [];
    }

    public function getName(): string
    {
        return 'nvidia_nim';
    }
}
