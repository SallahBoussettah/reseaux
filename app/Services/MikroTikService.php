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

    // Add a user to MikroTik hotspot
    public function addHotspotUser($username, $password, $profile)
    {
        $query = (new Query('/ip/hotspot/user/add'))
            ->equal('server', 'server1')
            ->equal('name', $password)
            ->equal('password', '123456789')
            ->equal('mac-address', $password)
            ->equal('profile', $profile);



        return $this->client->query($query)->read();
    }

    public function getUserByUsername($username)
    {
        $query = (new Query('/ip/hotspot/user/print'))
            ->where('name', $username);

        $response = $this->client->query($query)->read();

        // Return the first user found (assuming usernames are unique)
        return $response[0] ?? null;
    }

    // Update user profile (e.g., from free to premium)
    public function updateUserProfile($username, $profile)
    {
        // Step 1: Get the user by username
        $user = $this->getUserByUsername($username);

        if (!$user) {
            throw new \Exception("User not found: $username");
        }
        $query = (new Query('/ip/hotspot/user/set'))
            ->equal('.id', $user['.id'])
            ->equal('profile', $profile);

        return $this->client->query($query)->read();
    }

    // Ban a user by adding their IP to the address list
    public function banUser($macAddress)
    {
        $query = (new Query('/ip/firewall/address-list/add'))
            ->equal('address', $macAddress) // For MAC use 'src-mac-address'
            ->equal('list', 'banned_users');

        return $this->client->query($query)->read();
    }

}
