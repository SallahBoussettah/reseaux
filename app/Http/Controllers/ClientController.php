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
            Mail::send('emails.token_verification', [
                'verificationToken' => $verificationToken,
                'verificationUrl' => $verificationUrl
            ], function ($message) use ($validatedData) {
                $message->to($validatedData['email'])
                    ->subject('Votre code de vérification WiFi')
                    ->from('wifi@eureka-communication.com');
            });
            
            // Log successful email sending attempt
            \Log::info('Verification token sent to: ' . $validatedData['email'] . ' with token: ' . $verificationToken);
            
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
        
        // If no email in session, it means the user is coming directly to enter an existing code
        if (!$email) {
            return view('token_verification');
        }
        
        return view('token_verification', [
            'email' => $email,
            'mac_address' => $mac_address,
            'login_url' => $login_url,
            'router_ip' => $router_ip
        ]);
    }

    public function verifyToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string|size:6',
        ]);

        $token = $request->input('token');
        $backupToken = session('backup_token');
        
        // Log the token received and the request details
        \Log::info('Token verification request received', [
            'token' => $token,
            'backup_token' => $backupToken,
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
                return redirect()->route('verification_failed')->withErrors(['error' => 'Maximum verification attempts reached. Please request a new token.']);
            }

            // Increment the attempts counter
            $client->verification_token_attempts += 1;
            $client->save();

            \Log::info('Client found for verification: ' . $client->email);

            try {
                // Update user profile in MikroTik
                $this->mikroTikService->updateUserProfile($client->mac_address, 'premium_user');
                
                // Mark the user as verified only if MikroTik update was successful
                $client->email_verified_at = now();
                $client->verification_token = null;  // Clear the token to prevent reuse
                $client->premium_expires_at = now()->addDays(7);
                $client->save();
                
                \Log::info('Client email marked as verified: ' . $client->email);

                // Clear the backup token from session
                session()->forget('backup_token');

                // Redirect to router with premium access
                $router_ip = '10.5.50.1';
                $redirect_url = 'http://' . $router_ip . '/login?username='.$client->mac_address.'&password=123456789&mac='.$client->mac_address;
                $original_destination = 'https://eureka-digital.ma';
                
                return redirect($redirect_url . '&dst=' . urlencode($original_destination));
            } catch (\Exception $e) {
                // If MikroTik update fails, don't clear the token so user can try again
                \Log::error('MikroTik update failed: ' . $e->getMessage());
                return redirect()->route('verification_failed')->withErrors(['error' => 'Failed to update your WiFi access. Please try again later.']);
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
            $client->premium_expires_at = now()->addDays(7);
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

    
    public function updateDataUsage($macAddress)
    {
        // Connect to MikroTik API
        $mikrotikClient = new RouterOSAPI([
            'host' => '192.168.88.1',
            'user' => 'api',
            'pass' => 'admin',
            'port' => 8728,
            'timeout' => 30,
        ]);

        // Fetch the active session for the user by MAC address
        $query = new Query('/ip/hotspot/active/print');
        $query->where('mac-address', $macAddress);
        $activeSession = $mikrotikClient->query($query)->read();

        if (!empty($activeSession)) {
            $bytes_in = $activeSession[0]['bytes-in'];  // Data downloaded by the user
            $bytes_out = $activeSession[0]['bytes-out']; // Data uploaded by the user
            $total_bytes = $bytes_in + $bytes_out;  // Total data usage in bytes

            // Update the user's data usage in the database
            $client = Client::where('mac_address', $macAddress)->first();
            if ($client) {
                $client->data_usage += $total_bytes;
                $client->save();
            }
        }
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
        // If no token parameter is provided, show a page that redirects to verification
        if (!$request->has('token')) {
            $verificationUrl = session('return_to_verification') ?? route('token.verification');
            return view('redirect_to_verification', ['verificationUrl' => $verificationUrl]);
        }
        
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

}
