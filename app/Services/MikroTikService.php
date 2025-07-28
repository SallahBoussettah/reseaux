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
            \Log::info("Testing connection to MikroTik router...");
            
            // Log connection parameters (without password)
            $connectionParams = [
                'host' => env('MIKROTIK_HOST', 'eurekadigital.ddns.net'),
                'port' => env('MIKROTIK_PORT', 8728),
                'user' => env('MIKROTIK_USER', 'api'),
                'timeout' => 15
            ];
            \Log::info("Connection parameters: " . json_encode($connectionParams));
            
            // A simple query to test the connection
            $query = (new Query('/system/resource/print'));
            $response = $this->client->query($query)->read();
            
            // If we get a response, the connection is working
            if (!empty($response)) {
                // Extract system information for logging
                $systemInfo = $response[0] ?? [];
                $version = $systemInfo['version'] ?? 'unknown';
                $cpuLoad = $systemInfo['cpu-load'] ?? 'unknown';
                $uptime = $systemInfo['uptime'] ?? 'unknown';
                
                \Log::info("MikroTik connection successful. Router version: $version, CPU load: $cpuLoad, Uptime: $uptime");
                return true;
            } else {
                \Log::warning("MikroTik connection test returned empty response");
                return false;
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $errorCode = $e->getCode();
            
            // Enhanced error logging with more detailed diagnostics
            \Log::error("MikroTik connection test failed: {$errorMessage} (Code: {$errorCode})");
            
            // Provide more specific diagnostic information based on error type
            if (strpos($errorMessage, 'timeout') !== false) {
                \Log::error("Connection timeout detected. Check if the router is reachable at the specified IP/hostname and port.");
            } elseif (strpos($errorMessage, 'refused') !== false || $errorCode === 111) {
                \Log::error("Connection refused. Ensure the API service is enabled on the router and the port is correct.");
            } elseif (strpos($errorMessage, 'auth') !== false || strpos($errorMessage, 'login') !== false) {
                \Log::error("Authentication failure. Verify username and password are correct.");
            } elseif (strpos($errorMessage, 'resolve') !== false) {
                \Log::error("DNS resolution failed. Check if the hostname is correct.");
            }
            
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
            // Step 1: Find and disconnect any active session for this user
            $activeSession = $this->findActiveSessionByUser($username);
            if ($activeSession) {
                \Log::info("Found active session for user: $username. Disconnecting to refresh profile.");
                $this->removeActiveSession($activeSession['.id']);
                \Log::info("Successfully disconnected user: $username");
            }
            
            // Step 2: Get the user by username
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
            
            // Step 3: User exists, update their profile
            $user = $response[0];
            
            $updateQuery = (new Query('/ip/hotspot/user/set'))
                ->equal('.id', $user['.id'])
                ->equal('profile', $profile);

            $this->client->query($updateQuery)->read();
            \Log::info("Updated user profile in MikroTik: $username to profile: $profile");
            
            // Force a hotspot system refresh to clear any cached states
            $this->refreshHotspotSystem();
            
            // Clear hotspot cache to ensure changes take effect immediately
            try {
                $clearCacheQuery = new Query('/ip/hotspot/host/print');
                $hosts = $this->client->query($clearCacheQuery)->read();
                
                // Remove any cached host entries for this user
                foreach ($hosts as $host) {
                    if (isset($host['mac-address']) && $host['mac-address'] === $username) {
                        $removeHostQuery = new Query('/ip/hotspot/host/remove');
                        $removeHostQuery->equal('.id', $host['.id']);
                        $this->client->query($removeHostQuery)->read();
                        \Log::info("Cleared hotspot host cache for: $username");
                    }
                }
            } catch (\Exception $e) {
                \Log::warning("Could not clear hotspot cache: " . $e->getMessage());
                // Don't fail the whole operation if cache clearing fails
            }
            
            // Also try to refresh IP bindings
            try {
                $bindingsQuery = new Query('/ip/hotspot/ip-binding/print');
                $bindings = $this->client->query($bindingsQuery)->read();
                
                // Remove any conflicting IP bindings for this MAC
                foreach ($bindings as $binding) {
                    if (isset($binding['mac-address']) && $binding['mac-address'] === $username) {
                        $removeBindingQuery = new Query('/ip/hotspot/ip-binding/remove');
                        $removeBindingQuery->equal('.id', $binding['.id']);
                        $this->client->query($removeBindingQuery)->read();
                        \Log::info("Cleared IP binding for: $username");
                    }
                }
            } catch (\Exception $e) {
                \Log::warning("Could not clear IP bindings: " . $e->getMessage());
            }
            
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
    
    // Force refresh hotspot system state
    public function refreshHotspotSystem()
    {
        try {
            \Log::info("Forcing hotspot system refresh");
            
            // Method 1: Clear all hotspot host cache
            $clearHostsQuery = new Query('/ip/hotspot/host/remove');
            $clearHostsQuery->equal('numbers', '0-999999');
            try {
                $this->client->query($clearHostsQuery)->read();
                \Log::info("Cleared all hotspot host cache");
            } catch (\Exception $e) {
                \Log::warning("Could not clear all host cache: " . $e->getMessage());
            }
            
            // Method 2: Refresh DHCP leases
            try {
                $refreshDhcpQuery = new Query('/ip/dhcp-server/lease/make-static');
                $refreshDhcpQuery->equal('numbers', '');
                $this->client->query($refreshDhcpQuery)->read();
            } catch (\Exception $e) {
                // This might fail, that's okay
            }
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to refresh hotspot system: ' . $e->getMessage());
            return false;
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
                // Log the raw values from the router for debugging
                \Log::debug("Raw connection data: " . json_encode($connection));
                
                // Extract user info
                $username = $connection['user'] ?? 'unknown';
                $macAddress = $connection['mac-address'] ?? 'unknown';
                $ipAddress = $connection['address'] ?? 'unknown';
                
                // Extract bandwidth usage (convert from bytes to more readable format)
                $rxRateRaw = isset($connection['rx-rate']) ? (int)$connection['rx-rate'] : 0;
                $txRateRaw = isset($connection['tx-rate']) ? (int)$connection['tx-rate'] : 0;
                
                // Extract total bytes transferred
                $bytesIn = isset($connection['bytes-in']) ? (int)$connection['bytes-in'] : 0;
                $bytesOut = isset($connection['bytes-out']) ? (int)$connection['bytes-out'] : 0;
                
                // Format bandwidth - keeping tx and rx swapped as per your initial setup
                $rxRate = $this->formatBandwidth($txRateRaw); // Note: deliberately swapped as mentioned
                $txRate = $this->formatBandwidth($rxRateRaw); // Note: deliberately swapped as mentioned
                
                // Check if rx/tx rates are zero but bytes transferred is not
                $isIdle = ($rxRateRaw === 0 && $txRateRaw === 0);
                $hasTransferredData = ($bytesIn > 0 || $bytesOut > 0);
                
                if ($isIdle && $hasTransferredData) {
                    \Log::info("User {$username} is currently idle (0 bps) but has transferred data: In={$bytesIn} bytes, Out={$bytesOut} bytes");
                    
                    // Append idle status to formatted rate
                    $rxRate = "0 bps <small>(idle)</small>";
                    $txRate = "0 bps <small>(idle)</small>";
                } else if ($isIdle) {
                    \Log::info("User {$username} is idle with no data transfer");
                } else {
                    \Log::info("User {$username} is active with current rates: RX={$rxRate}, TX={$txRate}");
                }
                
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
                    'rx_rate' => $rxRate, // Note: deliberately swapped as mentioned
                    'tx_rate' => $txRate, // Note: deliberately swapped as mentioned
                    'rx_rate_raw' => $txRateRaw, // Note: deliberately swapped but still raw value
                    'tx_rate_raw' => $rxRateRaw, // Note: deliberately swapped but still raw value
                    'bytes_in' => $bytesIn,
                    'bytes_out' => $bytesOut,
                    'bytes_in_formatted' => $bytesInFormatted,
                    'bytes_out_formatted' => $bytesOutFormatted,
                    'uptime' => $uptime,
                    'login_time' => isset($connection['login-by']) ? $connection['login-by'] : 'unknown',
                    'session_id' => isset($connection['.id']) ? $connection['.id'] : 'unknown',
                    'status' => $isIdle ? 'idle' : 'active',
                    'has_transferred_data' => $hasTransferredData
                ];
                
                // Log detailed information for each connection
                \Log::info("Connection details for {$username}: " .
                          "RX={$rxRate} ({$txRateRaw} bps), " . // Note the deliberate swap
                          "TX={$txRate} ({$rxRateRaw} bps), " . // Note the deliberate swap
                          "Bytes In={$bytesInFormatted}, " .
                          "Bytes Out={$bytesOutFormatted}");
            }
            
            return $formattedConnections;
        } catch (\Exception $e) {
            \Log::error('Failed to get active connections with bandwidth: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
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
        
        if ($bytes > 1000000) {
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

    /**
     * Get real-time interface traffic statistics
     * This method fetches the current traffic rate from a specific interface
     * 
     * @param string $interfaceName The name of the interface to monitor (default: all interfaces)
     * @return array The traffic data including rx-bits-per-second and tx-bits-per-second
     */
    public function getInterfaceTraffic($interfaceName = null)
    {
        try {
            // First check if the connection is working
            if (!$this->testConnection()) {
                throw new \Exception("Cannot establish connection to MikroTik router");
            }
            
            \Log::info('Fetching interface traffic data from MikroTik');
            
            // Common WAN interface names to check first
            $priorityInterfaces = ['ether1', 'wlan1', 'sfp1', 'pppoe-out1', 'wan'];
            
            // Get all interfaces if no specific interface is specified
            if ($interfaceName === null) {
                // First get list of all interfaces
                $interfacesQuery = (new Query('/interface/print'));
                $interfaces = $this->client->query($interfacesQuery)->read();
                
                \Log::info('Found ' . count($interfaces) . ' interfaces on MikroTik router');
                
                // Initialize counters
                $totalRxBitsPerSecond = 0;
                $totalTxBitsPerSecond = 0;
                $wanInterfaceFound = false;
                
                // First try to find a WAN/external interface specifically
                foreach ($priorityInterfaces as $wanInterface) {
                    foreach ($interfaces as $interface) {
                        if (isset($interface['name']) && 
                            isset($interface['running']) && 
                            $interface['running'] === 'true' &&
                            (strpos($interface['name'], $wanInterface) !== false || $interface['name'] === $wanInterface)) {
                            
                            \Log::info("Found priority external interface: {$interface['name']}");
                            
                            // Monitor this interface only
                            $trafficQuery = (new Query('/interface/monitor-traffic'))
                                ->equal('interface', $interface['name'])
                                ->equal('once', '');
                            
                            $trafficData = $this->client->query($trafficQuery)->read();
                            
                            if (!empty($trafficData)) {
                                // Use this as our definitive external traffic source
                                $rxBitsPerSecond = isset($trafficData[0]['rx-bits-per-second']) ? 
                                    (int)$trafficData[0]['rx-bits-per-second'] : 0;
                                
                                $txBitsPerSecond = isset($trafficData[0]['tx-bits-per-second']) ? 
                                    (int)$trafficData[0]['tx-bits-per-second'] : 0;
                                
                                \Log::info("External interface {$interface['name']} traffic: RX={$rxBitsPerSecond} bps, TX={$txBitsPerSecond} bps");
                                
                                // Important: For external interfaces, RX is download and TX is upload from the user perspective
                                return [
                                    'rx-bits-per-second' => $rxBitsPerSecond,
                                    'tx-bits-per-second' => $txBitsPerSecond,
                                    'interface' => $interface['name'],
                                    'type' => 'external'
                                ];
                            }
                            
                            $wanInterfaceFound = true;
                            break;
                        }
                    }
                    
                    if ($wanInterfaceFound) {
                        break;
                    }
                }
                
                // If no WAN interface was found, fall back to all interfaces approach
                if (!$wanInterfaceFound) {
                    \Log::warning("No priority external interface found, summing all interfaces instead");
                    
                    // Get traffic from each interface that's running
                    foreach ($interfaces as $interface) {
                        if (isset($interface['name']) && isset($interface['running']) && $interface['running'] === 'true') {
                            $interfaceName = $interface['name'];
                            
                            // Skip loopback and internal interfaces
                            if (strpos($interfaceName, 'lo') === 0 || 
                                strpos($interfaceName, 'bridge') === 0 ||
                                strpos($interfaceName, 'vlan') === 0 ||
                                strpos($interfaceName, 'veth') === 0) {
                                continue;
                            }
                            
                            \Log::info("Monitoring traffic on interface: {$interfaceName}");
                            
                            // Get traffic for this interface
                            $trafficQuery = (new Query('/interface/monitor-traffic'))
                                ->equal('interface', $interfaceName)
                                ->equal('once', '');
                            
                            $trafficData = $this->client->query($trafficQuery)->read();
                            
                            if (!empty($trafficData)) {
                                // Add to total
                                $totalRxBitsPerSecond += isset($trafficData[0]['rx-bits-per-second']) ? 
                                    (int)$trafficData[0]['rx-bits-per-second'] : 0;
                                
                                $totalTxBitsPerSecond += isset($trafficData[0]['tx-bits-per-second']) ? 
                                    (int)$trafficData[0]['tx-bits-per-second'] : 0;
                                
                                \Log::debug("Interface {$interfaceName} traffic: RX=" . 
                                    ($trafficData[0]['rx-bits-per-second'] ?? 0) . " bps, TX=" . 
                                    ($trafficData[0]['tx-bits-per-second'] ?? 0) . " bps");
                            }
                        }
                    }
                    
                    \Log::info("Total network traffic (all interfaces): RX={$totalRxBitsPerSecond} bps, TX={$totalTxBitsPerSecond} bps");
                    
                    return [
                        'rx-bits-per-second' => $totalRxBitsPerSecond,
                        'tx-bits-per-second' => $totalTxBitsPerSecond,
                        'type' => 'all'
                    ];
                }
            } else {
                // Monitor specific interface
                $query = (new Query('/interface/monitor-traffic'))
                    ->equal('interface', $interfaceName)
                    ->equal('once', '');
                
                $trafficData = $this->client->query($query)->read();
                
                if (!empty($trafficData)) {
                    \Log::info('Successfully retrieved interface traffic data for ' . $interfaceName);
                    \Log::debug('Traffic data: ' . json_encode($trafficData[0]));
                    return array_merge($trafficData[0] ?? [], ['interface' => $interfaceName]);
                }
                
                \Log::warning('No interface traffic data returned for ' . $interfaceName);
                return [];
            }
        } catch (\Exception $e) {
            \Log::error('Failed to get interface traffic: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return [
                'rx-bits-per-second' => 0,
                'tx-bits-per-second' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
}
