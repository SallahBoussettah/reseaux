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

class DeleteExpiredPremiumUsers extends Command
{
    protected $signature = 'users:delete-expired';
    protected $description = 'Delete users whose scheduled deletion time has passed';

    // MikroTik connection details from config
    protected $mikrotikConfig = [];
    
    // Track statistics for reporting
    protected $stats = [
        'total_processed' => 0,
        'db_deleted' => 0,
        'router_deleted' => 0,
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

        $this->info('Found ' . $expiredUsers->count() . ' users scheduled for deletion.');
        Log::info('Found ' . $expiredUsers->count() . ' users scheduled for deletion.');
        
        if ($expiredUsers->count() === 0) {
            return Command::SUCCESS;
        }
        
        // Initialize MikroTik client for premium users
        $mikrotikClient = null;
        $mikrotikConnected = false;
        
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
                $this->info('Successfully connected to MikroTik router');
                Log::info('Successfully connected to MikroTik router: ' . json_encode($response[0]['version'] ?? 'Unknown version'));
            } else {
                throw new \Exception('Empty response from router');
            }
        } catch (\Exception $e) {
            $this->error('Failed to connect to MikroTik router: ' . $e->getMessage());
            Log::error('Failed to connect to MikroTik router: ' . $e->getMessage());
            // We'll continue with database deletion even if MikroTik connection fails
        }
        
        // Loop through each expired user
        foreach ($expiredUsers as $user) {
            $this->stats['total_processed']++;
            $this->info('Processing user: ' . $user->email . ' (ID: ' . $user->id . ', MAC: ' . ($user->mac_address ?? 'None') . ')');
            Log::info('Processing user: ' . $user->email . ' (ID: ' . $user->id . ', MAC: ' . ($user->mac_address ?? 'None') . ')');
            
            DB::beginTransaction();
            try {
                // If this is a user with MAC address, handle MikroTik removal
                if (!empty($user->mac_address) && $mikrotikConnected) {
                    try {
                        $this->removeUserFromRouter($mikrotikClient, $user->mac_address);
                        $this->stats['router_deleted']++;
                    } catch (\Exception $e) {
                        $this->error('Failed to remove user from router: ' . $e->getMessage());
                        Log::error('Failed to remove user from router: ' . $e->getMessage(), [
                            'user_id' => $user->id,
                            'mac_address' => $user->mac_address,
                            'error' => $e->getMessage()
                        ]);
                        // Continue with database deletion even if MikroTik removal fails
                    }
                }
                
                // Store user ID for logging
                $userId = $user->id;
                $userEmail = $user->email;
                
                // Delete the user from the database
                $user->delete();
                $this->stats['db_deleted']++;
                
                DB::commit();
                
                $this->info('Successfully deleted user: ' . $userEmail . ' (ID: ' . $userId . ')');
                Log::info('Successfully deleted user: ' . $userEmail . ' (ID: ' . $userId . ')');
            } catch (\Exception $e) {
                DB::rollBack();
                $this->stats['errors']++;
                $this->error('Failed to delete user ' . $user->email . ': ' . $e->getMessage());
                Log::error('Failed to delete user ' . $user->email . ': ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $executionTime = round(microtime(true) - $startTime, 2);
        
        // Output summary
        $this->info('');
        $this->info('=== Deletion Summary ===');
        $this->info('Total users processed: ' . $this->stats['total_processed']);
        $this->info('Deleted from database: ' . $this->stats['db_deleted']);
        $this->info('Deleted from router: ' . $this->stats['router_deleted']);
        $this->info('Errors encountered: ' . $this->stats['errors']);
        $this->info('Execution time: ' . $executionTime . ' seconds');
        
        Log::info('User deletion completed', [
            'total_processed' => $this->stats['total_processed'],
            'db_deleted' => $this->stats['db_deleted'],
            'router_deleted' => $this->stats['router_deleted'],
            'errors' => $this->stats['errors'],
            'execution_time' => $executionTime
        ]);
        
        return Command::SUCCESS;
    }
    
    /**
     * Remove a user from the MikroTik router by MAC address
     */
    private function removeUserFromRouter(RouterOSAPI $mikrotikClient, $macAddress)
    {
        $this->info('Removing user with MAC address: ' . $macAddress . ' from MikroTik');
        Log::info('Removing user with MAC address: ' . $macAddress . ' from MikroTik');
        
        // Step 1: Find the user in MikroTik by MAC address or username
        $findUserQuery = new Query('/ip/hotspot/user/print');
        $findUserQuery->where('name', $macAddress);
        $userInfo = $mikrotikClient->query($findUserQuery)->read();
        
        if (empty($userInfo)) {
            $this->warn('User not found by name in MikroTik: ' . $macAddress);
            Log::warning('User not found by name in MikroTik: ' . $macAddress);
            
            // Try finding by MAC address directly in case the username is different
            $findByMacQuery = new Query('/ip/hotspot/user/print');
            $findByMacQuery->where('mac-address', $macAddress);
            $userInfoByMac = $mikrotikClient->query($findByMacQuery)->read();
            
            if (empty($userInfoByMac)) {
                $this->warn('User not found by MAC address in MikroTik: ' . $macAddress);
                Log::warning('User not found by MAC address in MikroTik: ' . $macAddress);
                return;
            }
            
            $userInfo = $userInfoByMac;
            $this->info('Found user by MAC address instead: ' . $userInfo[0]['name']);
            Log::info('Found user by MAC address instead: ' . $userInfo[0]['name']);
        }
        
        // Step 2: Remove any active sessions for this user
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
        
        // Step 3: Remove the user from hotspot users
        try {
            $removeUserQuery = new Query('/ip/hotspot/user/remove');
            $removeUserQuery->equal('.id', $userInfo[0]['.id']);
            $mikrotikClient->query($removeUserQuery)->read();
            $this->info('Removed user from MikroTik hotspot: ' . $macAddress);
            Log::info('Removed user from MikroTik hotspot: ' . $macAddress, [
                'user_id' => $userInfo[0]['.id']
            ]);
        } catch (\Exception $e) {
            $this->error('Failed to remove user from hotspot: ' . $e->getMessage());
            Log::error('Failed to remove user from hotspot: ' . $e->getMessage(), [
                'user_id' => $userInfo[0]['.id'],
                'mac_address' => $macAddress
            ]);
            throw $e; // Re-throw to handle at the caller level
        }
    }
} 