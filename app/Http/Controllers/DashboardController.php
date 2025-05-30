<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Exports\ClientsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use \RouterOS\Client as RouterOSAPI;
use \RouterOS\Query;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
            // Fetch total number of users
        $totalUsers = Client::count();

        // Fetch number of users connected this month
        $connectedThisMonth = Client::whereMonth('created_at', now()->month)->count();

        // Fetch number of new users this month
        $newUsers = Client::whereMonth('created_at', now()->month)->whereNotNull('email_verified_at')->count();

        // Fetch connected users this week (from MikroTik active sessions)
       /* $mikrotikClient = new RouterOSAPI([
            'host' => '192.168.88.1',
            'user' => 'api',
            'pass' => 'admin',
            'port' => 8728,
            'timeout' => 30,
        ]);*/

        // Fetch active sessions
        $active_sessions_query = new Query('/ip/hotspot/active/print');
       // $activeSessions = $mikrotikClient->query($active_sessions_query)->read();
        $connectedThisWeek = 5;
        $activeSessions =[];

        /*$totalClients = Client::count();
        $totalMale = Client::where('gender', 'male')->count();
        $totalFemale = Client::where('gender', 'female')->count();*/
        //$recentClients = Client::latest()->take(10)->get();

        // Get data for the last 7 days (daily connections)
        $dailyConnections = Client::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->whereBetween('created_at', [now()->subDays(7), now()])
            ->get();

        // Prepare data for the chart
        $categories = $dailyConnections->pluck('date')->map(function ($date) {
                            return \Carbon\Carbon::parse($date)->format('D, d M');
                        })->toArray();
        $data = $dailyConnections->pluck('total')->toArray();

        $latestConnections = Client::orderBy('last_login_at', 'desc')->take(10)->get();
        $demographics = Client::select('language', DB::raw('count(*) as total'))
                ->groupBy('language')
                ->orderBy('total', 'desc')
                ->get();

        // Get the counts of device types in the last 7 days
        $deviceTypes = Client::select('device_type', DB::raw('count(*) as total'))
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('device_type')
            ->get();

        // Prepare data for the chart
        $labels = $deviceTypes->pluck('device_type')->toArray();
        $datadevice = $deviceTypes->pluck('total')->toArray();

        return view('dashboard.index', compact('totalUsers', 'connectedThisMonth', 'newUsers', 'connectedThisWeek','dailyConnections','activeSessions','latestConnections','demographics','categories', 'data','datadevice','labels'));
    }

    public function clients()
    {
       /* $users = Client::all(); // Fetch users from the database

        // Connect to MikroTik to get active sessions
        $mikrotikClient = new RouterOSAPI([
            'host' => '192.168.88.1',
            'user' => 'api',
            'pass' => 'admin',
            'port' => 8728,
            'timeout' => 30,
        ]);

        // Fetch active sessions
        $active_sessions_query = new Query('/ip/hotspot/active/print');
        $active_sessions = $mikrotikClient->query($active_sessions_query)->read();*/

        $clients = Client::select('id', 'full_name', 'email', 'mac_address', 'device_type', 'platform', 'login_count', 'premium_expires_at', 'status', 'profile_type', 'scheduled_deletion_at')
        ->orderBy('created_at', 'desc')
        ->paginate(10);  // Paginate if you have many clients


        return view('dashboard.clients', compact('clients'));
    }

    public function statistics()
    {
        // Start with empty default values
        $activeConnections = [];
        $averageRxRate = '0 bps';
        $averageTxRate = '0 bps';
        $totalRxRateFormatted = '0 bps';
        $totalTxRateFormatted = '0 bps';
        
        // Use cached statistics data if available (cache for 1 hour)
        $statistics = Cache::remember('statistics_data', now()->addHour(), function () {
            return Client::select(
                // Daily active users (last 30 days) - this is simpler than grouping by hour
                DB::raw('COUNT(DISTINCT email) as daily_active_users'),
                
                // Monthly active users (current month)
                DB::raw('SUM(CASE WHEN MONTH(last_login_at) = MONTH(CURDATE()) THEN 1 ELSE 0 END) as monthly_active_users'),

                // Total bandwidth usage per user (sum of data_usage)
                DB::raw('SUM(data_usage) as total_bandwidth_usage'),

                // Retention rate (users with more than one login)
                DB::raw('SUM(CASE WHEN login_count > 1 THEN 1 ELSE 0 END) as returning_users')
            )
            ->whereBetween('last_login_at', [now()->subDays(30), now()]) // Restrict to the last 30 days
            ->get();
        });
        
        // Fetch user activity by hour in a separate query and cache it (cache for 1 day)
        $usersByHour = Cache::remember('users_by_hour', now()->addDay(), function () {
            return Client::select(
                DB::raw('HOUR(last_login_at) as hour_of_day, COUNT(*) as users_by_hour')
            )
            ->whereBetween('last_login_at', [now()->subDays(30), now()])
            ->groupBy(DB::raw('HOUR(last_login_at)'))
            ->get()
            ->pluck('users_by_hour', 'hour_of_day')
            ->toArray();
        });
        
        // Fill in missing hours with zeros
        $completeUsersByHour = [];
        for ($i = 0; $i < 24; $i++) {
            $completeUsersByHour[$i] = $usersByHour[$i] ?? 0;
        }
        
        // Use cached bandwidth usage data (cache for 1 hour)
        $bandwidthUsagePerUser = Cache::remember('bandwidth_usage_per_user', now()->addHour(), function () {
            return Client::select(
                'full_name',
                'data_usage',
                'email'
            )
            ->orderByDesc('data_usage')
            ->take(10)
            ->get();
        });
        
        // Check if we have cached MikroTik data
        if (Cache::has('bandwidth_data')) {
            $cachedData = Cache::get('bandwidth_data');
            if (isset($cachedData['connections'])) {
                $activeConnections = $cachedData['connections'];
            }
            if (isset($cachedData['totalRx'])) {
                $totalRxRateFormatted = $cachedData['totalRx'];
            }
            if (isset($cachedData['totalTx'])) {
                $totalTxRateFormatted = $cachedData['totalTx'];
            }
            if (isset($cachedData['averageRx'])) {
                $averageRxRate = $cachedData['averageRx'];
            }
            if (isset($cachedData['averageTx'])) {
                $averageTxRate = $cachedData['averageTx'];
            }
        }
        
        // Add the hourly data to the statistics collection
        if (!empty($statistics) && $statistics->count() > 0) {
            $statistics->first()->users_by_hour = $completeUsersByHour;
        }

        // Pass both historical and real-time data to the view
        return view('dashboard.statistics', compact(
            'statistics',
            'bandwidthUsagePerUser',
            'activeConnections',
            'averageRxRate',
            'averageTxRate',
            'totalRxRateFormatted',
            'totalTxRateFormatted'
        ));
    }
    
    // Helper function to format bandwidth
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

    public function exportClients()
    {
        return Excel::download(new ClientsExport, 'clients.xlsx');
    }

    public function deactivateUser($client_id)
    {
        // Find the client by ID
        $client = Client::find($client_id);

        if (!$client) {
            return redirect()->back()->withErrors(['error' => 'Client not found']);
        }

        try {
            // Connect to MikroTik API
            $mikrotikClient = new RouterOSAPI([
                'host' => '192.168.88.1',
                'user' => 'api',
                'pass' => 'admin',
                'port' => 8728,
                'timeout' => 30,
            ]);

            // Step 1: Update user's profile to 'banned_user' in MikroTik
            $find_user_query = new Query('/ip/hotspot/user/print');
            $find_user_query->where('name', $client->email);  // Assuming you're using email as the username
            $user_info = $mikrotikClient->query($find_user_query)->read();

            if (!empty($user_info)) {
                $user_id = $user_info[0]['.id'];  // Get the user's ID from MikroTik

                // Update the MikroTik user profile to 'banned_user'
                $update_profile_query = new Query('/ip/hotspot/user/set');
                $update_profile_query->equal('.id', $user_id)
                                     ->equal('profile', 'banned_user');  // Change profile to banned_user
                $mikrotikClient->query($update_profile_query)->read();
            }

            // Step 2: Get the user's active session to retrieve their IP address
            $find_active_user_query = new Query('/ip/hotspot/active/print');
            $find_active_user_query->where('user', $client->mac_address);  // Assuming the email is used
            $active_session = $mikrotikClient->query($find_active_user_query)->read();

            if (!empty($active_session)) {
                $client_ip = $active_session[0]['address'];  // Get the client's IP address from the active session

                // Step 3: Add the client's IP to the 'banned_users' address list in MikroTik
                $add_to_address_list = new Query('/ip/firewall/address-list/add');
                $add_to_address_list->equal('list', 'banned_users')  // The address list name
                                    ->equal('address', $client_ip)  // Use the client's IP address
                                    ->equal('comment', 'Banned User');
                $mikrotikClient->query($add_to_address_list)->read();
                
                \Log::info('Added client IP to banned_users list: ' . $client_ip);

                // Step 4: Clear the user's active session
                $session_id = $active_session[0]['.id'];
                $remove_session_query = new Query('/ip/hotspot/active/remove');
                $remove_session_query->equal('.id', $session_id);
                $mikrotikClient->query($remove_session_query)->read();

                \Log::info('Removed active session for user: ' . $client->mac_address);
            } else {
                \Log::info('No active session found for user: ' . $client->mac_address);
            }

            // Step 5: Update the client's status in the database
            $client->status = 'deactivated';
            $client->save();

            return redirect()->back()->with('status', 'User deactivated and blocked from the network.');

        } catch (\Exception $e) {
            \Log::error('MikroTik API Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to deactivate user. Please try again later.']);
        }
    }

    public function testDeleteExpiredUsers()
    {
        try {
            // Call the command directly
            \Artisan::call('users:delete-expired');
            
            // Get the output from the command
            $output = \Artisan::output();
            
            return response()->json([
                'success' => true,
                'message' => 'Command executed successfully',
                'details' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error executing command: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function manuallyScheduleUserForDeletion($client_id)
    {
        // Find the client by ID
        $client = \App\Models\Client::find($client_id);
        
        if (!$client) {
            return redirect()->back()->withErrors(['error' => 'Client not found']);
        }
        
        // Schedule the client for deletion in 1 minute
        $client->profile_type = 'premium_user';
        $client->scheduled_deletion_at = now()->addMinute();
        $client->save();
        
        return redirect()->back()->with('success', 'User scheduled for deletion in 1 minute');
    }

    /**
     * Get real-time bandwidth data for AJAX requests
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBandwidthData()
    {
        try {
            // Check if we have a cached version (cache for 5 seconds to prevent hammering the router)
            if (Cache::has('bandwidth_data') && !request()->has('force_refresh')) {
                return response()->json(Cache::get('bandwidth_data'));
            }
            
            // Connect to MikroTik with appropriate settings from the environment
            $mikrotikClient = new RouterOSAPI([
                'host' => env('MIKROTIK_HOST', 'eurekadigital.ddns.net'),
                'user' => env('MIKROTIK_USER', 'api'),
                'pass' => env('MIKROTIK_PASS', 'Erekapp314'),
                'port' => (int)env('MIKROTIK_PORT', 8728),
                'timeout' => 5, // Reduced timeout to avoid long waits
            ]);
            
            // Create MikroTik service instance
            $mikrotikService = new \App\Services\MikroTikService($mikrotikClient);
            
            // Test connection to ensure it's working
            if (!$mikrotikService->testConnection()) {
                throw new \Exception("Cannot establish connection to MikroTik router");
            }
            
            // Get active connections with bandwidth usage
            $activeConnections = $mikrotikService->getActiveConnectionsWithBandwidth();
            
            // Calculate total and average bandwidth usage
            $totalRxRate = 0;
            $totalTxRate = 0;
            $totalRxBytes = 0;
            $totalTxBytes = 0;
            $userCount = count($activeConnections);
            
            foreach ($activeConnections as $connection) {
                $totalRxRate += $connection['rx_rate_raw'];
                $totalTxRate += $connection['tx_rate_raw'];
                $totalRxBytes += isset($connection['bytes_in']) ? $connection['bytes_in'] : 0;
                $totalTxBytes += isset($connection['bytes_out']) ? $connection['bytes_out'] : 0;
            }
            
            // Extract data for the chart
            $rxData = [];
            $txData = [];
            $labels = [];
            
            foreach ($activeConnections as $connection) {
                $rxData[] = $connection['rx_rate_raw'];
                $txData[] = $connection['tx_rate_raw'];
                $labels[] = $connection['username'];
            }
            
            // Create response data
            $responseData = [
                'success' => true,
                'activeUsers' => $userCount,
                'totalRx' => $this->formatBandwidth($totalRxRate),
                'totalTx' => $this->formatBandwidth($totalTxRate),
                'totalRxBytes' => $totalRxBytes > 0 ? $mikrotikService->formatBytesTransferred($totalRxBytes) : null,
                'totalTxBytes' => $totalTxBytes > 0 ? $mikrotikService->formatBytesTransferred($totalTxBytes) : null,
                'averageRx' => $userCount > 0 ? $this->formatBandwidth($totalRxRate / $userCount) : '0 bps',
                'averageTx' => $userCount > 0 ? $this->formatBandwidth($totalTxRate / $userCount) : '0 bps',
                'connections' => $activeConnections,
                'rxData' => $rxData,
                'txData' => $txData,
                'labels' => $labels,
                'timestamp' => now()->timestamp,
                'source' => 'live'
            ];
            
            // Cache the response for 5 seconds
            Cache::put('bandwidth_data', $responseData, now()->addSeconds(5));
            
            return response()->json($responseData);
            
        } catch (\Exception $e) {
            \Log::error('Failed to get bandwidth data: ' . $e->getMessage());
            
            // Check if we have a cached version we can fall back to
            if (Cache::has('bandwidth_data')) {
                $cachedData = Cache::get('bandwidth_data');
                $cachedData['success'] = true;
                $cachedData['fromCache'] = true;
                $cachedData['cacheReason'] = 'Error connecting to router: ' . $e->getMessage();
                
                return response()->json($cachedData);
            }
            
            // If no cache is available, return an error response
            return response()->json([
                'success' => false,
                'message' => 'Erreur de connexion au routeur MikroTik',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}