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
        // Check if we need to clear the statistics cache
        $clearStatsFile = storage_path('framework/cache/data/clear_stats');
        if (file_exists($clearStatsFile)) {
            Cache::forget('statistics');
            unlink($clearStatsFile); // Remove the file after clearing cache
            \Log::info('Statistics cache cleared via clear_stats file');
        }

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

            // Get current daily and monthly active users
            $dailyActiveUsers = Client::where('last_login_at', '>=', now()->subDay())
                ->count();

            $monthlyActiveUsers = Client::where('last_login_at', '>=', $startOfMonth)
                ->where('last_login_at', '<=', $endOfMonth)
                ->count();

            // Generate data for daily active users for the past 30 days
            $dailyActiveUsersHistory = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $startOfDay = $date->copy()->startOfDay();
                $endOfDay = $date->copy()->endOfDay();
                
                // Get count of users who logged in on that specific day OR were active
                $userCount = Client::where(function($query) use ($startOfDay, $endOfDay) {
                    $query->where('last_login_at', '>=', $startOfDay)
                          ->where('last_login_at', '<=', $endOfDay);
                })
                ->orWhere(function($query) use ($startOfDay, $endOfDay) {
                    $query->where('created_at', '>=', $startOfDay)
                          ->where('created_at', '<=', $endOfDay);
                })
                ->count();
                
                // If today and we have active connections, use that count instead if it's higher
                if ($i === 0 && isset($activeConnections) && count($activeConnections) > $userCount) {
                    $userCount = count($activeConnections);
                }
                
                $dailyActiveUsersHistory[] = $userCount;
            }

            return collect([
                [
                    'daily_active_users' => $dailyActiveUsers,
                    'monthly_active_users' => $monthlyActiveUsers,
                    'daily_active_users_history' => $dailyActiveUsersHistory,
                    'users_by_hour' => $this->getUserActivityByHour()
                ]
            ]);
        });

        // Get database totals for bandwidth usage
        $databaseTotals = $this->getDatabaseTotals();
        $formattedDatabaseTotals = $this->getFormattedDatabaseTotals($databaseTotals);

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
            'activeConnections' => $activeConnections,
            'averageRxRate' => $averageRxRate,
            'averageTxRate' => $averageTxRate,
            'totalRxRateFormatted' => $totalRxRateFormatted,
            'totalTxRateFormatted' => $totalTxRateFormatted,
            'topUsers' => $topUsers,
            'bandwidthUsagePerUser' => $bandwidthUsagePerUser,
            'databaseTotals' => $databaseTotals,
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
     * Get bandwidth data for the dashboard via AJAX
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBandwidthData()
    {
        try {
            // Only check for cached data if not forcing a refresh
            if (Cache::has('bandwidth_data') && !request()->has('force_refresh')) {
                $cachedData = Cache::get('bandwidth_data');
                $cachedData['from_cache'] = true;
                $cachedData['cache_time'] = date('H:i:s', Cache::get('bandwidth_data_timestamp', time()));
                return response()->json($cachedData);
            }
            
            // Debug information about the connection
            $host = env('MIKROTIK_HOST', 'eurekadigital.ddns.net');
            $port = (int)env('MIKROTIK_PORT', 8728);
            $user = env('MIKROTIK_USER', 'api');
            $pass = env('MIKROTIK_PASS', 'Erekapp314');
            
            // Enhanced logging for connection debugging
            \Log::info("Attempting to connect to MikroTik at {$host}:{$port} with user {$user}");
            \Log::info("Connection parameters: Host={$host}, Port={$port}, User={$user}, Pass=****");
            
            // Connect to MikroTik with approptriate settings from the environment
            $mikrotikClient = new RouterOSAPI([
                'host' => $host,
                'user' => $user,
                'pass' => $pass,
                'port' => $port,
                'timeout' => 20, // Increased timeout for better reliability
            ]);
            
            // Create MikroTik service instance
            $mikrotikService = new \App\Services\MikroTikService($mikrotikClient);
            
            // Test connection to ensure it's working
            if (!$mikrotikService->testConnection()) {
                \Log::error("Connection test failed for MikroTik router at {$host}:{$port}");
                throw new \Exception("Cannot establish connection to MikroTik router at {$host}:{$port}. Please check your connection settings.");
            }
            
            \Log::info("Successfully connected to MikroTik router");
            
            // Get interface traffic data first (more reliable for total bandwidth)
            $interfaceTraffic = $mikrotikService->getInterfaceTraffic();
            
            // Get active connections with bandwidth usage
            $activeConnections = $mikrotikService->getActiveConnectionsWithBandwidth();
            
            // Get user data from database to map MAC addresses to user names
            $macAddresses = collect($activeConnections)->pluck('mac_address')->filter()->toArray();
            $userDataMap = [];
            
            if (!empty($macAddresses)) {
                // Fetch clients with these MAC addresses
                $clients = \App\Models\Client::whereIn('mac_address', $macAddresses)->get();
                
                // Create a map of MAC address to user data
                foreach ($clients as $client) {
                    $userDataMap[$client->mac_address] = [
                        'id' => $client->id,
                        'full_name' => $client->full_name,
                        'email' => $client->email,
                        'status' => $client->status
                    ];
                }
                
                \Log::info("Found " . count($userDataMap) . " users in database matching active connections");
            }
            
            // Count active users
            $userCount = count($activeConnections);
            \Log::info("Found {$userCount} active connections on MikroTik router");
            
            // Calculate total bandwidth usage
            $totalRxRate = 0;
            $totalTxRate = 0;
            $totalRxBytes = 0;
            $totalTxBytes = 0;
            
            // If we have interface traffic data, use that for total bandwidth (more reliable)
            if (isset($interfaceTraffic['rx-bits-per-second']) && isset($interfaceTraffic['tx-bits-per-second'])) {
                $totalRxRate = $interfaceTraffic['rx-bits-per-second'];
                $totalTxRate = $interfaceTraffic['tx-bits-per-second'];
                
                \Log::info("Using interface traffic data: RX={$totalRxRate} bps, TX={$totalTxRate} bps");
            } else {
                // Otherwise sum up the individual connection rates
                foreach ($activeConnections as $connection) {
                    if (isset($connection['rx_rate_raw'])) {
                        $totalRxRate += $connection['rx_rate_raw'];
                    }
                    
                    if (isset($connection['tx_rate_raw'])) {
                        $totalTxRate += $connection['tx_rate_raw'];
                    }
                    
                    if (isset($connection['bytes_in'])) {
                        $totalRxBytes += $connection['bytes_in'];
                    }
                    
                    if (isset($connection['bytes_out'])) {
                        $totalTxBytes += $connection['bytes_out'];
                    }
                }
                
                \Log::info("Calculated total bandwidth from connections: RX={$totalRxRate} bps, TX={$totalTxRate} bps");
            }
            
            // Log total bandwidth usage
            \Log::info("Total bandwidth: RX={$totalRxRate} bps, TX={$totalTxRate} bps");
            \Log::info("Total bytes: RX={$totalRxBytes} bytes, TX={$totalTxBytes} bytes");
            
            // Extract data for the chart
            $rxData = [];
            $txData = [];
            $labels = [];
            
            foreach ($activeConnections as $connection) {
                if (isset($connection['rx_rate_raw'])) {
                    $rxData[] = $connection['rx_rate_raw'];
                } else {
                    $rxData[] = 0;
                }
                
                if (isset($connection['tx_rate_raw'])) {
                    $txData[] = $connection['tx_rate_raw'];
                } else {
                    $txData[] = 0;
                }
                
                // Check if we have a user name for this MAC address
                $macAddress = $connection['mac_address'] ?? null;
                $displayName = $connection['username'] ?? 'Unknown';
                
                if ($macAddress && isset($userDataMap[$macAddress]) && !empty($userDataMap[$macAddress]['full_name'])) {
                    $displayName = $userDataMap[$macAddress]['full_name'];
                } else if ($macAddress && isset($userDataMap[$macAddress]) && !empty($userDataMap[$macAddress]['email'])) {
                    $displayName = $userDataMap[$macAddress]['email'];
                }
                
                $labels[] = $displayName;
            }
            
            // Get database totals for display
            $databaseTotals = $this->getDatabaseTotals();
            
            // Get current daily active users count
            $dailyActiveUsers = Client::where('last_login_at', '>=', now()->subDay())
                ->count();
            
            // Update database with bandwidth usage
            $this->updateBandwidthUsageInDatabase($activeConnections);
            
            // Format bandwidth values for display
            $totalRxRateFormatted = $this->formatBandwidth($totalRxRate);
            $totalTxRateFormatted = $this->formatBandwidth($totalTxRate);
            $averageRxRate = $userCount > 0 ? $this->formatBandwidth($totalRxRate / $userCount) : '0 bps';
            $averageTxRate = $userCount > 0 ? $this->formatBandwidth($totalTxRate / $userCount) : '0 bps';
            
            // Get network capacity - default to 1 Gbps if not configured
            $networkCapacity = env('NETWORK_CAPACITY_MBPS', 1000) * 1000000; // Convert Mbps to bps
            
            // Create response data
            $responseData = [
                'success' => true,
                'activeUsers' => $userCount,
                'dailyActiveUsers' => $dailyActiveUsers,
                'totalRx' => $totalRxRateFormatted,
                'totalTx' => $totalTxRateFormatted,
                'total_rx_rate_formatted' => $totalRxRateFormatted,
                'total_tx_rate_formatted' => $totalTxRateFormatted,
                'totalRxBytes' => $totalRxBytes > 0 ? $mikrotikService->formatBytesTransferred($totalRxBytes) : '0 B',
                'totalTxBytes' => $totalTxBytes > 0 ? $mikrotikService->formatBytesTransferred($totalTxBytes) : '0 B',
                'averageRx' => $averageRxRate,
                'averageTx' => $averageTxRate,
                'average_rx_rate' => $averageRxRate,
                'average_tx_rate' => $averageTxRate,
                'connections' => $activeConnections,
                'active_connections' => $activeConnections,
                'rxData' => $rxData,
                'txData' => $txData,
                'labels' => $labels,
                'timestamp' => now()->timestamp,
                'source' => 'live',
                'databaseTotals' => $databaseTotals,
                'usersByHour' => $this->getUserActivityByHour(),
                'user_data' => $userDataMap,
                'debug' => [
                    'mikrotik_host' => $host,
                    'mikrotik_port' => $port,
                    'connection_status' => 'success',
                    'active_connections_count' => count($activeConnections),
                    'bandwidth_capacity' => $networkCapacity,
                    'bandwidth_raw' => [
                        'rx' => $totalRxRate,
                        'tx' => $totalTxRate
                    ],
                    'interface_traffic' => [
                        'success' => isset($interfaceTraffic['rx-bits-per-second']),
                        'rx' => $interfaceTraffic['rx-bits-per-second'] ?? 0,
                        'tx' => $interfaceTraffic['tx-bits-per-second'] ?? 0,
                        'interface' => $interfaceTraffic['interface'] ?? 'unknown',
                        'type' => $interfaceTraffic['type'] ?? 'unknown'
                    ]
                ]
            ];
            
            // Cache the response for 5 seconds
            Cache::put('bandwidth_data', $responseData, now()->addSeconds(5));
            Cache::put('bandwidth_data_timestamp', time(), now()->addSeconds(5));
            
            return response()->json($responseData);
            
        } catch (\Exception $e) {
            \Log::error('Failed to get bandwidth data: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Check if we have a cached version we can fall back to
            if (Cache::has('bandwidth_data')) {
                $cachedData = Cache::get('bandwidth_data');
                $cachedData['success'] = true;
                $cachedData['fromCache'] = true;
                $cachedData['cacheReason'] = 'Error connecting to router: ' . $e->getMessage();
                $cachedData['cache_time'] = date('H:i:s', Cache::get('bandwidth_data_timestamp', time()));
                
                return response()->json($cachedData);
            }
            
            // If no cache is available, return an error response with detailed information
            return response()->json([
                'success' => false,
                'message' => 'Erreur de connexion au routeur MikroTik: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'debug' => [
                    'mikrotik_host' => env('MIKROTIK_HOST', 'eurekadigital.ddns.net'),
                    'mikrotik_port' => (int)env('MIKROTIK_PORT', 8728),
                    'connection_status' => 'failed',
                    'error_time' => date('Y-m-d H:i:s'),
                    'error_details' => env('APP_DEBUG') ? $e->getTraceAsString() : null
                ]
            ], 500);
        }
    }
    
    /**
     * Get formatted database totals for bandwidth usage
     * 
     * @return array
     */
    private function getDatabaseTotals()
    {
        $totalDownloadedBytes = Client::sum('total_downloaded_bytes');
        $totalUploadedBytes = Client::sum('total_uploaded_bytes');
        $totalBandwidthUsage = $totalDownloadedBytes + $totalUploadedBytes;

        return [
            'downloaded' => $totalDownloadedBytes,
            'uploaded' => $totalUploadedBytes,
            'total' => $totalBandwidthUsage
        ];
    }

    /**
     * Reset the statistics cache to ensure fresh data
     * This can be called when users connect or disconnect
     */
    private function resetStatisticsCache()
    {
        try {
            Cache::forget('statistics');
            \Log::info('Statistics cache cleared to ensure fresh data');
            return true;
        } catch (\Exception $e) {
            \Log::error('Error clearing statistics cache: ' . $e->getMessage());
            return false;
        }
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
            $updated = false;
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
                        $updated = true;
                    }
                    
                    if ($bytesOut > 0 && $bytesOut > $client->total_uploaded_bytes) {
                        $client->total_uploaded_bytes = $bytesOut;
                        $updated = true;
                    }
                    
                    // Update last_login_at if not set to track active users
                    if (empty($client->last_login_at)) {
                        $client->last_login_at = now();
                        $updated = true;
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
            
            // If we updated any clients, reset the statistics cache
            if ($updated) {
                $this->resetStatisticsCache();
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

    /**
     * Get user activity by hour for the current day
     */
    private function getUserActivityByHour()
    {
        $now = now();
        $startOfDay = $now->copy()->startOfDay();
        $endOfDay = $now->copy()->endOfDay();
        $currentHour = $now->format('H');

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
        $filledUserActivityByHour = collect($hours)->mapWithKeys(function ($hour) use ($userActivityByHour, $currentHour) {
            $formattedHour = str_pad($hour, 2, '0', STR_PAD_LEFT);
            // Always show at least one user for the current hour (someone is viewing the dashboard)
            if ($formattedHour === $currentHour) {
                return [$formattedHour => max(1, $userActivityByHour->get($formattedHour, 0))];
            }
            return [$formattedHour => $userActivityByHour->get($formattedHour, 0)];
        });

        return $filledUserActivityByHour;
    }

    private function getFormattedDatabaseTotals($databaseTotals)
    {
        return [
            'downloaded' => $this->formatBytes($databaseTotals['downloaded']),
            'uploaded' => $this->formatBytes($databaseTotals['uploaded']),
            'total' => $this->formatBytes($databaseTotals['total'])
        ];
    }
}