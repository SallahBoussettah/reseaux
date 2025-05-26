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

        $clients = Client::select('id', 'full_name', 'email', 'mac_address', 'device_type', 'platform', 'login_count', 'premium_expires_at','status')
        ->orderBy('created_at', 'desc')
        ->paginate(10);  // Paginate if you have many clients


        return view('dashboard.clients', compact('clients'));
    }

    public function statistics()
    {

        $statistics = Client::select(
            // Daily active users (last 30 days)
            DB::raw('COUNT(DISTINCT email) as daily_active_users'),
            
            // Monthly active users (current month)
            DB::raw('SUM(CASE WHEN MONTH(last_login_at) = MONTH(CURDATE()) THEN 1 ELSE 0 END) as monthly_active_users'),

            // Total bandwidth usage per user (sum of data_usage)
            DB::raw('SUM(data_usage) as total_bandwidth_usage'),

            // Retention rate (users with more than one login)
            DB::raw('SUM(CASE WHEN login_count > 1 THEN 1 ELSE 0 END) as returning_users'),

            // Group by the hour of the day to check user activity by time of day
            DB::raw('HOUR(last_login_at) as hour_of_day, COUNT(*) as users_by_hour')
        )
        ->whereBetween('last_login_at', [now()->subDays(30), now()]) // Restrict to the last 30 days
        ->groupBy(DB::raw('HOUR(last_login_at)')) // Group by hour for user activity
        ->get();

        $bandwidthUsagePerUser = Client::select(
            'full_name',
            'data_usage',
            'email'
        )
        ->orderByDesc('data_usage') // Get users with the highest data usage first
        ->take(10) // Fetch top 10 users by data usage
        ->get();

        return view('dashboard.statistics', compact('statistics','bandwidthUsagePerUser'));
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

}