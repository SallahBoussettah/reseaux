<?php

namespace App\Http\Controllers;

use App\Services\MikroTikService;
use Illuminate\Http\Request;
use RouterOS\Client as RouterOSAPI;
use RouterOS\Query;

class MikroTikController extends Controller
{
    protected $mikroTikService;

    public function __construct(MikroTikService $mikroTikService)
    {
        $this->mikroTikService = $mikroTikService;
    }

    public function testConnection()
    {
     
        if ($this->mikroTikService->testConnection()) {
            return response()->json(['message' => 'Connected to MikroTik router successfully.']);
        } else {
            return response()->json(['message' => 'Failed to connect to MikroTik router.'], 500);
        }
    }

    public function getAddresses()
    {
        $addresses = $this->mikroTikService->getIPAddresses();

        if ($addresses) {
            return response()->json($addresses);
        } else {
            return response()->json(['message' => 'Failed to retrieve IP addresses.'], 500);
        }
    }
    
    public function getHotspotProfiles()
    {
        try {
            // Connect to MikroTik API
            $mikrotikClient = new RouterOSAPI([
                'host' => '192.168.88.1',
                'user' => 'api',
                'pass' => 'admin',
                'port' => 8728,
                'timeout' => 30,
            ]);

            // Get hotspot profiles
            $query = new Query('/ip/hotspot/user/profile/print');
            $profiles = $mikrotikClient->query($query)->read();
            
            return response()->json([
                'success' => true,
                'profiles' => $profiles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve hotspot profiles: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getHotspotUsers()
    {
        try {
            // Connect to MikroTik API
            $mikrotikClient = new RouterOSAPI([
                'host' => '192.168.88.1',
                'user' => 'api',
                'pass' => 'admin',
                'port' => 8728,
                'timeout' => 30,
            ]);

            // Get hotspot users
            $query = new Query('/ip/hotspot/user/print');
            $users = $mikrotikClient->query($query)->read();
            
            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve hotspot users: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function createTestUsers()
    {
        try {
            // Connect to MikroTik API
            $mikrotikClient = new RouterOSAPI([
                'host' => '192.168.88.1',
                'user' => 'api',
                'pass' => 'admin',
                'port' => 8728,
                'timeout' => 30,
            ]);

            // Test MAC addresses
            $freeMac = "AA:BB:CC:DD:EE:FF";
            $premiumMac = "11:22:33:44:55:66";

            // Create free user with MAC address as username
            $freeQuery = new Query('/ip/hotspot/user/add');
            $freeQuery->equal('name', $freeMac)
                     ->equal('password', '123456789')
                     ->equal('mac-address', $freeMac)
                     ->equal('profile', 'free_user');
            $mikrotikClient->query($freeQuery)->read();
            
            // Create premium user with MAC address as username
            $premiumQuery = new Query('/ip/hotspot/user/add');
            $premiumQuery->equal('name', $premiumMac)
                        ->equal('password', '123456789')
                        ->equal('mac-address', $premiumMac)
                        ->equal('profile', 'premium_user');
            $mikrotikClient->query($premiumQuery)->read();
            
            return response()->json([
                'success' => true,
                'message' => 'Test users created successfully',
                'users' => [
                    'free_user' => [
                        'mac' => $freeMac,
                        'username' => $freeMac,
                        'password' => '123456789'
                    ],
                    'premium_user' => [
                        'mac' => $premiumMac,
                        'username' => $premiumMac,
                        'password' => '123456789'
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create test users: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkAndCreateProfiles()
    {
        try {
            // Connect to MikroTik API
            $mikrotikClient = new RouterOSAPI([
                'host' => '192.168.88.1',
                'user' => 'api',
                'pass' => 'admin',
                'port' => 8728,
                'timeout' => 30,
            ]);

            // Get hotspot profiles
            $query = new Query('/ip/hotspot/user/profile/print');
            $profiles = $mikrotikClient->query($query)->read();
            
            // Check if free_user profile exists
            $freeUserExists = false;
            $premiumUserExists = false;
            
            foreach ($profiles as $profile) {
                if ($profile['name'] === 'free_user') {
                    $freeUserExists = true;
                }
                if ($profile['name'] === 'premium_user') {
                    $premiumUserExists = true;
                }
            }
            
            $created = [];
            
            // Create free_user profile if it doesn't exist
            if (!$freeUserExists) {
                $freeQuery = new Query('/ip/hotspot/user/profile/add');
                $freeQuery->equal('name', 'free_user')
                         ->equal('session-timeout', '00:05:00')
                         ->equal('keepalive-timeout', '00:05:00')
                         ->equal('status-autorefresh', '1m')
                         ->equal('shared-users', '1');
                $mikrotikClient->query($freeQuery)->read();
                $created[] = 'free_user';
            }
            
            // Create premium_user profile if it doesn't exist
            if (!$premiumUserExists) {
                $premiumQuery = new Query('/ip/hotspot/user/profile/add');
                $premiumQuery->equal('name', 'premium_user')
                           ->equal('session-timeout', '7d 00:00:00')
                           ->equal('keepalive-timeout', '00:10:00')
                           ->equal('status-autorefresh', '1m')
                           ->equal('shared-users', '1');
                $mikrotikClient->query($premiumQuery)->read();
                $created[] = 'premium_user';
            }
            
            // Get updated profiles
            $updatedProfiles = $mikrotikClient->query($query)->read();
            
            return response()->json([
                'success' => true,
                'created_profiles' => $created,
                'profiles' => $updatedProfiles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check/create hotspot profiles: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cleanupDuplicateUsers()
    {
        try {
            // Connect to MikroTik API
            $mikrotikClient = new RouterOSAPI([
                'host' => '192.168.88.1',
                'user' => 'api',
                'pass' => 'admin',
                'port' => 8728,
                'timeout' => 30,
            ]);

            // Get all hotspot users
            $query = new Query('/ip/hotspot/user/print');
            $users = $mikrotikClient->query($query)->read();
            
            // Track usernames we've seen
            $seenUsernames = [];
            $duplicates = [];
            $removed = [];
            
            // Find duplicates
            foreach ($users as $user) {
                $username = $user['name'];
                
                if (isset($seenUsernames[$username])) {
                    // This is a duplicate
                    $duplicates[] = $user;
                } else {
                    $seenUsernames[$username] = $user;
                }
            }
            
            // Remove duplicates
            foreach ($duplicates as $duplicate) {
                $removeQuery = new Query('/ip/hotspot/user/remove');
                $removeQuery->equal('.id', $duplicate['.id']);
                $mikrotikClient->query($removeQuery)->read();
                $removed[] = $duplicate;
            }
            
            return response()->json([
                'success' => true,
                'total_users' => count($users),
                'duplicates_found' => count($duplicates),
                'duplicates_removed' => $removed
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cleanup duplicate users: ' . $e->getMessage()
            ], 500);
        }
    }
}
