<?php

/*
|--------------------------------------------------------------------------
| AI Provider Configuration
|--------------------------------------------------------------------------
|
| Central registry of supported AI providers, their default models for
| model-rotation (simple/complex), and the system-level API keys.
|
| BYOK (Bring Your Own Key) flow: tenants may supply their own API keys
| for a given provider; in that case the AiRequest carries `apiKeyOverride`
| and the system-level key here is ignored. See FAZ 3.3 roadmap entry.
|
*/

return [

    /*
    |----------------------------------------------------------------------
    | Default provider used when caller does not specify one explicitly.
    |----------------------------------------------------------------------
    */
    'default_provider' => env('AI_DEFAULT_PROVIDER', 'anthropic'),

    /*
    |----------------------------------------------------------------------
    | Model rotation — global hints
    |----------------------------------------------------------------------
    |
    | Each endpoint asks the AiManager for either `simple` or `complex`
    | tier; the manager resolves the actual model name from the chosen
    | provider's `models` map below.
    |
    | Tiers are intentionally provider-neutral so callers can stay agnostic
    | (e.g. `simple` could be Haiku on Anthropic, gpt-4o-mini on OpenAI).
    |
    */
    'tiers' => [
        'simple'  => 'simple',
        'complex' => 'complex',
    ],

    /*
    |----------------------------------------------------------------------
    | Request defaults
    |----------------------------------------------------------------------
    */
    'defaults' => [
        'max_tokens'  => (int) env('AI_DEFAULT_MAX_TOKENS', 1024),
        'temperature' => (float) env('AI_DEFAULT_TEMPERATURE', 0.7),
        'timeout'     => (int) env('AI_TIMEOUT', 60),
    ],

    /*
    |----------------------------------------------------------------------
    | Providers
    |----------------------------------------------------------------------
    */
    'providers' => [

        'anthropic' => [
            'driver'      => 'anthropic',
            'api_key'     => env('ANTHROPIC_API_KEY'),
            'base_url'    => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1'),
            'api_version' => env('ANTHROPIC_API_VERSION', '2023-06-01'),
            'models' => [
                'simple'  => env('ANTHROPIC_MODEL_SIMPLE', 'claude-haiku-4-5'),
                'complex' => env('ANTHROPIC_MODEL_COMPLEX', 'claude-sonnet-4-6'),
            ],
        ],

        'openai' => [
            'driver'   => 'openai',
            'api_key'  => env('OPENAI_API_KEY'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'organization' => env('OPENAI_ORGANIZATION'),
            'models' => [
                'simple'  => env('OPENAI_MODEL_SIMPLE', 'gpt-4o-mini'),
                'complex' => env('OPENAI_MODEL_COMPLEX', 'gpt-4o'),
            ],
        ],

        'openrouter' => [
            'driver'   => 'openrouter',
            'api_key'  => env('OPENROUTER_API_KEY'),
            'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
            'models' => [
                'simple'  => env('OPENROUTER_MODEL_SIMPLE', 'anthropic/claude-haiku-4-5'),
                'complex' => env('OPENROUTER_MODEL_COMPLEX', 'anthropic/claude-sonnet-4-6'),
            ],
        ],

    ],

];
