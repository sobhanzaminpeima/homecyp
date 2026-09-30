<?php

namespace App\Services\Llm;

use InvalidArgumentException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class LlmManager
{
    protected array $providers = [];

    /**
     * Resolve the active chat provider. Reads from site_settings (admin-configurable)
     * first, falling back to config/llm.php default.
     */
    public function chatProvider(): LlmProviderInterface
    {
        return $this->resolve($this->activeProviderKey('chat'));
    }

    /**
     * Resolve the active embedding provider (may differ from chat provider,
     * e.g. Anthropic for chat + NVIDIA NIM for embeddings).
     */
    public function embeddingProvider(): LlmProviderInterface
    {
        return $this->resolve($this->activeProviderKey('embedding'));
    }

    public function chat(array $messages, array $options = []): string
    {
        return $this->withFailover('chat', fn (LlmProviderInterface $provider) => $provider->chat($messages, $options));
    }

    public function embed(string $text): array
    {
        return Cache::remember('llm:embedding:'.hash('sha256', $text), now()->addDays(7),
            fn () => $this->withFailover('embedding', fn (LlmProviderInterface $provider) => $provider->embed($text))
        );
    }

    protected function withFailover(string $purpose, callable $operation): mixed
    {
        $active = $this->activeProviderKey($purpose);
        $candidates = array_values(array_unique(array_merge([$active], config("llm.{$purpose}_fallbacks", []))));
        $errors = [];

        foreach ($candidates as $key) {
            if (Cache::get("llm:circuit:{$purpose}:{$key}")) {
                continue;
            }
            try {
                return $operation($this->resolve($key));
            } catch (\Throwable $e) {
                $errors[$key] = $e->getMessage();
                Cache::put("llm:circuit:{$purpose}:{$key}", true, now()->addMinute());
                Log::warning('LLM provider failed; trying fallback', ['purpose' => $purpose, 'provider' => $key, 'error' => $e->getMessage()]);
            }
        }

        throw new RuntimeException('No '.$purpose.' provider is currently available: '.json_encode($errors));
    }

    public function resolve(string $key): LlmProviderInterface
    {
        if (isset($this->providers[$key])) {
            return $this->providers[$key];
        }

        $provider = match ($key) {
            'nvidia_nim' => new NvidiaNimProvider(),
            'openai' => new OpenAiProvider(),
            'anthropic' => new AnthropicProvider(),
            default => throw new InvalidArgumentException("Unknown LLM provider: {$key}"),
        };

        return $this->providers[$key] = $provider;
    }

    protected function activeProviderKey(string $purpose): string
    {
        $setting = \App\Models\SiteSetting::query()
            ->where('key', "llm_{$purpose}_provider")
            ->value('value');

        return $setting ?: config("llm.default_{$purpose}_provider");
    }
}
