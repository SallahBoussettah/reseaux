<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use RouterOSAPI;
use RouterOS\Query;

class UpdateLoginStats extends Command
{
    protected $signature = 'users:update-login-stats';
    protected $description = 'Update last login and login count for auto-logged in users';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Connect to MikroTik API
        $mikrotikClient = new RouterOSAPI([
            'host' => '192.168.88.1',
            'user' => 'api',
            'pass' => 'admin',
            'port' => 8728,
            'timeout' => 30,
        ]);

        // Fetch active sessions from MikroTik
        $query = new Query('/ip/hotspot/active/print');
        $activeSessions = $mikrotikClient->query($query)->read();

        // Loop through active sessions and update database
        foreach ($activeSessions as $session) {
            $macAddress = $session['mac-address'];
            $client = Client::where('mac_address', $macAddress)->first();

            if ($client) {
                // Update last login time
                $client->last_login_at = now();

                // Increment login count if this is a new session
                // You can add logic to determine if it's a new session or not
                if ($client->last_login_at === null || $client->last_login_at->diffInMinutes(now()) > 60) {
                    // Increment login count if last login was more than 60 minutes ago
                    $client->login_count += 1;
                }

                //$client->login_count += 1;

                // Save the updated client data
                $client->save();

                $this->info('Updated login stats for client: ' . $client->email);
            }
        }
    }
}
