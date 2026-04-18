<?php

return [
    'api_base' => env('TELEGRAM_API_BASE', 'https://api.telegram.org'),

    'bots' => [
        // Primary gateway agents
        'Number 2'           => env('TELEGRAM_BOT_TOKEN_NUMBER2'),
        'Hymie'              => env('TELEGRAM_BOT_TOKEN_HYMIE'),
        'Random Task'        => env('TELEGRAM_BOT_TOKEN_RANDOMTASK'),
        'Simon Templar'      => env('TELEGRAM_BOT_TOKEN_SIMONTEMPLAR'),
        // Number 2's internal roster (each with own Telegram identity for now)
        'Basil'              => env('TELEGRAM_BOT_TOKEN_BASIL'),
        'Fox Mulder'         => env('TELEGRAM_BOT_TOKEN_FOXMULDER'),
        'Inspector Clouseau' => env('TELEGRAM_BOT_TOKEN_INSPECTORCLOUSEAU'),
        'Max'                => env('TELEGRAM_BOT_TOKEN_MAX'),
        'Matt'               => env('TELEGRAM_BOT_TOKEN_MATT'),
    ],

    'timeout_seconds' => env('TELEGRAM_TIMEOUT', 10),
];
