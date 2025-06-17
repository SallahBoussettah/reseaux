<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use \RouterOS\Client as RouterOSAPI;
use \RouterOS\Query;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;
use App\Services\MikroTikService;
use \Carbon\Carbon;

class ClientController extends Controller
{

    public function __construct(MikroTikService $mikroTikService)
    {
        $this->mikroTikService = $mikroTikService;
    }

    public function index()
    {
      
        /*try {
            $client = new RouterOSAPI([
                'host' => '192.168.88.1',
                'user' => 'api',
                'pass' => 'admin',
                'port' => 8728,
                'timeout' => 30,
            ]);

            $query = new Query('/ip/hotspot/active/print');
            $users = $client->query($query)->read();
            dd($users);
        } catch (\Exception $e) {
            dd($e);
            return false; // Return false if there's an issue
        }*/
        return view('home');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients',
            'mac' => 'required',
            'gender' => 'required|in:male,female,other'
        ]);

        $mac_address = $request->input('mac');
        
        $validatedData['mac_address'] = $mac_address;

        // Generate a 6-digit verification token
        $verificationToken = mt_rand(100000, 999999);
        
        // Set token expiration to 15 minutes from now
        $tokenExpiration = now()->addMinutes(15);

        $acceptLanguage = $request->server('HTTP_ACCEPT_LANGUAGE');
        $language = explode(',', $acceptLanguage)[0];
        $language = explode(';', $language)[0];
        $language = substr($language, 0, 2); // Get the first two characters

        $validatedData['language'] = $language;

        // Initialize the Agent class
        $agent = new Agent();
        
        // Detect device type
        $deviceType = 'unknown';
        if ($agent->isMobile()) {
            if ($agent->is('iPhone')) {
                $deviceType = 'iPhone';
            } elseif ($agent->is('Samsung')) {
                $deviceType = 'Samsung';
            } else {
                $deviceType = 'Android';
            }
        } elseif ($agent->isDesktop()) {
            $deviceType = 'PC';
        } elseif ($agent->isTablet()) {
            $deviceType = 'Tablet';
        }
        
        // Store device type
        $validatedData['device_type'] = $deviceType;
        $validatedData['platform'] = $agent->platform();
        $validatedData['browser'] = $agent->browser();
        $validatedData['last_login_at'] = Carbon::now()->toDateTimeString();

        // Add verification token data
        $userData = array_merge($validatedData, [
            'verification_token' => (string)$verificationToken, // Explicitly cast to string
            'verification_token_expires_at' => $tokenExpiration,
            'verification_token_attempts' => 0,
            'verified' => false
        ]);
        
        // Log the data being saved
        \Log::info('Creating client with data:', [
            'verification_token' => $userData['verification_token'],
            'expires_at' => $userData['verification_token_expires_at']
        ]);

        // Store the client in the database with the verification token
        $clients = Client::create($userData);
        
        // Verify the token was saved
        $savedClient = Client::find($clients->id);
        \Log::info('Saved client verification data:', [
            'id' => $savedClient->id,
            'verification_token' => $savedClient->verification_token,
            'expires_at' => $savedClient->verification_token_expires_at
        ]);
        
        // Send verification email with token
        try {
            $verificationUrl = route('token.verification');
            
            // Determine which email template to use based on the user's language
            $emailTemplate = 'emails.token_verification';
            $emailSubject = \App\Models\Setting::get('email_verification_subject_en', 'Your WiFi Verification Code');
            
            if ($validatedData['language'] === 'fr') {
                $emailTemplate = 'emails.token_verification_fr';
                $emailSubject = \App\Models\Setting::get('email_verification_subject_fr', 'Votre code de vérification WiFi');
            } else {
                $emailTemplate = 'emails.token_verification_en';
                $emailSubject = \App\Models\Setting::get('email_verification_subject_en', 'Your WiFi Verification Code');
            }
            
            Mail::send($emailTemplate, [
                'verificationToken' => $verificationToken,
                'verificationUrl' => $verificationUrl,
                'language' => $validatedData['language']
            ], function ($message) use ($validatedData, $emailSubject) {
                $message->to($validatedData['email'])
                    ->subject($emailSubject)
                    ->from('wifi@eureka-communication.com');
            });
            
            // Log successful email sending attempt
            \Log::info('Verification token sent to: ' . $validatedData['email'] . ' with token: ' . $verificationToken . ' using template: ' . $emailTemplate);
            
            // Add a success message
            session()->flash('status', 'Verification code has been sent to your email. Please check your inbox.');
            
        } catch (\Exception $e) {
            // Log email sending error
            \Log::error('Failed to send verification email: ' . $e->getMessage());
            session()->flash('error', 'Failed to send verification email. Please try again.');
        }

        try {
            // Add user to MikroTik hotspot with free profile
            $this->mikroTikService->addHotspotUser($clients->email, $mac_address, 'free_user');

            $router_ip = '10.5.50.1';
            // Store the verification token in the session as a backup
            session(['backup_token' => $verificationToken]);
            session(['mac_address' => $mac_address]);
            
            // Instead of redirecting to the router, redirect to token verification page
            // with information needed to manually connect
            return redirect()->route('token.verification')->with([
                'email' => $clients->email,
                'token_generated' => true,
                'mac_address' => $mac_address,
                'router_ip' => $router_ip,
                'login_url' => 'http://' . $router_ip . '/login?username='.$mac_address.'&password=123456789&mac='.$mac_address
            ]);

        } catch (\Exception $e) {
            \Log::error('MikroTik API Error: ' . $e->getMessage());
            return response()->json(['error' => 'There was an issue processing your request.'], 500);
        }
    }
    
    public function finale(){
        try {
            // Get active users using MikroTikService
            $active_users = $this->mikroTikService->getActiveUsers();

            // Check if there are active users
            if (empty($active_users)) {
                return response()->json(['message' => 'No active users found']);
            }

            // Log the first active user
            \Log::info('Active user found: ' . json_encode($active_users[0]));

            // Remove the first active session
            $response = $this->mikroTikService->removeActiveSession($active_users[0]['.id']);

            // Return the response
            return response()->json(['message' => 'Session removed successfully', 'response' => $response]);

        } catch (\Exception $e) {
            \Log::error('Failed to manage active users: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to manage active users: ' . $e->getMessage()], 500);
        }
    }

    public function redirect2()
    {
        $mac_address = session('mac_address');
        if (!$mac_address) {
            // If MAC address is not in session, redirect to WiFi page
            return redirect('/wifi');
        }
        
        // Store the verification URL in session for later use
        session(['return_to_verification' => route('token.verification')]);
        
        // Redirect to MikroTik login for free access
        $loginUrl = "http://10.5.50.1/login?username=$mac_address&password=123456789&mac=$mac_address";
        
        // Add a success parameter to the URL that will be shown after login
        $successUrl = route('check.token');
        $loginUrl .= "&dst=" . urlencode($successUrl);
        
        return redirect($loginUrl);
    }

    public function showTokenVerification()
    {
        // Check if there's an email in the session (coming from form submission)
        $email = session('email');
        $mac_address = session('mac_address');
        $login_url = session('login_url');
        $router_ip = session('router_ip');
        $token = session('backup_token');
        $tokenRegistration = session('token_registration');
        $tempClientId = session('temp_client_id');
        
        // Initialize client data
        $client = null;
        $tempClient = null;
        
        // Check if we have a temporary client from token registration
        if ($tempClientId) {
            $tempClient = Client::find($tempClientId);
            if ($tempClient) {
                \Log::info('Found temporary client: ' . $tempClient->email);
                $mac_address = $tempClient->mac_address;
            }
        }
        
        // If we have an email, try to find the client
        if ($email) {
            $client = Client::where('email', $email)->first();
        }
        // If we have a token in the query string, try to find the client by token
        elseif (request()->has('token')) {
            $token = request()->get('token');
            $client = Client::where('verification_token', $token)->first();
        }
        
        // If coming from token registration and there's no client yet, show the token entry form
        if ($tokenRegistration && !$client) {
            $data = [
                'mac_address' => $tokenRegistration['mac_address'] ?? $mac_address,
                'token_registration' => true,
                'full_name' => $tokenRegistration['full_name'] ?? null,
                'temp_client' => $tempClient,
            ];
            
            return view('token_verification', $data);
        }
        
        // If no email in session and no token registration, it means the user is coming directly to enter an existing code
        if (!$email && !$tokenRegistration && !$client) {
            return view('token_verification');
        }
        
        $data = [
            'email' => $email,
            'mac_address' => $mac_address,
            'login_url' => $login_url,
            'router_ip' => $router_ip,
            'temp_client' => $tempClient
        ];
        
        // Add client data if available
        if ($client) {
            $data['client'] = $client;
            $data['attempts'] = $client->verification_token_attempts;
            $data['attempts_remaining'] = 5 - $client->verification_token_attempts;
            $data['successful_verifications'] = $client->successful_verifications ?? 0;
            $data['devices_remaining'] = 5 - ($client->successful_verifications ?? 0);
        }
        
        return view('token_verification', $data);
    }

    public function verifyToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string|size:6',
        ]);

        $token = $request->input('token');
        $backupToken = session('backup_token');
        $tempClientId = session('temp_client_id'); // Get temp client ID from session
        
        // Log the token received and the request details
        \Log::info('Token verification request received', [
            'token' => $token,
            'backup_token' => $backupToken,
            'temp_client_id' => $tempClientId,
            'user_agent' => $request->header('User-Agent'),
            'ip' => $request->ip()
        ]);

        try {
            // Find the client by the token
            $client = Client::where('verification_token', $token)
                            ->where('verification_token_expires_at', '>=', now())
                            ->first();

            // If not found by token in database, check if it matches the backup token in session
            if (!$client && $backupToken && $token == $backupToken) {
                // Try to find the most recent client without a verification token
                $client = Client::whereNull('verification_token')
                               ->orWhere('verification_token', '')
                               ->orderBy('created_at', 'desc')
                               ->first();
                               
                if ($client) {
                    \Log::info('Client found using backup token in session: ' . $client->email);
                }
            }

            // Check if the token is valid
            if (!$client) {
                \Log::warning('Invalid or expired verification token: ' . $token);
                return redirect()->route('token.verification')->withErrors(['token' => 'Invalid or expired verification token.']);
            }

            // Check if maximum attempts reached
            if ($client->verification_token_attempts >= 5) {
                \Log::warning('Maximum token attempts reached for: ' . $client->email);
                // Clear the token only when max attempts are reached
                $client->verification_token = null;
                $client->save();
                return redirect()->route('verification_failed')->withErrors(['error' => 'Maximum verification attempts reached. Please request a new token.']);
            }

            // Increment the attempts counter
            $client->verification_token_attempts += 1;
            $client->save();

            \Log::info('Client found for verification: ' . $client->email);

            // If we have token registration data in session and a temporary client
            $tokenRegistrationData = session('token_registration');
            $temporaryClient = null;
            
            if ($tempClientId) {
                $temporaryClient = Client::find($tempClientId);
            }
            
            // Check if the token is correct
            if ($client->verification_token == $token) {
                try {
                    // Determine which client record to update with premium access
                    $clientToUpdate = $temporaryClient ?: $client;
                    
                    if ($temporaryClient) {
                        \Log::info('Using temporary client for upgrade: ' . $temporaryClient->email);
                        
                        // Copy the verification token data from the original client to the temporary one
                        $temporaryClient->verification_token = $client->verification_token;
                        $temporaryClient->verification_token_expires_at = $client->verification_token_expires_at;
                    }
                    
                    // Update user profile in MikroTik using the client to update (temp or original)
                    $this->mikroTikService->updateUserProfile($clientToUpdate->mac_address, 'premium_user');
                    
                    // Mark the user as verified only if MikroTik update was successful
                    $clientToUpdate->email_verified_at = now();
                    
                    // Track successful verifications on both clients
                    $clientToUpdate->successful_verifications = ($clientToUpdate->successful_verifications ?? 0) + 1;
                    $client->successful_verifications = ($client->successful_verifications ?? 0) + 1;
                    
                    // Only clear the token if it's been used 5 times (from the original client)
                    if ($client->successful_verifications >= 5) {
                        $client->verification_token = null;
                        \Log::info('Token cleared after 5 successful verifications for: ' . $client->email);
                    } else {
                        \Log::info('Token used successfully ' . $client->successful_verifications . ' times for: ' . $client->email);
                    }
                    
                    // Update both clients
                    $clientToUpdate->premium_expires_at = now()->addDays((int)\App\Models\Setting::get('email_premium_duration_days', 7));
                    $clientToUpdate->profile_type = 'premium_user'; 
                    $clientToUpdate->scheduled_deletion_at = now()->addMinute(); // Schedule deletion after 1 minute (for testing)
                    $clientToUpdate->save();
                    
                    // Save the original client's updated verification count too
                    if ($client->id != $clientToUpdate->id) {
                        $client->save();
                    }
                    
                    \Log::info('Client email marked as verified: ' . $clientToUpdate->email);

                    // Clear the backup token and temp client ID from session
                    session()->forget(['backup_token', 'temp_client_id', 'token_registration']);

                    // Redirect to router with premium access
                    $router_ip = session('router_ip') ?? '10.5.50.1';
                    $mac_address = $clientToUpdate->mac_address;
                    
                    // Ensure MAC address has correct format for redirection
                    if (strpos($mac_address, 'IP_') === 0) {
                        \Log::warning("Using IP-based MAC address for redirection: $mac_address");
                    }
                    
                    // Format the redirect URL correctly
                    $redirect_url = 'http://' . $router_ip . '/login?username='.$mac_address.'&password=123456789&mac='.$mac_address;
                    $original_destination = \App\Models\Setting::get('redirection_url', 'https://eureka-digital.ma');
                    
                    \Log::info("Redirecting to router: $redirect_url with destination: $original_destination");
                    
                    return redirect($redirect_url . '&dst=' . urlencode($original_destination));
                } catch (\Exception $e) {
                    // If MikroTik update fails, don't clear the token so user can try again
                    \Log::error('MikroTik update failed: ' . $e->getMessage());
                    return redirect()->route('verification_failed')->withErrors(['error' => 'Failed to update your WiFi access. Please try again later.']);
                }
            } else {
                // If token doesn't match, increment attempts but keep the token
                \Log::warning('Invalid token provided for client: ' . $client->email);
                
                // Save the updated attempts count
                $client->save();
                
                $attemptsRemaining = 5 - $client->verification_token_attempts;
                return redirect()->route('token.verification')
                    ->withErrors(['token' => "Invalid verification token. Please try again. You have $attemptsRemaining attempts remaining."])
                    ->with(['email' => $client->email]);
            }

        } catch (\Exception $e) {
            // Log any unexpected exception
            \Log::error('Token verification error: ' . $e->getMessage());
            return redirect()->route('verification_failed')->withErrors(['error' => 'Failed to verify your token. Please try again later.']);
        }
    }

    public function verifyEmail(Request $request)
    {
        $token = $request->input('token');

        // Log the token received and the request details
        \Log::info('Legacy verification request received', [
            'token' => $token,
            'user_agent' => $request->header('User-Agent'),
            'ip' => $request->ip()
        ]);

        try {
            // Find the client by the token
            $client = Client::where('remember_token', $token)->first();

            // Check if the user exists and the token is valid
            if (!$client) {
                \Log::warning('Invalid or expired verification token: ' . $token);
                return redirect()->route('verification_failed')->withErrors(['error' => 'Invalid or expired verification token.']);
            }

            \Log::info('Client found for verification: ' . $client->email);

            // Mark the user as verified
            $client->email_verified_at = now();
            $client->remember_token = '';  // Clear the token to prevent reuse
            $client->premium_expires_at = now()->addDays((int)\App\Models\Setting::get('email_premium_duration_days', 7));
            $client->profile_type = 'premium_user'; // Set profile type to premium_user
            $client->scheduled_deletion_at = now()->addMinute(); // Schedule deletion after 1 minute (for testing)
            $client->save();
            \Log::info('Client email marked as verified: ' . $client->email);

            // Update user profile in MikroTik
            $this->mikroTikService->updateUserProfile($client->mac_address, 'premium_user');

            // Redirect to success if everything went well
            return redirect()->route('verification_success')->with('status', 'Your email has been verified, and your access has been upgraded!');

        } catch (\Exception $e) {
            // Log any unexpected exception
            \Log::error('MikroTik API Error for user ' . ($client->email ?? 'unknown') . ': ' . $e->getMessage());
            return redirect()->route('verification_failed')->withErrors(['error' => 'Failed to update your access. Please try again later.']);
        }
    }

    
    public function updateDataUsage(Request $request)
    {
        $validated = $request->validate([
            'mac_address' => 'required|string',
            'bytes_in' => 'required|numeric',
            'bytes_out' => 'required|numeric',
        ]);

        $client = Client::where('mac_address', $validated['mac_address'])->first();

        if ($client) {
            // Use increment to avoid race conditions
            $client->increment('total_downloaded_bytes', $validated['bytes_in']);
            $client->increment('total_uploaded_bytes', $validated['bytes_out']);
            
            // Log the update
            Log::info("Updated data usage for client {$client->id} with MAC {$validated['mac_address']}. Added {$validated['bytes_in']} downloaded bytes and {$validated['bytes_out']} uploaded bytes.");
            
            return response()->json(['success' => true, 'message' => 'Data usage updated successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Client not found'], 404);
    }

    /**
     * Test email sending functionality
     * This method is for debugging purposes only
     */
    public function testEmail(Request $request)
    {
        $email = $request->input('email') ?? 'wifi@eureka-communication.com';
        $token = bin2hex(random_bytes(16));
        $verificationLink = "https://wifi.eureka-communication.com/public/email/verify?token=$token";
        
        try {
            Mail::send('emails.verification', ['verificationLink' => $verificationLink], function ($message) use ($email) {
                $message->to($email)
                    ->subject('Test - Vérifiez votre adresse e-mail')
                    ->from('wifi@eureka-communication.com');
            });
            
            \Log::info('Test email sent to: ' . $email);
            return response()->json(['success' => true, 'message' => 'Test email sent to ' . $email]);
        } catch (\Exception $e) {
            \Log::error('Failed to send test email: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send test email: ' . $e->getMessage()]);
        }
    }

    public function checkToken(Request $request)
    {
        // Log all request parameters for debugging
        \Log::info('Router redirected to check.token with params:', $request->all());
        
        // Check for MAC address in router response
        $macAddress = null;
        $possibleMacParams = ['mac', 'client_mac', 'macaddr', 'mac-address', 'userMac'];
        
        foreach ($possibleMacParams as $param) {
            if ($request->has($param) && !empty($request->input($param))) {
                $macAddress = $request->input($param);
                \Log::info("Found MAC address in router response parameter '$param': $macAddress");
                break;
            }
        }
        
        // If no MAC found in parameters, try to find it in the URL pattern
        if (!$macAddress) {
            $url = $request->fullUrl();
            if (preg_match('/[?&]([0-9A-F]{2}[:-]){5}([0-9A-F]{2})(?:&|$)/i', $url, $matches)) {
                $macAddress = trim($matches[0], '?&');
                \Log::info("Found MAC address in URL pattern: $macAddress");
            }
        }
        
        // Check for token registration data in session
        $tokenRegistration = session('token_registration');
        
        // If we have token registration data and a MAC address, create the client record
        if ($tokenRegistration && $macAddress) {
            \Log::info('Creating new client from token registration with MAC: ' . $macAddress);
            
            // Generate a unique placeholder email to avoid duplicates
            $placeholderEmail = 'token_user_' . time() . '_' . uniqid() . '@placeholder.local';
            
            // Create a temporary client record 
            $tempClient = new Client([
                'full_name' => $tokenRegistration['full_name'],
                'email' => $placeholderEmail,
                'gender' => $tokenRegistration['gender'],
                'mac_address' => $macAddress,
                'device_type' => $tokenRegistration['device_type'] ?? 'unknown',
                'platform' => $tokenRegistration['platform'] ?? 'unknown',
                'browser' => $tokenRegistration['browser'] ?? 'unknown',
                'language' => $tokenRegistration['language'] ?? 'en',
                'verification_token_attempts' => 0,
                'last_login_at' => now()
            ]);
            
            $tempClient->save();
            
            try {
                // Add user to MikroTik hotspot with free profile (exactly like in the email registration flow)
                $this->mikroTikService->addHotspotUser($tempClient->email, $macAddress, 'free_user');
                
                // Update session with client ID and MAC address
                $tokenRegistration['client_id'] = $tempClient->id;
                $tokenRegistration['mac_address'] = $macAddress;
                $tokenRegistration['email'] = $placeholderEmail;
                
                session(['token_registration' => $tokenRegistration]);
                session(['mac_address' => $macAddress]);
                session(['temp_client_id' => $tempClient->id]);
                session(['router_ip' => '10.5.50.1']);
                
                \Log::info('Successfully created free user for token verification: ' . $placeholderEmail);
                
                // Now redirect to token verification page to enter token
                return redirect()->route('token.verification');
                
            } catch (\Exception $e) {
                \Log::error('Failed to create MikroTik user: ' . $e->getMessage());
                // Continue with the flow but log the error
            }
        }
        
        // Handle token checking for existing users or show redirect page
        if (!$request->has('token')) {
            $verificationUrl = session('return_to_verification') ?? route('token.verification');
            return view('redirect_to_verification', ['verificationUrl' => $verificationUrl]);
        }
        
        // Rest of the existing code for token checking
        $token = $request->input('token');
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'No token provided'
            ]);
        }
        
        // Find the client by the token
        $client = Client::where('verification_token', $token)->first();
        
        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Token not found in database'
            ]);
        }
        
        // Check if token is expired
        $isExpired = $client->verification_token_expires_at < now();
        
        // Check if maximum attempts reached
        $maxAttemptsReached = $client->verification_token_attempts >= 5;
        
        return response()->json([
            'success' => true,
            'token_found' => true,
            'client' => [
                'id' => $client->id,
                'email' => $client->email,
                'mac_address' => $client->mac_address,
                'token_expires_at' => $client->verification_token_expires_at,
                'token_attempts' => $client->verification_token_attempts,
                'is_expired' => $isExpired,
                'max_attempts_reached' => $maxAttemptsReached
            ]
        ]);
    }

    public function showTokenRegistration(Request $request)
    {
        // Log all request parameters to help debug
        \Log::info('Token registration page request parameters:', $request->all());
        \Log::info('Token registration page full URL: ' . $request->fullUrl());
        \Log::info('Token registration page headers:', $request->headers->all());
        
        // Use the comprehensive MAC address detection method
        $macAddress = $this->detectCurrentDeviceMacAddress($request);
        
        // Store MAC address in session if found and it's not an IP-based MAC
        if (!empty($macAddress) && strpos($macAddress, 'IP_') !== 0) {
            session(['mac_address' => $macAddress]);
            \Log::info('Real MAC address stored in session for token registration: ' . $macAddress);
            
            // Redirect with MAC parameter if it wasn't in the original request
            if (!$request->has('mac')) {
                return redirect()->route('token.registration', ['mac' => $macAddress]);
            }
        } else {
            \Log::warning('No real MAC address found in token registration request, using: ' . $macAddress);
        }
        
        return view('token_registration', ['mac_address' => $macAddress]);
    }

    public function processTokenRegistration(Request $request)
    {
        // Validate form data
        $validatedData = $request->validate([
            'full_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'mac' => 'nullable'
        ]);

        // Try to get MAC address from request first
        $macAddress = $request->input('mac');
        
        // If MAC is empty or looks like an IP address, try to detect it using our comprehensive method
        if (empty($macAddress) || strpos($macAddress, 'IP_') === 0) {
            $detectedMac = $this->detectCurrentDeviceMacAddress($request);
            
            // Only use the detected MAC if it's not an IP-based fallback
            if (!empty($detectedMac) && strpos($detectedMac, 'IP_') !== 0) {
                $macAddress = $detectedMac;
                \Log::info("Using detected MAC address: $macAddress");
            }
        }
        
        // If still no MAC or still IP-based, use the client's IP address as fallback
        if (empty($macAddress) || strpos($macAddress, 'IP_') === 0) {
            $clientIp = $request->ip();
            $macAddress = 'IP_' . $clientIp;
            \Log::info('Using IP address as MAC address: ' . $macAddress);
        }
        
        // Initialize the Agent class to detect device information
        $agent = new Agent();
        
        // Detect device type
        $deviceType = 'unknown';
        if ($agent->isMobile()) {
            if ($agent->is('iPhone')) {
                $deviceType = 'iPhone';
            } elseif ($agent->is('Samsung')) {
                $deviceType = 'Samsung';
            } else {
                $deviceType = 'Android';
            }
        } elseif ($agent->isDesktop()) {
            $deviceType = 'PC';
        } elseif ($agent->isTablet()) {
            $deviceType = 'Tablet';
        }

        // Detect language
        $acceptLanguage = $request->server('HTTP_ACCEPT_LANGUAGE');
        $language = explode(',', $acceptLanguage)[0];
        $language = explode(';', $language)[0];
        $language = substr($language, 0, 2); // Get the first two characters
        
        // Generate a unique placeholder email to avoid duplicates
        $placeholderEmail = 'token_user_' . time() . '_' . uniqid() . '@placeholder.local';
        
        // Create a temporary client record
        $tempClient = new Client([
            'full_name' => $validatedData['full_name'],
            'email' => $placeholderEmail,
            'gender' => $validatedData['gender'],
            'mac_address' => $macAddress,
            'device_type' => $deviceType,
            'platform' => $agent->platform(),
            'browser' => $agent->browser(),
            'language' => $language,
            'verification_token_attempts' => 0,
            'last_login_at' => now()
        ]);
        
        $tempClient->save();
        
        try {
            // Add user to MikroTik hotspot with free profile
            $this->mikroTikService->addHotspotUser($tempClient->email, $macAddress, 'free_user');
            
            // Router info
            $router_ip = '10.5.50.1';
            
            // Save user information in session for the verification step
            session([
                'token_registration' => [
                    'client_id' => $tempClient->id,
                    'full_name' => $validatedData['full_name'],
                    'gender' => $validatedData['gender'],
                    'mac_address' => $macAddress,
                    'email' => $placeholderEmail,
                    'device_type' => $deviceType,
                    'platform' => $agent->platform(),
                    'browser' => $agent->browser(),
                    'language' => $language
                ]
            ]);
            
            // Store MAC address and temp client ID in session
            session(['mac_address' => $macAddress]);
            session(['temp_client_id' => $tempClient->id]);
            
            // Store router info in session
            session([
                'router_ip' => $router_ip,
                'login_url' => 'http://' . $router_ip . '/login?username='.$macAddress.'&password=123456789&mac='.$macAddress
            ]);
            
            \Log::info('Created free user for token verification: ' . $placeholderEmail . ' with MAC: ' . $macAddress);
            
            // Directly redirect to token verification page
            return redirect()->route('token.verification');
            
        } catch (\Exception $e) {
            \Log::error('MikroTik API Error during token registration: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'There was an issue processing your request. Please try again.']);
        }
    }

    /**
     * Try to detect the MAC address of the current device connecting to the hotspot
     * This method is specific to router hotspot environments where the MAC is often
     * passed as a URL parameter by the captive portal
     */
    private function detectCurrentDeviceMacAddress(Request $request)
    {
        // Common parameter names used by MikroTik and other hotspot systems
        $macParams = ['mac', 'client_mac', 'macaddr', 'mac-address', 'userMac', 'mac_address'];
        
        // Check URL parameters first (most common for hotspot redirects)
        foreach ($macParams as $param) {
            if ($request->has($param) && !empty($request->input($param))) {
                $mac = $request->input($param);
                // Basic MAC address format validation
                if (preg_match('/^([0-9A-F]{2}[:-]){5}([0-9A-F]{2})$/i', $mac) || 
                    preg_match('/^([0-9A-F]{2}){6}$/i', $mac)) {
                    // Log the found MAC address and source
                    \Log::info("MAC address found in request parameter '$param': $mac");
                    return strtoupper($mac); // Return normalized MAC
                }
            }
        }
        
        // MikroTik sometimes adds MAC address to the query string without parameter name
        // Check for patterns like ?00:11:22:33:44:55 in the URL
        $url = $request->fullUrl();
        if (preg_match('/[?&]([0-9A-F]{2}[:-]){5}([0-9A-F]{2})(?:&|$)/i', $url, $matches)) {
            $mac = $matches[0];
            $mac = trim($mac, '?&');
            \Log::info("MAC address found in URL pattern: $mac");
            return strtoupper($mac);
        }
        
        // Check for custom headers that might contain MAC address
        // MikroTik often uses custom headers
        $allHeaders = $request->headers->all();
        foreach ($allHeaders as $headerName => $headerValue) {
            if (is_array($headerValue)) {
                $headerValue = implode(',', $headerValue);
            }
            
            if (preg_match('/([0-9A-F]{2}[:-]){5}([0-9A-F]{2})/i', $headerValue, $matches)) {
                $mac = $matches[0];
                \Log::info("MAC address found in header '$headerName': $mac");
                return strtoupper($mac);
            }
        }
        
        // Check if the client IP is the same as the router's internal network
        // This is common when devices are directly connected to the router
        $clientIp = $request->ip();
        $routerNetworks = ['10.5.50.', '192.168.88.']; // Common MikroTik internal networks
        
        foreach ($routerNetworks as $network) {
            if (strpos($clientIp, $network) === 0) {
                // This is an internal IP, try to get MAC from ARP table
                // For now, just prefix with "IP_" to indicate it's an internal address
                \Log::info("Client IP is in router network: $clientIp");
                return 'IP_' . $clientIp;
            }
        }
        
        // If we still don't have a MAC, use the IP address with a prefix
        \Log::info("No MAC address found, using IP address: $clientIp");
        return 'IP_' . $clientIp;
    }

    /**
     * Legacy MAC address detection method
     * Now simply delegates to the more comprehensive detectCurrentDeviceMacAddress method
     */
    private function detectMacAddress(Request $request)
    {
        // Use the comprehensive detection method
        return $this->detectCurrentDeviceMacAddress($request);
    }

}