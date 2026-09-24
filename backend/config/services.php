<?php

return [
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:3002'),

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('APP_URL') . '/api/auth/google/callback',
    ],

    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID'),
        'team_id' => env('APPLE_TEAM_ID'),
        'key_id' => env('APPLE_KEY_ID'),
        'private_key' => env('APPLE_PRIVATE_KEY'), 
        'redirect' => env('APP_URL') . '/api/auth/apple/callback',
    ],

    // n8n Integrations
    'n8n' => [
        'webhook_url' => env('N8N_WEBHOOK_URL'), // Card template processing webhook
        'chatbot_webhook_url' => env('N8N_CHATBOT_WEBHOOK_URL', 'https://n8n.jiosgroup.com/webhook/e529b3a4-d09d-45d6-8de5-01cc6885bcb7/chat'), // AI chatbot webhook
        'webhook_secret' => env('N8N_WEBHOOK_SECRET'), // Optional secret for verification
    ],
];
