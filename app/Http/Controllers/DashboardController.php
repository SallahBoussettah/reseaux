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

    public function statistics(Request $request)
    {
        // Initialize variables
        $activeConnections = [];
        $averageRxRate = '0 B/s';
        $averageTxRate = '0 B/s';
        $totalRxRateFormatted = '0 B/s';
        $totalTxRateFormatted = '0 B/s';

        // Calculate database totals
        $totalDownloadedBytes = Client::sum('total_downloaded_bytes');
        $totalUploadedBytes = Client::sum('total_uploaded_bytes');
        $totalBandwidthUsage = $totalDownloadedBytes + $totalUploadedBytes;
        
        // Log the database totals for debugging
        \Log::info("Database Totals - Downloaded: $totalDownloadedBytes bytes, Uploaded: $totalUploadedBytes bytes, Total: $totalBandwidthUsage bytes");
        
        // Format database totals for display
        $formattedDatabaseTotals = [
            'downloaded' => $this->formatBytes($totalDownloadedBytes),
            'uploaded' => $this->formatBytes($totalUploadedBytes),
            'total' => $this->formatBytes($totalBandwidthUsage)
        ];

        // Cache statistics for 1 hour
        $statistics = Cache::remember('statistics', 3600, function () {
            $now = now();
            $startOfMonth = $now->copy()->startOfMonth();
            $endOfMonth = $now->copy()->endOfMonth();

            $dailyActiveUsers = Client::where('last_login_at', '>=', now()->subDay())
                ->count();

            $monthlyActiveUsers = Client::where('last_login_at', '>=', $startOfMonth)
                ->where('last_login_at', '<=', $endOfMonth)
                ->count();

            return collect([
                [
                    'daily_active_users' => $dailyActiveUsers,
                    'monthly_active_users' => $monthlyActiveUsers,
                ]
            ]);
        });

        // Cache user activity by hour for 1 day
        $userActivityByHour = Cache::remember('user_activity_by_hour', 86400, function () {
            $now = now();
            $startOfDay = $now->copy()->startOfDay();
            $endOfDay = $now->copy()->endOfDay();

            $userActivityByHour = Client::where('last_login_at', '>=', $startOfDay)
                ->where('last_login_at', '<=', $endOfDay)
                ->get()
                ->groupBy(function ($client) {
                    return Carbon::parse($client->last_login_at)->format('H');
                })
                ->map(function ($clients) {
                    return $clients->count();
                });

            // Fill in missing hours with 0
            $hours = range(0, 23);
            $filledUserActivityByHour = collect($hours)->mapWithKeys(function ($hour) use ($userActivityByHour) {
                $formattedHour = str_pad($hour, 2, '0', STR_PAD_LEFT);
                return [$formattedHour => $userActivityByHour->get($formattedHour, 0)];
            });

            return $filledUserActivityByHour;
        });

        // Get top users with bandwidth usage
        $topUsers = Client::orderBy('total_downloaded_bytes', 'desc')
            ->take(10)
            ->get()
            ->map(function ($client) {
                return [
                    'name' => $client->name ?? $client->full_name ?? 'Unknown',
                    'downloaded' => $this->formatBytes($client->total_downloaded_bytes),
                    'uploaded' => $this->formatBytes($client->total_uploaded_bytes),
                ];
            });

        // Create bandwidth usage per user data for chart
        $bandwidthUsagePerUser = Client::where('total_downloaded_bytes', '>', 0)
            ->orWhere('total_uploaded_bytes', '>', 0)
            ->orderBy('total_downloaded_bytes', 'desc')
            ->take(10)
            ->get()
            ->map(function ($client) {
                return [
                    'full_name' => $client->full_name ?? $client->name ?? 'Unknown',
                    'total_downloaded_bytes' => $client->total_downloaded_bytes ?? 0,
                    'total_uploaded_bytes' => $client->total_uploaded_bytes ?? 0,
                    'downloaded_formatted' => $this->formatBytes($client->total_downloaded_bytes ?? 0),
                    'uploaded_formatted' => $this->formatBytes($client->total_uploaded_bytes ?? 0),
                ];
            });

        // Get MikroTik data if available
        try {
            // Check if we have cached MikroTik data
            if (Cache::has('bandwidth_data')) {
                $bandwidthData = Cache::get('bandwidth_data');
                
                if (isset($bandwidthData['active_connections'])) {
                    $activeConnections = $bandwidthData['active_connections'];
                }
                
                if (isset($bandwidthData['average_rx_rate'])) {
                    $averageRxRate = $bandwidthData['average_rx_rate'];
                }
                
                if (isset($bandwidthData['average_tx_rate'])) {
                    $averageTxRate = $bandwidthData['average_tx_rate'];
                }
                
                if (isset($bandwidthData['total_rx_rate_formatted'])) {
                    $totalRxRateFormatted = $bandwidthData['total_rx_rate_formatted'];
                }
                
                if (isset($bandwidthData['total_tx_rate_formatted'])) {
                    $totalTxRateFormatted = $bandwidthData['total_tx_rate_formatted'];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error retrieving bandwidth data: ' . $e->getMessage());
        }

        return view('dashboard.statistics', [
            'statistics' => $statistics,
            'userActivityByHour' => $userActivityByHour,
            'activeConnections' => $activeConnections,
            'averageRxRate' => $averageRxRate,
            'averageTxRate' => $averageTxRate,
            'totalRxRateFormatted' => $totalRxRateFormatted,
            'totalTxRateFormatted' => $totalTxRateFormatted,
            'topUsers' => $topUsers,
            'bandwidthUsagePerUser' => $bandwidthUsagePerUser,
            'databaseTotals' => [
                'downloaded' => $totalDownloadedBytes,
                'uploaded' => $totalUploadedBytes,
                'total' => $totalBandwidthUsage
            ],
            'formattedDatabaseTotals' => $formattedDatabaseTotals
        ]);
    }
    
    // Helper function to format bandwidth (for real-time rates)
    private function formatBandwidth($bytes)
    {
        $bytes = (int)$bytes;
        
        if ($bytes === 0) {
            return '0 bps';
        } else if ($bytes > 1000000) {
            return round($bytes / 1000000, 2) . ' Mbps';
        } elseif ($bytes > 1000) {
            return round($bytes / 1000, 2) . ' Kbps';
        } else {
            return $bytes . ' bps';
        }
    }
    
    // Helper function to format bytes (for cumulative data)
    private function formatBytes($bytes)
    {
        $bytes = (int)$bytes;
        
        if ($bytes > 1099511627776) { // TB
            return round($bytes / 1099511627776, 2) . ' TB';
        } elseif ($bytes > 1073741824) { // GB
            return round($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes > 1048576) { // MB
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes > 1024) { // KB
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' B';
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
            
            // Check if we should use mock data for local development
            if (env('APP_ENV') === 'local' && request()->has('use_mock_data')) {
                return response()->json($this->getMockBandwidthData());
            }
            
            // Debug information about the connection
            $host = env('MIKROTIK_HOST', 'eurekadigital.ddns.net');
            $port = (int)env('MIKROTIK_PORT', 8728);
            $user = env('MIKROTIK_USER', 'api');
            
            // Log connection attempt
            \Log::info("Attempting to connect to MikroTik at {$host}:{$port} with user {$user}");
            
            // Connect to MikroTik with appropriate settings from the environment
            $mikrotikClient = new RouterOSAPI([
                'host' => $host,
                'user' => $user,
                'pass' => env('MIKROTIK_PASS', 'Erekapp314'),
                'port' => $port,
                'timeout' => 5, // Reduced timeout to avoid long waits
            ]);
            
            // Create MikroTik service instance
            $mikrotikService = new \App\Services\MikroTikService($mikrotikClient);
            
            // Test connection to ensure it's working
            if (!$mikrotikService->testConnection()) {
                // If in local development, use mock data instead of failing
                if (env('APP_ENV') === 'local') {
                    \Log::info("Using mock data for local development since MikroTik connection failed");
                    return response()->json($this->getMockBandwidthData());
                }
                throw new \Exception("Cannot establish connection to MikroTik router at {$host}:{$port}");
            }
            
            \Log::info("Successfully connected to MikroTik router");
            
            // Get active connections with bandwidth usage
            $activeConnections = $mikrotikService->getActiveConnectionsWithBandwidth();
            
            // Log the number of active connections found
            \Log::info("Retrieved " . count($activeConnections) . " active connections from MikroTik");
            
            // If debugging is enabled, log the first connection details
            if (env('APP_DEBUG') && count($activeConnections) > 0) {
                \Log::debug("First connection details: " . json_encode($activeConnections[0]));
            }
            
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
            
            // Log the calculated totals
            \Log::info("Total bandwidth: RX={$totalRxRate} bps, TX={$totalTxRate} bps");
            
            // Extract data for the chart
            $rxData = [];
            $txData = [];
            $labels = [];
            
            foreach ($activeConnections as $connection) {
                $rxData[] = $connection['rx_rate_raw'];
                $txData[] = $connection['tx_rate_raw'];
                $labels[] = $connection['username'];
            }
            
            // Get database totals for display
            $databaseTotals = $this->getDatabaseTotals();
            
            // Update database with bandwidth usage
            $this->updateBandwidthUsageInDatabase($activeConnections);
            
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
                'source' => 'live',
                'databaseTotals' => $databaseTotals,
                'debug' => [
                    'mikrotik_host' => $host,
                    'mikrotik_port' => $port,
                    'connection_status' => 'success'
                ]
            ];
            
            // Cache the response for 5 seconds
            Cache::put('bandwidth_data', $responseData, now()->addSeconds(5));
            
            return response()->json($responseData);
            
        } catch (\Exception $e) {
            \Log::error('Failed to get bandwidth data: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // If in local development, use mock data instead of failing
            if (env('APP_ENV') === 'local') {
                \Log::info("Using mock data for local development due to exception: " . $e->getMessage());
                return response()->json($this->getMockBandwidthData());
            }
            
            // Check if we have a cached version we can fall back to
            if (Cache::has('bandwidth_data')) {
                $cachedData = Cache::get('bandwidth_data');
                $cachedData['success'] = true;
                $cachedData['fromCache'] = true;
                $cachedData['cacheReason'] = 'Error connecting to router: ' . $e->getMessage();
                
                return response()->json($cachedData);
            }
            
            // If no cache is available, return an error response with detailed information
            return response()->json([
                'success' => false,
                'message' => 'Erreur de connexion au routeur MikroTik',
                'error' => $e->getMessage(),
                'debug' => [
                    'mikrotik_host' => env('MIKROTIK_HOST', 'eurekadigital.ddns.net'),
                    'mikrotik_port' => (int)env('MIKROTIK_PORT', 8728),
                    'connection_status' => 'failed',
                    'error_details' => env('APP_DEBUG') ? $e->getTraceAsString() : null
                ]
            ], 500);
        }
    }
    
    /**
     * Generate mock bandwidth data for local development and testing
     * 
     * @return array
     */
    private function getMockBandwidthData()
    {
        // Generate random number of active users (1-5)
        $userCount = rand(1, 5);
        
        // Create mock active connections
        $activeConnections = [];
        $rxData = [];
        $txData = [];
        $labels = [];
        $totalRxRate = 0;
        $totalTxRate = 0;
        $totalRxBytes = 0;
        $totalTxBytes = 0;
        
        // Mock user data
        $mockUsers = [
            ['name' => 'User1', 'mac' => '00:11:22:33:44:55', 'ip' => '192.168.1.100'],
            ['name' => 'User2', 'mac' => '00:11:22:33:44:56', 'ip' => '192.168.1.101'],
            ['name' => 'User3', 'mac' => '00:11:22:33:44:57', 'ip' => '192.168.1.102'],
            ['name' => 'User4', 'mac' => '00:11:22:33:44:58', 'ip' => '192.168.1.103'],
            ['name' => 'User5', 'mac' => '00:11:22:33:44:59', 'ip' => '192.168.1.104'],
        ];
        
        for ($i = 0; $i < $userCount; $i++) {
            // Generate random bandwidth values (in bps)
            $rxRateRaw = rand(500000, 5000000); // 500 Kbps to 5 Mbps
            $txRateRaw = rand(100000, 1000000); // 100 Kbps to 1 Mbps
            
            // Generate cumulative bytes
            $bytesIn = rand(10000000, 100000000); // 10 MB to 100 MB
            $bytesOut = rand(1000000, 10000000);  // 1 MB to 10 MB
            
            // Add to totals
            $totalRxRate += $rxRateRaw;
            $totalTxRate += $txRateRaw;
            $totalRxBytes += $bytesIn;
            $totalTxBytes += $bytesOut;
            
            // Format values
            $rxRate = $this->formatBandwidth($rxRateRaw);
            $txRate = $this->formatBandwidth($txRateRaw);
            $bytesInFormatted = $this->formatBytes($bytesIn);
            $bytesOutFormatted = $this->formatBytes($bytesOut);
            
            // Add to arrays for charts
            $rxData[] = $rxRateRaw;
            $txData[] = $txRateRaw;
            $labels[] = $mockUsers[$i]['name'];
            
            // Create connection entry
            $activeConnections[] = [
                'username' => $mockUsers[$i]['name'],
                'mac_address' => $mockUsers[$i]['mac'],
                'ip_address' => $mockUsers[$i]['ip'],
                'rx_rate' => $rxRate,
                'tx_rate' => $txRate,
                'rx_rate_raw' => $rxRateRaw,
                'tx_rate_raw' => $txRateRaw,
                'bytes_in' => $bytesIn,
                'bytes_out' => $bytesOut,
                'bytes_in_formatted' => $bytesInFormatted,
                'bytes_out_formatted' => $bytesOutFormatted,
                'uptime' => rand(1, 12) . ':' . rand(10, 59) . ':' . rand(10, 59),
                'login_time' => 'cookie',
                'session_id' => 'mock-session-' . $i
            ];
        }
        
        // Get database totals for display
        $databaseTotals = $this->getDatabaseTotals();
        
        // Create response data
        return [
            'success' => true,
            'activeUsers' => $userCount,
            'totalRx' => $this->formatBandwidth($totalRxRate),
            'totalTx' => $this->formatBandwidth($totalTxRate),
            'totalRxBytes' => $this->formatBytes($totalRxBytes),
            'totalTxBytes' => $this->formatBytes($totalTxBytes),
            'averageRx' => $userCount > 0 ? $this->formatBandwidth($totalRxRate / $userCount) : '0 bps',
            'averageTx' => $userCount > 0 ? $this->formatBandwidth($totalTxRate / $userCount) : '0 bps',
            'connections' => $activeConnections,
            'rxData' => $rxData,
            'txData' => $txData,
            'labels' => $labels,
            'timestamp' => now()->timestamp,
            'source' => 'mock',
            'databaseTotals' => $databaseTotals,
            'debug' => [
                'connection_status' => 'mock_data',
                'note' => 'Using mock data for local development'
            ]
        ];
    }
    
    /**
     * Get formatted database totals for bandwidth usage
     * 
     * @return array
     */
    private function getDatabaseTotals()
    {
        $totalDownloaded = \App\Models\Client::sum('total_downloaded_bytes');
        $totalUploaded = \App\Models\Client::sum('total_uploaded_bytes');
        $totalBandwidth = $totalDownloaded + $totalUploaded;
        
        return [
            'downloaded' => $this->formatBytes($totalDownloaded),
            'uploaded' => $this->formatBytes($totalUploaded),
            'total' => $this->formatBytes($totalBandwidth)
        ];
    }

    /**
     * Update client bandwidth usage in the database
     * 
     * @param array $activeConnections
     * @return void
     */
    private function updateBandwidthUsageInDatabase($activeConnections)
    {
        try {
            foreach ($activeConnections as $connection) {
                // Skip if no MAC address or bytes data
                if (!isset($connection['mac_address']) || 
                    !isset($connection['bytes_in']) || 
                    !isset($connection['bytes_out'])) {
                    continue;
                }
                
                $macAddress = $connection['mac_address'];
                $bytesIn = (int)$connection['bytes_in'];
                $bytesOut = (int)$connection['bytes_out'];
                
                // Find client by MAC address
                $client = Client::where('mac_address', $macAddress)->first();
                
                if ($client) {
                    // Store previous values for logging
                    $previousDownloaded = $client->total_downloaded_bytes;
                    $previousUploaded = $client->total_uploaded_bytes;
                    
                    // Update client's bandwidth usage
                    // We're using the bytes from the active session
                    // We'll update the database only if the new values are higher than existing ones
                    if ($bytesIn > 0 && $bytesIn > $client->total_downloaded_bytes) {
                        $client->total_downloaded_bytes = $bytesIn;
                    }
                    
                    if ($bytesOut > 0 && $bytesOut > $client->total_uploaded_bytes) {
                        $client->total_uploaded_bytes = $bytesOut;
                    }
                    
                    // Save changes if there are any
                    if ($client->isDirty()) {
                        $client->save();
                        
                        \Log::info("Updated bandwidth usage for client {$client->id} ({$client->mac_address}): " . 
                                  "Downloaded: {$previousDownloaded} -> {$client->total_downloaded_bytes}, " .
                                  "Uploaded: {$previousUploaded} -> {$client->total_uploaded_bytes}");
                    }
                } else {
                    \Log::warning("Client with MAC address {$macAddress} not found in database");
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error updating bandwidth usage in database: ' . $e->getMessage());
        }
    }

    /**
     * Update bandwidth usage from MikroTik API
     * This can be called via a scheduled task or manually
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateBandwidthUsage()
    {
        try {
            // Connect to MikroTik with appropriate settings from the environment
            $mikrotikClient = new RouterOSAPI([
                'host' => env('MIKROTIK_HOST', 'eurekadigital.ddns.net'),
                'user' => env('MIKROTIK_USER', 'api'),
                'pass' => env('MIKROTIK_PASS', 'Erekapp314'),
                'port' => (int)env('MIKROTIK_PORT', 8728),
                'timeout' => 5,
            ]);
            
            // Create MikroTik service instance
            $mikrotikService = new \App\Services\MikroTikService($mikrotikClient);
            
            // Test connection to ensure it's working
            if (!$mikrotikService->testConnection()) {
                throw new \Exception("Cannot establish connection to MikroTik router");
            }
            
            // Get active connections with bandwidth usage
            $activeConnections = $mikrotikService->getActiveConnectionsWithBandwidth();
            
            // Update database with bandwidth usage
            $this->updateBandwidthUsageInDatabase($activeConnections);
            
            // Get historical data from MikroTik's accounting
            $accountingData = $mikrotikService->getAccountingData();
            $this->updateHistoricalBandwidthUsage($accountingData);
            
            return response()->json([
                'success' => true,
                'message' => 'Bandwidth usage updated successfully',
                'active_connections' => count($activeConnections),
                'accounting_records' => count($accountingData ?? [])
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to update bandwidth usage: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update bandwidth usage',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update historical bandwidth usage from MikroTik accounting data
     * 
     * @param array $accountingData
     * @return void
     */
    private function updateHistoricalBandwidthUsage($accountingData)
    {
        if (empty($accountingData)) {
            return;
        }
        
        try {
            foreach ($accountingData as $record) {
                // Skip if no MAC address or bytes data
                if (!isset($record['mac-address']) || 
                    !isset($record['bytes-in']) || 
                    !isset($record['bytes-out'])) {
                    continue;
                }
                
                $macAddress = $record['mac-address'];
                $bytesIn = (int)$record['bytes-in'];
                $bytesOut = (int)$record['bytes-out'];
                
                // Find client by MAC address
                $client = Client::where('mac_address', $macAddress)->first();
                
                if ($client) {
                    // Update client's bandwidth usage if the new values are higher
                    if ($bytesIn > 0 && $bytesIn > $client->total_downloaded_bytes) {
                        $client->total_downloaded_bytes = $bytesIn;
                    }
                    
                    if ($bytesOut > 0 && $bytesOut > $client->total_uploaded_bytes) {
                        $client->total_uploaded_bytes = $bytesOut;
                    }
                    
                    // Save changes if there are any
                    if ($client->isDirty()) {
                        $client->save();
                        
                        \Log::info("Updated historical bandwidth usage for client {$client->id} ({$client->mac_address}): " . 
                                  "Downloaded: {$client->total_downloaded_bytes}, " .
                                  "Uploaded: {$client->total_uploaded_bytes}");
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error updating historical bandwidth usage: ' . $e->getMessage());
        }
    }
}