<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;

class MikroTikService
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    // Test connection to MikroTik router
    public function testConnection()
    {
        try {
            // A simple query to test the connection
            $query = (new Query('/system/resource/print'));
            $response = $this->client->query($query)->read();
            
            // If we get a response, the connection is working
            return !empty($response);
        } catch (\Exception $e) {
            \Log::error('MikroTik connection test failed: ' . $e->getMessage());
            return false;
        }
    }

    // Add a user to MikroTik hotspot
    public function addHotspotUser($username, $macAddress, $profile, $server = 'server1', $defaultPassword = '123456789')
    {
        try {
            // Check if user already exists
            $checkQuery = new Query('/ip/hotspot/user/print');
            $checkQuery->where('name', $macAddress);
            $existingUser = $this->client->query($checkQuery)->read();
            
            if (!empty($existingUser)) {
                \Log::info("User already exists in MikroTik: $macAddress. Updating profile to: $profile");
                
                // Update the user's profile
                $updateQuery = new Query('/ip/hotspot/user/set');
                $updateQuery->equal('.id', $existingUser[0]['.id'])
                           ->equal('profile', $profile);
                
                $this->client->query($updateQuery)->read();
                return $existingUser[0];
            }
            
            // Always use MAC address as username for consistency
            $query = (new Query('/ip/hotspot/user/add'))
                ->equal('server', $server)
                ->equal('name', $macAddress)
                ->equal('password', $defaultPassword)
                ->equal('mac-address', $macAddress)
                ->equal('profile', $profile);

            $result = $this->client->query($query)->read();
            \Log::info("Created new user in MikroTik: $macAddress with profile: $profile");
            
            return $result;
        } catch (\Exception $e) {
            \Log::error('Failed to add hotspot user: ' . $e->getMessage());
            throw new \Exception('Failed to add user to MikroTik: ' . $e->getMessage());
        }
    }

    public function getUserByUsername($username)
    {
        try {
            $query = (new Query('/ip/hotspot/user/print'))
                ->where('name', $username);

            $response = $this->client->query($query)->read();

            // Return the first user found (assuming usernames are unique)
            return $response[0] ?? null;
        } catch (\Exception $e) {
            \Log::error('Failed to get user by username: ' . $e->getMessage());
            return null;
        }
    }

    // Update user profile (e.g., from free to premium)
    public function updateUserProfile($username, $profile)
    {
        try {
            // Step 1: Get the user by username
            $query = (new Query('/ip/hotspot/user/print'))
                ->where('name', $username);

            $response = $this->client->query($query)->read();
            
            // If user doesn't exist, try to create it
            if (empty($response)) {
                \Log::warning("User not found in MikroTik: $username. Attempting to create user.");
                
                // Create the user with the specified profile
                $addQuery = (new Query('/ip/hotspot/user/add'))
                    ->equal('name', $username)
                    ->equal('password', '123456789')
                    ->equal('mac-address', $username)
                    ->equal('profile', $profile);
                
                $this->client->query($addQuery)->read();
                \Log::info("Created new user in MikroTik: $username with profile: $profile");
                return true;
            }
            
            // User exists, update their profile
            $user = $response[0];
            
            $updateQuery = (new Query('/ip/hotspot/user/set'))
                ->equal('.id', $user['.id'])
                ->equal('profile', $profile);

            $this->client->query($updateQuery)->read();
            \Log::info("Updated user profile in MikroTik: $username to profile: $profile");
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to update user profile: ' . $e->getMessage());
            throw new \Exception('Failed to update user profile: ' . $e->getMessage());
        }
    }

    // Ban a user by adding their IP to the address list
    public function banUser($macAddress)
    {
        try {
            $query = (new Query('/ip/firewall/address-list/add'))
                ->equal('address', $macAddress) // For MAC use 'src-mac-address'
                ->equal('list', 'banned_users');

            return $this->client->query($query)->read();
        } catch (\Exception $e) {
            \Log::error('Failed to ban user: ' . $e->getMessage());
            throw new \Exception('Failed to ban user: ' . $e->getMessage());
        }
    }

    // Get all IP addresses from MikroTik
    public function getIPAddresses()
    {
        try {
            $query = (new Query('/ip/address/print'));
            return $this->client->query($query)->read();
        } catch (\Exception $e) {
            \Log::error('Failed to get IP addresses: ' . $e->getMessage());
            return null;
        }
    }

    // Get active hotspot users
    public function getActiveUsers()
    {
        try {
            $query = (new Query('/ip/hotspot/active/print'));
            return $this->client->query($query)->read();
        } catch (\Exception $e) {
            \Log::error('Failed to get active users: ' . $e->getMessage());
            return null;
        }
    }

    // Remove active hotspot session
    public function removeActiveSession($sessionId)
    {
        try {
            $query = (new Query('/ip/hotspot/active/remove'))
                ->equal('.id', $sessionId);
            return $this->client->query($query)->read();
        } catch (\Exception $e) {
            \Log::error('Failed to remove active session: ' . $e->getMessage());
            throw new \Exception('Failed to remove active session: ' . $e->getMessage());
        }
    }

    // Find active session by username
    public function findActiveSessionByUser($username)
    {
        try {
            $query = (new Query('/ip/hotspot/active/print'))
                ->where('user', $username);
            $response = $this->client->query($query)->read();
            return $response[0] ?? null;
        } catch (\Exception $e) {
            \Log::error('Failed to find active session: ' . $e->getMessage());
            return null;
        }
    }
    
    // Get active connections with bandwidth usage (Rx/Tx rates)
    public function getActiveConnectionsWithBandwidth()
    {
        try {
            // First check if the connection is working
            if (!$this->testConnection()) {
                throw new \Exception("Cannot establish connection to MikroTik router");
            }
            
            // Log connection attempt details
            \Log::info('Attempting to fetch active connections from MikroTik at: ' . 
                      env('MIKROTIK_HOST', 'eurekadigital.ddns.net') . ':' . 
                      env('MIKROTIK_PORT', 8728));
            
            // Query to get active hotspot users with their details
            $query = (new Query('/ip/hotspot/active/print'));
            $activeConnections = $this->client->query($query)->read();
            
            // Log the number of active connections found
            \Log::info('Found ' . count($activeConnections) . ' active connections on MikroTik router');
            
            // Process and format the data
            $formattedConnections = [];
            foreach ($activeConnections as $connection) {
                // Extract user info
                $username = $connection['user'] ?? 'unknown';
                $macAddress = $connection['mac-address'] ?? 'unknown';
                $ipAddress = $connection['address'] ?? 'unknown';
                
                // Extract bandwidth usage (convert from bytes to more readable format)
                $rxRateRaw = $connection['rx-rate'] ?? 0;
                $txRateRaw = $connection['tx-rate'] ?? 0;
                
                // Extract total bytes transferred
                $bytesIn = isset($connection['bytes-in']) ? (int)$connection['bytes-in'] : 0;
                $bytesOut = isset($connection['bytes-out']) ? (int)$connection['bytes-out'] : 0;
                
                // Format bandwidth
                $rxRate = $this->formatBandwidth($rxRateRaw);
                $txRate = $this->formatBandwidth($txRateRaw);
                
                // Format bytes transferred for display
                $bytesInFormatted = $this->formatBytesTransferred($bytesIn);
                $bytesOutFormatted = $this->formatBytesTransferred($bytesOut);
                
                // Extract session time
                $uptime = $connection['uptime'] ?? '00:00:00';
                
                // Add to formatted array
                $formattedConnections[] = [
                    'username' => $username,
                    'mac_address' => $macAddress,
                    'ip_address' => $ipAddress,
                    'rx_rate' => $rxRate,
                    'tx_rate' => $txRate,
                    'rx_rate_raw' => $rxRateRaw,
                    'tx_rate_raw' => $txRateRaw,
                    'bytes_in' => $bytesIn,
                    'bytes_out' => $bytesOut,
                    'bytes_in_formatted' => $bytesInFormatted,
                    'bytes_out_formatted' => $bytesOutFormatted,
                    'uptime' => $uptime,
                    'login_time' => $connection['login-by'] ?? 'unknown',
                    'session_id' => $connection['.id'] ?? null
                ];
            }
            
            return $formattedConnections;
        } catch (\Exception $e) {
            \Log::error('Failed to get active connections with bandwidth: ' . $e->getMessage());
            \Log::error('Exception details: ' . $e->getTraceAsString());
            
            // Add more detailed error diagnostics based on the error type
            if (strpos($e->getMessage(), 'timeout') !== false) {
                \Log::error('MikroTik connection timeout - check network connectivity to ' . 
                           env('MIKROTIK_HOST', 'eurekadigital.ddns.net') . ':' . 
                           env('MIKROTIK_PORT', 8728));
            } elseif (strpos($e->getMessage(), 'refused') !== false) {
                \Log::error('MikroTik connection refused - verify that API service is running on the router');
            }
            
            return [];
        }
    }
    
    // Get user bandwidth usage history if available
    public function getUserBandwidthHistory($username)
    {
        try {
            // This assumes MikroTik has a feature to store/retrieve bandwidth history
            // You may need to adapt this to your specific MikroTik configuration
            $query = (new Query('/ip/hotspot/user/profile/print'))
                ->where('name', $username);
            
            return $this->client->query($query)->read();
        } catch (\Exception $e) {
            \Log::error('Failed to get user bandwidth history: ' . $e->getMessage());
            return [];
        }
    }
    
    // Helper function to format bandwidth from bytes to human-readable format
    private function formatBandwidth($bytes)
    {
        $bytes = (int)$bytes;
        
        if ($bytes === 0) {
            return '0 bps';
        } else if ($bytes > 1000000) {
            return round($bytes / 1000000, 2) . ' Mbps';
        } elseif ($bytes > 1000) {
            return round($bytes / 1000, 2) . ' Kbps';
        } else {
            return $bytes . ' bps';
        }
    }
    
    // Helper function to format total bytes transferred to human-readable format
    public function formatBytesTransferred($bytes)
    {
        $bytes = (int)$bytes;
        
        if ($bytes > 1073741824) {
            return round($bytes / 1073741824, 2) . ' GB'; // Convert to gigabytes
        } elseif ($bytes > 1048576) {
            return round($bytes / 1048576, 2) . ' MB'; // Convert to megabytes
        } elseif ($bytes > 1024) {
            return round($bytes / 1024, 2) . ' KB'; // Convert to kilobytes
        } else {
            return $bytes . ' B'; // Bytes
        }
    }
    
    /**
     * Get accounting data from MikroTik router
     * This retrieves historical bandwidth usage data
     * 
     * @return array
     */
    public function getAccountingData()
    {
        try {
            // First check if the connection is working
            if (!$this->testConnection()) {
                throw new \Exception("Cannot establish connection to MikroTik router");
            }
            
            \Log::info('Fetching accounting data from MikroTik');
            
            // Query to get accounting data
            $query = (new Query('/ip/accounting/snapshot/print'));
            $accountingData = $this->client->query($query)->read();
            
            if (empty($accountingData)) {
                \Log::info('No accounting data found on MikroTik router');
                
                // Try to get data from hotspot user stats instead
                $query = (new Query('/ip/hotspot/user/print'));
                $userStats = $this->client->query($query)->read();
                
                \Log::info('Found ' . count($userStats) . ' user stats records on MikroTik router');
                return $userStats;
            }
            
            \Log::info('Found ' . count($accountingData) . ' accounting records on MikroTik router');
            return $accountingData;
            
        } catch (\Exception $e) {
            \Log::error('Failed to get accounting data: ' . $e->getMessage());
            \Log::error('Exception details: ' . $e->getTraceAsString());
            return [];
        }
    }
}
