<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RouterOS\Query;
use RouterOS\Client as RouterOSAPI;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class TestMikrotikConnection extends Command
{
    protected $signature = 'mikrotik:test-connection';
    protected $description = 'Test the connection to the MikroTik router';

    // MikroTik connection details from config
    protected $mikrotikConfig = [];

    public function __construct()
    {
        parent::__construct();
        
        // Load MikroTik config from environment variables or config
        $this->mikrotikConfig = [
            'host' => env('MIKROTIK_HOST', Config::get('services.mikrotik.host', '10.10.10.1')),
            'user' => env('MIKROTIK_USER', Config::get('services.mikrotik.user', 'api')),
            'pass' => env('MIKROTIK_PASS', Config::get('services.mikrotik.pass', 'password')),
            'port' => (int)env('MIKROTIK_PORT', Config::get('services.mikrotik.port', 8728)),
            'timeout' => (int)env('MIKROTIK_TIMEOUT', Config::get('services.mikrotik.timeout', 30)),
        ];
    }

    public function handle()
    {
        $this->info('Testing connection to MikroTik router at ' . $this->mikrotikConfig['host']);
        Log::info('Testing connection to MikroTik router at ' . $this->mikrotikConfig['host']);
        
        try {
            $this->info('Attempting to connect to MikroTik router...');
            $this->info('Using config: ' . json_encode($this->mikrotikConfig, JSON_UNESCAPED_SLASHES));
            $mikrotikClient = new RouterOSAPI($this->mikrotikConfig);
            
            // Test the connection with a simple query
            $testQuery = new Query('/system/resource/print');
            $response = $mikrotikClient->query($testQuery)->read();
            
            if (!empty($response)) {
                $this->info('Successfully connected to MikroTik router!');
                $this->info('Router information:');
                $this->info(json_encode($response, JSON_PRETTY_PRINT));
                Log::info('Successfully connected to MikroTik router: ' . json_encode($response));
                
                // List some hotspot users as a further test
                $this->info('Attempting to list hotspot users...');
                $usersQuery = new Query('/ip/hotspot/user/print');
                $users = $mikrotikClient->query($usersQuery)->read();
                
                if (!empty($users)) {
                    $this->info('Found ' . count($users) . ' hotspot users');
                    $this->info('First few users:');
                    $count = 0;
                    foreach ($users as $user) {
                        $this->info('User: ' . ($user['name'] ?? 'N/A') . ', MAC: ' . ($user['mac-address'] ?? 'N/A'));
                        $count++;
                        if ($count >= 5) break;
                    }
                } else {
                    $this->info('No hotspot users found');
                }
                
                return Command::SUCCESS;
            } else {
                $this->error('Empty response from router');
                Log::error('Empty response from router');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('Failed to connect to MikroTik router: ' . $e->getMessage());
            Log::error('Failed to connect to MikroTik router: ' . $e->getMessage());
            
            // Additional debugging information
            $this->info('Debug information:');
            $this->info('PHP version: ' . phpversion());
            $this->info('RouterOS API version: ' . (class_exists('RouterOS\Client') ? 'Installed' : 'Not installed'));
            $this->info('Connection config: ' . json_encode($this->mikrotikConfig, JSON_UNESCAPED_SLASHES));
            
            return Command::FAILURE;
        }
    }
} 