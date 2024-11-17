<?php

use JacobTilly\LaraFort\Enums\FortnoxScope;

return [
    'client_id' => env('FORTNOX_CLIENT_ID'),
    'client_secret' => env('FORTNOX_CLIENT_SECRET'),
    'environment' => env('FORTNOX_ENVIRONMENT', 'live'),
    'scopes' => FortnoxScope::getDefaultScopes(),
    'tunnel' => env('FORTNOX_TUNNEL', 'expose'),
    'callback_url' => env('FORTNOX_CALLBACK_URL'),
];
