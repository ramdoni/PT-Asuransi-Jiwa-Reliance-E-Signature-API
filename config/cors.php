<?php

return [
    'paths' => ['api/*', 'auth/*', 'stamp/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000',
        'http://relisign.entigi.co.id',
        'https://relisign.entigi.co.id',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];