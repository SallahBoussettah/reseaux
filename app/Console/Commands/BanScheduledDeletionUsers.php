<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use Carbon\Carbon;
use RouterOS\Query;
use RouterOS\Client as RouterOSAPI;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Services\MikroTikService;

class BanScheduledDeletionUsers extends Command
{
    protected $signature = 'users:ban-scheduled';
    protected $description = 'Ban users whose scheduled deletion time has passed by adding them to the MikroTik banned users list';

    // MikroTik connection details from config
    protected $mikrotikConfig = [];
    
    // Track statistics for reporting
    protected $stats = [
        'total_processed' => 0,
        'banned' => 0,
        'errors' => 0
    ];

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
        $startTime = microtime(true);
        
        // Find all users whose scheduled deletion time has passed
        $expiredUsers = Client::whereNotNull('scheduled_deletion_at')
                             ->where('scheduled_deletion_at', '<', Carbon::now())
                             ->get();

        $this->info('Found ' . $expiredUsers->count() . ' users scheduled for deletion/banning.');
        Log::info('Found ' . $expiredUsers->count() . ' users scheduled for deletion/banning.');
        
        if ($expiredUsers->count() === 0) {
            return Command::SUCCESS;
        }
        
        // Initialize MikroTik client
        $mikrotikClient = null;
        $mikrotikConnected = false;
        $mikrotikService = null;
        
        // Try to establish connection to MikroTik router upfront
        try {
            $this->info('Attempting to connect to MikroTik router at ' . $this->mikrotikConfig['host']);
            Log::info('Attempting to connect to MikroTik router at ' . $this->mikrotikConfig['host']);
            
            $mikrotikClient = new RouterOSAPI($this->mikrotikConfig);
            
            // Test the connection with a simple query
            $testQuery = new Query('/system/resource/print');
            $response = $mikrotikClient->query($testQuery)->read();
            
            if (!empty($response)) {
                $mikrotikConnected = true;
                $mikrotikService = new MikroTikService($mikrotikClient);
                $this->info('Successfully connected to MikroTik router');
                Log::info('Successfully connected to MikroTik router: ' . json_encode($response[0]['version'] ?? 'Unknown version'));
            } else {
                throw new \Exception('Empty response from router');
            }
        } catch (\Exception $e) {
            $this->error('Failed to connect to MikroTik router: ' . $e->getMessage());
            Log::error('Failed to connect to MikroTik router: ' . $e->getMessage());
            return Command::FAILURE;
        }
        
        // Create address-list for banned users if it doesn't exist
        try {
            $this->ensureBannedUsersListExists($mikrotikClient);
        } catch (\Exception $e) {
            $this->error('Failed to ensure banned users list exists: ' . $e->getMessage());
            Log::error('Failed to ensure banned users list exists: ' . $e->getMessage());
            // Continue anyway, it might already exist
        }
        
