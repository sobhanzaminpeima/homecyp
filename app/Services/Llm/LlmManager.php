<?php

namespace App\Services\Llm;

use InvalidArgumentException;

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
