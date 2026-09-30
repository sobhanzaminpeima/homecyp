<?php

return [
    // Which provider handles chat/completions and embeddings by default.
    // Overridable at runtime from the admin panel (site_settings: llm_chat_provider, llm_embedding_provider).
    'default_chat_provider' => env('LLM_CHAT_PROVIDER', 'nvidia_nim'),
    'default_embedding_provider' => env('LLM_EMBEDDING_PROVIDER', 'nvidia_nim'),
    'chat_fallbacks' => array_values(array_filter(explode(',', env('LLM_CHAT_FALLBACKS', 'openai,anthropic')))),
    'embedding_fallbacks' => array_values(array_filter(explode(',', env('LLM_EMBEDDING_FALLBACKS', 'openai')))),

    'providers' => [
        'nvidia_nim' => [
            'base_url' => env('NVIDIA_NIM_BASE_URL', 'https://integrate.api.nvidia.com/v1'),
            'api_key' => env('NVIDIA_NIM_API_KEY'),
            'chat_model' => env('NVIDIA_NIM_CHAT_MODEL', 'meta/llama-3.1-70b-instruct'),
            'embed_model' => env('NVIDIA_NIM_EMBED_MODEL', 'nvidia/nemotron-3-embed-1b'),
        ],
        'openai' => [
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'api_key' => env('OPENAI_API_KEY'),
            'chat_model' => env('OPENAI_CHAT_MODEL', 'gpt-4o-mini'),
            'embed_model' => env('OPENAI_EMBED_MODEL', 'text-embedding-3-small'),
        ],
        'anthropic' => [
            'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1'),
            'api_key' => env('ANTHROPIC_API_KEY'),
            'chat_model' => env('ANTHROPIC_CHAT_MODEL', 'claude-sonnet-5'),
        ],
    ],
];
