<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use Carbon\Carbon;
use RouterOS\Query;
use RouterOS\RouterOSAPI;

class CheckExpiredPremiumUsers extends Command
{
    protected $signature = 'premium:check-expired';
    protected $description = 'Check users whose premium plan has expired and revert them to the free plan';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Find all users whose premium plan has expired
        $expiredUsers = Client::where('premium_expires_at', '<', Carbon::now())->get();

        // Loop through each expired user
        foreach ($expiredUsers as $user) {
            try {
                \Log::info('Reverting user to free plan: ' . $user->email);

                // Connect to MikroTik API
                $mikrotikClient = new RouterOSAPI([
                    'host' => '192.168.88.1',
                    'user' => 'api',
                    'pass' => 'admin',
                    'port' => 8728,
                    'timeout' => 30,
                ]);

                // Step 1: Find the user in MikroTik by MAC address
                $find_user_query = new Query('/ip/hotspot/user/print');
                $find_user_query->where('name', $user->email);
                $user_info = $mikrotikClient->query($find_user_query)->read();
                \Log::info('User Info from MikroTik: ' . json_encode($user_info));

                if (empty($user_info)) {
                    \Log::warning('User not found in MikroTik: ' . $user->email);
                    continue;  // Skip this user if not found in MikroTik
                }

                // Step 2: Update user's profile to free_user
                $user_id = $user_info[0]['.id'];
                $update_profile_query = new Query('/ip/hotspot/user/set');
                $update_profile_query->equal('.id', $user_id)
                                     ->equal('profile', 'free_user');  // Revert to free plan
                $mikrotikClient->query($update_profile_query)->read();
                \Log::info('MikroTik API Response for Profile Update: User reverted to free_user profile');

                // Step 3: Find and remove the active session if the user is connected
                $find_active_user_query = new Query('/ip/hotspot/active/print');
                $find_active_user_query->where('user', $user->mac_address);
                $active_session = $mikrotikClient->query($find_active_user_query)->read();
                \Log::info('Active Session Info: ' . json_encode($active_session));

                if (!empty($active_session)) {
                    $session_id = $active_session[0]['.id'];  // Get the session ID

                    // Remove the active session to force re-login with the free plan
                    $remove_session_query = new Query('/ip/hotspot/active/remove');
                    $remove_session_query->equal('.id', $session_id);
                    $mikrotikClient->query($remove_session_query)->read();
                    \Log::info('Removed active session for user: ' . $user->email);
                }

                // Step 4: Update the user record in the database
                $user->premium_expires_at = null;  // Clear premium expiration
                $user->save();
                \Log::info('User reverted to free plan in database: ' . $user->email);

            } catch (\Exception $e) {
                \Log::error('MikroTik API Error for user ' . $user->email . ': ' . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
