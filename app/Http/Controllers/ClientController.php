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

        // Generate token
        $token = bin2hex(random_bytes(16));

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

        // Store the client in the database with the token
        $clients = Client::create(array_merge($validatedData, ['remember_token' => $token, 'verified' => false]));
        

        // Send verification email
        $verificationLink = "https://wifi.eureka-communication.com/public/email/verify?token=$token";
        
        try {
            Mail::send('emails.verification', ['verificationLink' => $verificationLink], function ($message) use ($validatedData) {
                $message->to($validatedData['email'])
                    ->subject('Vérifiez votre adresse e-mail')
                    ->from('wifi@eureka-communication.com');
            });
            
            // Log successful email sending attempt
            \Log::info('Verification email sent to: ' . $validatedData['email'] . ' with token: ' . $token);
            
        } catch (\Exception $e) {
            // Log email sending error
            \Log::error('Failed to send verification email: ' . $e->getMessage());
        }


        


        /*$client_ip = $request->ip();

        $profile = 'free_user';*/
        try {
            // Add user to MikroTik hotspot with free profile
            $this->mikroTikService->addHotspotUser($clients->email, $mac_address, 'free_user');

           /* $client = new RouterOSAPI([
                'host' => '192.168.88.1',
                'user' => 'api',
                'pass' => 'admin',
                'port' => 8728,
                'timeout' => 30,
            ]);

            // Add user to MikroTik with 5-minute access
            $add_user_query = (new Query('/ip/hotspot/user/add'))
                ->equal('server', 'server1')
                ->equal('name', $clients->email) // Use email as the username
                ->equal('mac-address', $mac_address)
                ->equal('profile', $profile);  // Set time limit for 5 minutes
            $client->query($add_user_query)->read();*/



            $router_ip = '10.5.50.1';
            $redirect_url = 'http://' . $router_ip . '/login?username='.$mac_address.'&password=123456789&mac='.$mac_address;
            //$redirect_url = 'http://' . $router_ip . '/login?username='.$mac_address.'&password=123456789&mac='.$mac_address;
            $original_destination = 'http://www.eureka-digital.ma';
            //return redirect($original_destination);
            return redirect($redirect_url . '&dst=' . urlencode($original_destination));
           // return redirect()->back()->with('success', 'Please check your email to verify and upgrade to premium plan.');

        } catch (\Exception $e) {
            \Log::error('MikroTik API Error: ' . $e->getMessage());
            return response()->json(['error' => 'There was an issue processing your request.'], 500);
        }
    }
    
    public function finale(){
        try {
        // Connect to MikroTik API
        $mikrotikClient = new RouterOSAPI([
            'host' => '192.168.88.1',
            'user' => 'api',
            'pass' => 'admin',
            'port' => 8728,
            'timeout' => 30,
        ]);

        // Query the active users
        $active_users_query = new Query('/ip/hotspot/active/print');
        $active_users = $mikrotikClient->query($active_users_query)->read();

        // Check if there are active users
        if (empty($active_users)) {
            return response()->json(['message' => 'No active users found']);
        }

        var_dump($active_users[0]);

        $remove_old_session_query = new Query('/ip/hotspot/active/remove');
        $remove_old_session_query->equal('.id', $active_users[0]['.id']);
        $response = $mikrotikClient->query($remove_old_session_query)->read();


        dd($response);
        // Return the list of active users
        return response()->json(['active_users' => $active_users]);

        } catch (\Exception $e) {
            \Log::error('Failed to retrieve active users: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve active users'], 500);
        }
    }

    public function redirect2(){
        $loginUrl  = "http://10.5.50.1/login?username=verifieduser&password=verifieduser";
        return redirect($loginUrl);
    }

    public function verifyEmail(Request $request)
    {
        $token = $request->input('token');

        // Log the token received and the request details
        \Log::info('Verification request received', [
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

            // Set profile to premium_user
            $profile = 'premium_user';
            
           /* // Connect to MikroTik API
            $mikrotikClient = new RouterOSAPI([
                'host' => '192.168.88.1',
                'user' => 'api',
                'pass' => 'admin',
                'port' => 8728,
                'timeout' => 30,
            ]);
            \Log::info('Connected to MikroTik API for client: ' . $client->email);

            // Check if the user exists in MikroTik before updating the profile
            $find_user_query = new Query('/ip/hotspot/user/print');
            $find_user_query->where('name', $client->email);
            $user_info = $mikrotikClient->query($find_user_query)->read();
            \Log::info('User Info from MikroTik: ' . json_encode($user_info));

            if (empty($user_info)) {
                \Log::warning('User not found in MikroTik: ' . $client->email);
                return redirect()->route('verification_failed')->withErrors(['error' => 'User not found in MikroTik.']);
            }

            // Get the user's ID from the response and update the profile
            $user_id = $user_info[0]['.id'];
            $update_profile_query = new Query('/ip/hotspot/user/set');
            $update_profile_query->equal('.id', $user_id)  // Use the user's ID
                                 ->equal('profile', $profile);  // Assign the new profile
            $response = $mikrotikClient->query($update_profile_query)->read();
            \Log::info('MikroTik API Response for Profile Update: ' . json_encode($response));


            // Find and remove active session to apply the new profile
            $find_active_user_query = new Query('/ip/hotspot/active/print');
            $find_active_user_query->where('user', $client->mac_address);
            $active_session = $mikrotikClient->query($find_active_user_query)->read();
            \Log::info('Active Session Info: ' . json_encode($active_session));

            if (!empty($active_session)) {
                $session_id = $active_session[0]['.id'];  // Get the session ID

                $router_ip = '10.5.50.1';
                $redirect_url = 'http://' . $router_ip . '/login?username=' . $client->email . '&password=&mac=' . $client->mac_address;
                $original_destination = 'https://eureka-digital.ma';

                \Log::info('Redirecting user to MikroTik login page: ' . $redirect_url);
                return redirect($redirect_url . '&dst=' . urlencode($original_destination));
            } else {
                \Log::warning('No active session found for user: ' . $client->email);
            }*/
            // Update user profile in MikroTik
            
            $this->mikroTikService->updateUserProfile($client->mac_address, 'premium_user');

    //return redirect()->route('captive.portal')->with('success', 'Email verified! You now have premium access.');

            // Redirect to success if everything went well
            return redirect()->route('verification_success')->with('status', 'Your email has been verified, and your access has been upgraded!');

        } catch (\Exception $e) {
            // Log any unexpected exception
            \Log::error('MikroTik API Error for user ' . $client->email . ': ' . $e->getMessage());
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

}
