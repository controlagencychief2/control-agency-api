<?php

return [
    'api_base' => env('TELEGRAM_API_BASE', 'https://api.telegram.org'),

    'bots' => [
        'Number 2'      => env('TELEGRAM_BOT_TOKEN_NUMBER2'),
        'Hymie'         => env('TELEGRAM_BOT_TOKEN_HYMIE'),
        'Random Task'   => env('TELEGRAM_BOT_TOKEN_RANDOMTASK'),
        'Simon Templar' => env('TELEGRAM_BOT_TOKEN_SIMONTEMPLAR'),
        'Fox Mulder'    => env('TELEGRAM_BOT_TOKEN_FOXMULDER'),
        'Basil'         => env('TELEGRAM_BOT_TOKEN_BASIL'),
    ],

    'timeout_seconds' => env('TELEGRAM_TIMEOUT', 10),
];
