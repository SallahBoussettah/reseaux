<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use RouterOS\Client;
use RouterOS\Config;

class MikroTikServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Client::class, function ($app) {
            $config = new Config([
                'host' => env('MIKROTIK_HOST','eurekadigital.ddns.net'),
                'user' => env('MIKROTIK_USER','api'),
                'pass' => env('MIKROTIK_PASS', 'Erekapp314'),
                'port' => (int) env('MIKROTIK_PORT', 8728),
                'timeout' => 30,
            ]);

            return new Client($config);
        });
    }
}