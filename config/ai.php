<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Geração de questões por IA
    |--------------------------------------------------------------------------
    |
    | Desligada por padrão: com "enabled" falso (ou sem chave) o container
    | entrega o UnavailableQuestionGenerator e o botão "Gerar com IA" do painel
    | fica desativado. Tudo que a IA gera nasce com review_status = pending.
    |
    */

    'questions' => [
        'enabled' => (bool) env('AI_QUESTIONS_ENABLED', false),

        /** Teto de questões por chamada, para limitar custo e o tamanho da resposta. */
        'max_per_request' => (int) env('AI_QUESTIONS_MAX_PER_REQUEST', 20),
    ],

    /** Provedor usado na geração: "anthropic" (API direta) ou "openrouter". */
    'provider' => env('AI_PROVIDER', 'anthropic'),

    'openrouter' => [
        'key' => env('OPENROUTER_API_KEY'),
        'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),

        /** Slug do modelo no OpenRouter (veja openrouter.ai/models), ex.: "openai/gpt-4o-mini". */
        'model' => env('OPENROUTER_MODEL', 'anthropic/claude-sonnet-4.5'),
        'max_tokens' => (int) env('OPENROUTER_MAX_TOKENS', 4096),
        'timeout' => (int) env('OPENROUTER_TIMEOUT', 90),

        /** Opcionais: identificam a aplicação nos rankings do OpenRouter. */
        'referer' => env('OPENROUTER_REFERER', env('APP_URL')),
        'title' => env('OPENROUTER_TITLE', 'UniFocus'),
    ],

    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
        'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com'),
        'version' => '2023-06-01',
        'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-5-5'),
        'max_tokens' => (int) env('ANTHROPIC_MAX_TOKENS', 4096),
        'timeout' => (int) env('ANTHROPIC_TIMEOUT', 90),

        /** Centavos por milhão de tokens, para o cost_cents de ai_generations. Ajuste conforme o modelo. */
        'pricing' => [
            'input_cents_per_million' => (int) env('ANTHROPIC_INPUT_CENTS_PER_MILLION', 300),
            'output_cents_per_million' => (int) env('ANTHROPIC_OUTPUT_CENTS_PER_MILLION', 1500),
        ],
    ],

];
