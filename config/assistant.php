<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Driver
    |--------------------------------------------------------------------------
    |
    | "canned"  — matches questions against resources/assistant/topics.php and
    |             returns the written answer. No API, no key, no cost. Default.
    | "claude"  — real answers from Anthropic's API. Needs:
    |               composer require anthropic-ai/sdk
    |               ASSISTANT_DRIVER=claude
    |               ASSISTANT_API_KEY=sk-ant-...
    |
    */

    'driver' => env('ASSISTANT_DRIVER', 'canned'),

    'enabled' => env('ASSISTANT_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Claude driver
    |--------------------------------------------------------------------------
    */

    'api_key' => env('ASSISTANT_API_KEY'),
    'model' => env('ASSISTANT_MODEL', 'claude-haiku-4-5'),
    'max_tokens' => 1024,

    /*
    |--------------------------------------------------------------------------
    | Limits
    |--------------------------------------------------------------------------
    */

    'daily_message_limit' => (int) env('ASSISTANT_DAILY_LIMIT', 50),
    'history_turns' => 10,

    /*
    |--------------------------------------------------------------------------
    | Knowledge base
    |--------------------------------------------------------------------------
    */

    'intro' => 'You are the in-app help assistant for a small-business accounting system. '
        .'Answer only questions about how to use this system, in a few plain sentences for a '
        .'non-technical business owner. If asked about something outside the system (their own '
        .'numbers, account/billing, bugs), say you can only explain how the system works and '
        .'suggest they contact support. Never invent features.',

    'topics' => require resource_path('assistant/topics.php'),

];
