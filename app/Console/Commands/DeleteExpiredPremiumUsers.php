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
use Illuminate\Support\Facades\Mail;
use App\Models\Setting;

class DeleteExpiredPremiumUsers extends Command
{
    protected $signature = 'users:delete-expired';
    protected $description = 'Remove users from router configuration, mark them as deactivated, and update profile_type to expired in the database if scheduled deletion time has passed';

    // MikroTik connection details from config
    protected $mikrotikConfig = [];
    
    // Track statistics for reporting
    protected $stats = [
        'total_processed' => 0,
        'db_deactivated' => 0,
        'router_deleted' => 0,
        'emails_sent' => 0,
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

        $this->info('Found ' . $expiredUsers->count() . ' users scheduled for processing.');
        Log::info('Found ' . $expiredUsers->count() . ' users scheduled for processing.');
        
        // Debug - print out user details
        foreach ($expiredUsers as $user) {
            $this->info('User: ' . $user->email . ' (ID: ' . $user->id . ')');
            $this->info('  Status: ' . $user->status);
            $this->info('  Profile Type: ' . $user->profile_type);
            $this->info('  MAC Address: ' . ($user->mac_address ?? 'None'));
            $this->info('  Scheduled Deletion: ' . $user->scheduled_deletion_at);
        }
        
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
            // We'll continue with database updates even if MikroTik connection fails
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
                        // Continue with database update even if MikroTik removal fails
                    }
                }
                
                // Store user ID for logging
                $userId = $user->id;
                $userEmail = $user->email;
                
                // Update status to 'deactivated' (supported by schema) and profile_type to 'expired'
                $user->status = 'deactivated';
                $user->profile_type = 'expired';
                
                $this->info('Updating user in database: status = deactivated, profile_type = expired');
                Log::info('Updating user in database', [
                    'user_id' => $userId,
                    'email' => $userEmail,
                    'status' => 'deactivated', 
                    'profile_type' => 'expired'
                ]);
                
                $saved = $user->save();
                
                if ($saved) {
                    $this->info('Successfully saved user changes to database');
                    $this->stats['db_deactivated']++;
                } else {
                    $this->error('Failed to save user changes to database');
                    throw new \Exception('Database save failed for user ID: ' . $userId);
                }
                
                DB::commit();
                
                // After successfully disabling the user and removing from router, send feedback email
                try {
                    $this->sendFeedbackEmail($user);
                    $this->stats['emails_sent']++;
                    $this->info('Successfully sent feedback email to: ' . $userEmail);
                    Log::info('Successfully sent feedback email to: ' . $userEmail);
                } catch (\Exception $e) {
                    $this->error('Failed to send feedback email to ' . $userEmail . ': ' . $e->getMessage());
                    Log::error('Failed to send feedback email to ' . $userEmail . ': ' . $e->getMessage(), [
                        'user_id' => $userId,
                        'error' => $e->getMessage()
                    ]);
                    // Continue even if email sending fails
                }
                
                $this->info('Successfully disabled user: ' . $userEmail . ' (ID: ' . $userId . ')');
                Log::info('Successfully disabled user: ' . $userEmail . ' (ID: ' . $userId . ')');
            } catch (\Exception $e) {
                DB::rollBack();
                $this->stats['errors']++;
                $this->error('Failed to disable user ' . $user->email . ': ' . $e->getMessage());
                Log::error('Failed to disable user ' . $user->email . ': ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $executionTime = round(microtime(true) - $startTime, 2);
        
        // Output summary
        $this->info('');
        $this->info('=== Processing Summary ===');
        $this->info('Total users processed: ' . $this->stats['total_processed']);
        $this->info('Deactivated in database: ' . $this->stats['db_deactivated']);
        $this->info('Removed from router: ' . $this->stats['router_deleted']);
        $this->info('Feedback emails sent: ' . $this->stats['emails_sent']);
        $this->info('Errors encountered: ' . $this->stats['errors']);
        $this->info('Execution time: ' . $executionTime . ' seconds');
        
        Log::info('User processing completed', [
            'total_processed' => $this->stats['total_processed'],
            'db_deactivated' => $this->stats['db_deactivated'],
            'router_deleted' => $this->stats['router_deleted'],
            'emails_sent' => $this->stats['emails_sent'],
            'errors' => $this->stats['errors'],
            'execution_time' => $executionTime
        ]);
        
        return Command::SUCCESS;
    }
    
    /**
     * Send a feedback email to the user
     * 
     * @param \App\Models\Client $user
     * @return void
     */
    private function sendFeedbackEmail($user)
    {
        if (empty($user->email) || !filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Invalid or missing email for user ID: ' . $user->id);
            return;
        }

        // Skip sending email to token users
        if (strpos($user->email, 'token_user') !== false) {
            Log::info('Skipping feedback email for token user: ' . $user->email);
            return;
        }
        
        // Determine language based on user's language setting
        $language = $user->language ?? 'en';
        
        // Get the logo URL from settings
        $logo_url = Setting::get('email_logo_url', '');
        
        // Generate a verification token if not present
        if (empty($user->verification_token)) {
            $user->verification_token = substr(md5(rand(0, 9999) . time() . $user->email), 0, 32);
            $user->save();
        }
        
        // Generate a feedback URL with user identification parameters
        $feedback_url = url('/feedback') . '?email=' . urlencode($user->email) . '&token=' . urlencode($user->verification_token);
        
        // Determine which email template to use based on the language
        $emailTemplate = 'emails.feedback';
        $emailSubject = Setting::get('email_feedback_subject_en', 'Aqua Mirage Marrakech - Thank You for Your Stay');
        
        if ($language === 'fr') {
            $emailTemplate = 'emails.feedback_fr';
            $emailSubject = Setting::get('email_feedback_subject_fr', 'Aqua Mirage Marrakech - Merci pour votre séjour');
        } else {
            $emailTemplate = 'emails.feedback_en';
            $emailSubject = Setting::get('email_feedback_subject_en', 'Aqua Mirage Marrakech - Thank You for Your Stay');
        }
        
        // Check if the template exists
        if (!view()->exists($emailTemplate)) {
            Log::error('Email template does not exist', ['template' => $emailTemplate]);
            throw new \Exception("Email template not found: {$emailTemplate}");
        }
        
        // Prepare email data with all required variables that the template expects
        $emailData = [
            'language' => $language,
            'logo_url' => $logo_url,
            'hotel_name' => 'Aqua Mirage Marrakech',
            'subject' => $emailSubject,
            'greeting' => Setting::get($language === 'fr' ? 'email_feedback_greeting_fr' : 'email_feedback_greeting_en',
                $language === 'fr' ? 'Cher(e) Client(e),' : 'Dear Guest,'),
            'intro' => Setting::get($language === 'fr' ? 'email_feedback_intro_fr' : 'email_feedback_intro_en',
                $language === 'fr' ? 'Nous espérons que vous avez apprécié votre séjour' : 'We hope you enjoyed your stay'),
            'feedback_request' => Setting::get($language === 'fr' ? 'email_feedback_request_fr' : 'email_feedback_request_en',
                $language === 'fr' ? 'Nous apprécierions vos commentaires' : 'We would appreciate your feedback'),
            'button_text' => Setting::get($language === 'fr' ? 'email_feedback_button_text_fr' : 'email_feedback_button_text_en',
                $language === 'fr' ? 'Partagez Votre Avis' : 'Share Your Feedback'),
            'closing' => Setting::get($language === 'fr' ? 'email_feedback_closing_fr' : 'email_feedback_closing_en',
                $language === 'fr' ? 'Nous espérons vous accueillir à nouveau' : 'We hope to welcome you back soon'),
            'footer' => Setting::get($language === 'fr' ? 'email_feedback_footer_fr' : 'email_feedback_footer_en',
                $language === 'fr' ? '© 2025 Aqua Mirage Marrakech. Tous droits réservés.' : '© 2025 Aqua Mirage Marrakech. All rights reserved.'),
            'feedback_url' => $feedback_url
        ];
        
        Log::info('Preparing to send feedback email', [
            'email' => $user->email,
            'template' => $emailTemplate,
            'language' => $language
        ]);
        
        // Send email
        Mail::send($emailTemplate, $emailData, function ($message) use ($user, $emailSubject) {
            $message->to($user->email)
                ->subject($emailSubject)
                ->from(config('mail.from.address', 'hotel@aquamiragemarrakech.com'), 
                      config('mail.from.name', 'Aqua Mirage Marrakech'));
        });
        
        Log::info('Feedback email sent to: ' . $user->email . ' using template: ' . $emailTemplate);
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