<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use Carbon\Carbon;
use RouterOS\Query;
use RouterOS\Client as RouterOSAPI;
use Illuminate\Support\Facades\Log;

class CheckExpiredPremiumUsers extends Command
{
    protected $signature = 'premium:check-expired';
    protected $description = 'Check for expired premium users, downgrade them, and schedule them for deletion';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // This command works with any premium duration configured in the settings
        // The premium_expires_at field is set based on the "email_premium_duration_days" setting (default: 7 days)
        // So this implementation works whether the premium lasts for 7 days or any other configured period
        
        // Get users whose premium has expired but are still marked as premium
        $expiredPremiumUsers = Client::where('profile_type', 'premium_user')
                                    ->whereNotNull('premium_expires_at')
                                    ->where('premium_expires_at', '<', Carbon::now())
                                    ->whereNull('scheduled_deletion_at')
                                    ->get();
                                    
        \Log::info('Found ' . $expiredPremiumUsers->count() . ' expired premium users to revert to free users');

        if ($expiredPremiumUsers->count() === 0) {
            return Command::SUCCESS;
        }
        
        // Connect to MikroTik API
        try {
            $mikrotikClient = new RouterOSAPI([
                'host' => env('MIKROTIK_HOST', '10.5.50.1'),  // Default to common router IP
                'user' => env('MIKROTIK_USER', 'api'),
                'pass' => env('MIKROTIK_PASS', 'Erekapp314'),
                'port' => (int)env('MIKROTIK_PORT', 8728),
                'timeout' => 30,
            ]);
            \Log::info('Successfully connected to MikroTik router');
        } catch (\Exception $e) {
            \Log::error('Failed to connect to MikroTik API: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // Process each expired user
        foreach ($expiredPremiumUsers as $user) {
            try {
                \Log::info('Processing expired premium user: ' . $user->email);
                
                // Step 1: Update user profile in MikroTik if they have a MAC address
                if ($user->mac_address) {
                    $find_user_query = new Query('/ip/hotspot/user/print');
                    $find_user_query->where('name', $user->mac_address);
                    $user_info = $mikrotikClient->query($find_user_query)->read();
                    
                    if (empty($user_info)) {
                        \Log::warning('User not found in MikroTik by name: ' . $user->mac_address);
                    } else {
                        \Log::info('Found user in MikroTik: ' . $user->mac_address);
                        $user_id = $user_info[0]['.id'];  // Get the user's ID from MikroTik
                        
                        // Update the user's profile in MikroTik
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
                    }
                }

                // Step 4: Update the user record in the database
                $user->premium_expires_at = null;  // Clear premium expiration
                $user->profile_type = 'free_user';  // Revert to free user
                
                // Schedule the user for deletion in 24 hours
                $user->scheduled_deletion_at = Carbon::now()->addHours(24);
                \Log::info('Scheduled user for deletion at: ' . $user->scheduled_deletion_at);
                
                $saved = $user->save();
                if ($saved) {
                    \Log::info('Successfully saved database changes for user: ' . $user->email, [
                        'user_id' => $user->id,
                        'profile_type' => $user->profile_type,
                        'scheduled_deletion_at' => $user->scheduled_deletion_at
                    ]);
                } else {
                    \Log::error('Failed to save database changes for user: ' . $user->email);
                }
                \Log::info('User reverted to free plan and scheduled for deletion: ' . $user->email);

            } catch (\Exception $e) {
                \Log::error('MikroTik API Error for user ' . $user->email . ': ' . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
