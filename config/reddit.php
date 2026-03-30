<?php

return [
    'client_id' => env('REDDIT_CLIENT_ID'),
    'client_secret' => env('REDDIT_CLIENT_SECRET'),
    'username' => env('REDDIT_USERNAME'),
    'password' => env('REDDIT_PASSWORD'),
    'user_agent' => env('REDDIT_USER_AGENT', 'web:quickfeedback:v1.0'),
    'account_created_at' => env('REDDIT_ACCOUNT_CREATED_AT'),
    'dry_run' => env('REDDIT_DRY_RUN', true),
    'enabled' => env('REDDIT_ENABLED', false),
    'drafter_model' => env('REDDIT_DRAFTER_MODEL', 'claude-haiku-4-5-20251001'),
    'strategist_model' => env('REDDIT_STRATEGIST_MODEL', 'claude-sonnet-4-6'),
    'anthropic_api_key' => env('ANTHROPIC_API_KEY'),
];