        // Loop through each expired user
        foreach ($expiredUsers as $user) {
            $this->stats['total_processed']++;
            $this->info('Processing user: ' . $user->email . ' (ID: ' . $user->id . ', MAC: ' . ($user->mac_address ?? 'None') . ')');
            Log::info('Processing user: ' . $user->email . ' (ID: ' . $user->id . ', MAC: ' . ($user->mac_address ?? 'None') . ')');
            
            // Skip users without MAC address
            if (empty($user->mac_address)) {
                $this->warn('Skipping user without MAC address: ' . $user->email);
                Log::warning('Skipping user without MAC address: ' . $user->email);
                continue;
            }
            
            try {
                // Ban the user by adding to the address list
                $this->banUser($mikrotikClient, $user->mac_address);
                
                // Update user status in database
                $user->status = 'banned';
                $user->save();
                
                $this->stats['banned']++;
                $this->info('Successfully banned user: ' . $user->email . ' (MAC: ' . $user->mac_address . ')');
                Log::info('Successfully banned user: ' . $user->email . ' (MAC: ' . $user->mac_address . ')');
            } catch (\Exception $e) {
                $this->stats['errors']++;
                $this->error('Failed to ban user ' . $user->email . ': ' . $e->getMessage());
                Log::error('Failed to ban user ' . $user->email . ': ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'mac_address' => $user->mac_address,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $executionTime = round(microtime(true) - $startTime, 2);
        
        // Output summary
        $this->info('');
        $this->info('=== Banning Summary ===');
        $this->info('Total users processed: ' . $this->stats['total_processed']);
        $this->info('Users banned: ' . $this->stats['banned']);
        $this->info('Errors encountered: ' . $this->stats['errors']);
        $this->info('Execution time: ' . $executionTime . ' seconds');
        
        Log::info('User banning completed', [
            'total_processed' => $this->stats['total_processed'],
            'banned' => $this->stats['banned'],
            'errors' => $this->stats['errors'],
            'execution_time' => $executionTime
        ]);
        
        return Command::SUCCESS;
    }
    
    /**
     * Ban a user in the MikroTik router by MAC address
     */
    private function banUser(RouterOSAPI $mikrotikClient, $macAddress)
    {
        $this->info('Banning user with MAC address: ' . $macAddress);
        Log::info('Banning user with MAC address: ' . $macAddress);
        
        // Step 1: Remove any active sessions for this user
        $findActiveSessionQuery = new Query('/ip/hotspot/active/print');
        $findActiveSessionQuery->where('mac-address', $macAddress);
        $activeSessions = $mikrotikClient->query($findActiveSessionQuery)->read();
        
        if (!empty($activeSessions)) {
            $this->info('Found ' . count($activeSessions) . ' active sessions for MAC: ' . $macAddress);
            Log::info('Found ' . count($activeSessions) . ' active sessions for MAC: ' . $macAddress);
            
            foreach ($activeSessions as $session) {
                try {
                    $removeSessionQuery = new Query('/ip/hotspot/active/remove');
                    $removeSessionQuery->equal('.id', $session['.id']);
                    $mikrotikClient->query($removeSessionQuery)->read();
                    $this->info('Removed active session for MAC: ' . $macAddress);
                    Log::info('Removed active session for MAC: ' . $macAddress, [
                        'session_id' => $session['.id']
                    ]);
                } catch (\Exception $e) {
                    $this->error('Failed to remove active session: ' . $e->getMessage());
                    Log::error('Failed to remove active session: ' . $e->getMessage(), [
                        'session_id' => $session['.id'],
                        'mac_address' => $macAddress
                    ]);
                }
            }
        } else {
            $this->info('No active sessions found for MAC: ' . $macAddress);
            Log::info('No active sessions found for MAC: ' . $macAddress);
        }
        
        // Step 2: Add the user to the banned_users address list
        try {
            // Check if the MAC address is already in the banned_users list
            $checkQuery = new Query('/ip/firewall/address-list/print');
            $checkQuery->where('list', 'banned_users')
                      ->where('address', $macAddress);
            $existingEntry = $mikrotikClient->query($checkQuery)->read();
            
            if (!empty($existingEntry)) {
                $this->info('MAC address already in banned_users list: ' . $macAddress);
                Log::info('MAC address already in banned_users list: ' . $macAddress);
                return;
            }
            
            // Add MAC address to banned_users list
            $addQuery = new Query('/ip/firewall/address-list/add');
            $addQuery->equal('list', 'banned_users')
                    ->equal('address', $macAddress)
                    ->equal('comment', 'Banned due to scheduled deletion');
            
            $result = $mikrotikClient->query($addQuery)->read();
            $this->info('Added MAC address to banned_users list: ' . $macAddress);
            Log::info('Added MAC address to banned_users list: ' . $macAddress, [
                'result' => $result
            ]);
        } catch (\Exception $e) {
            $this->error('Failed to add MAC address to banned_users list: ' . $e->getMessage());
            Log::error('Failed to add MAC address to banned_users list: ' . $e->getMessage(), [
                'mac_address' => $macAddress
            ]);
            throw $e; // Re-throw to handle at the caller level
        }
    }
    
    /**
     * Ensure the banned_users address list exists in MikroTik
     */
    private function ensureBannedUsersListExists(RouterOSAPI $mikrotikClient)
    {
        // Check if banned_users address list exists
        $checkQuery = new Query('/ip/firewall/address-list/print');
        $checkQuery->where('list', 'banned_users');
        $existingList = $mikrotikClient->query($checkQuery)->read();
        
        if (empty($existingList)) {
            $this->info('Creating banned_users address list in MikroTik');
            Log::info('Creating banned_users address list in MikroTik');
            
            // Create a dummy entry to ensure the list exists
            $addQuery = new Query('/ip/firewall/address-list/add');
            $addQuery->equal('list', 'banned_users')
                    ->equal('address', '0.0.0.0/32')
                    ->equal('comment', 'Placeholder entry for banned_users list');
            
            $mikrotikClient->query($addQuery)->read();
        } else {
            $this->info('banned_users address list already exists in MikroTik');
            Log::info('banned_users address list already exists in MikroTik');
        }
    }
} 