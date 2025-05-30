<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MikroTik Router Connection Settings
    |--------------------------------------------------------------------------
    |
    | These settings are used for connecting to the MikroTik router
    |
    */

    'host' => env('MIKROTIK_HOST', '192.168.88.1'),
    'user' => env('MIKROTIK_USER', 'api'),
    'pass' => env('MIKROTIK_PASS', 'Erekapp314'),
    'port' => env('MIKROTIK_PORT', 8728),
    'timeout' => env('MIKROTIK_TIMEOUT', 5),
]; 