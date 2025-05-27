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
}
