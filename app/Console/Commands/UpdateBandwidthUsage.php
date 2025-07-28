<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use \RouterOS\Client as RouterOSAPI;
use App\Services\MikroTikService;
use Illuminate\Support\Facades\Log;

class UpdateBandwidthUsage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bandwidth:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update bandwidth usage data from MikroTik router using direct interface traffic monitoring';

    /**
     * MikroTik service instance
     *
     * @var MikroTikService
     */
    protected $mikrotikService;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting bandwidth usage update...');
        
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
            $this->mikrotikService = new MikroTikService($mikrotikClient);
            
            // Test connection to ensure it's working
            if (!$this->mikrotikService->testConnection()) {
                $this->error("Cannot establish connection to MikroTik router");
                Log::error("Cannot establish connection to MikroTik router during scheduled task");
                return Command::FAILURE;
            }
            
            $this->info('Connected to MikroTik router successfully');
            
            // Get active connections with bandwidth usage
            $activeConnections = $this->mikrotikService->getActiveConnectionsWithBandwidth();
            
            $this->info('Found ' . count($activeConnections) . ' active connections');
            
            // Update database with bandwidth usage
            $this->updateBandwidthUsageInDatabase($activeConnections);
            
            // Get historical data from MikroTik's accounting (optional, as we're now using direct traffic)
            $accountingData = $this->mikrotikService->getAccountingData();
            $this->updateHistoricalBandwidthUsage($accountingData);
            
            $this->info('Bandwidth usage updated successfully');
            $this->info('Active connections: ' . count($activeConnections));
            $this->info('Accounting records: ' . count($accountingData ?? []));
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Failed to update bandwidth usage: ' . $e->getMessage());
            Log::error('Failed to update bandwidth usage during scheduled task: ' . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
    
    /**
     * Update database with bandwidth usage using interface traffic allocation
     * 
     * @param array $activeConnections
     * @return void
     */
    private function updateBandwidthUsageInDatabase($activeConnections)
    {
        try {
            if (empty($activeConnections)) {
                $this->info('No active connections to process');
                return;
            }

            // Get interface traffic data for accurate total bandwidth
            $interfaceTraffic = $this->mikrotikService->getInterfaceTraffic();
            
            if (empty($interfaceTraffic) || !isset($interfaceTraffic['rx-bits-per-second'])) {
                $this->warn('No interface traffic data available, skipping bandwidth allocation');
                return;
            }

            $totalInterfaceRx = $interfaceTraffic['rx-bits-per-second']; // Download from internet
            $totalInterfaceTx = $interfaceTraffic['tx-bits-per-second']; // Upload to internet
            
            $this->info("Interface traffic: Download={$totalInterfaceRx} bps, Upload={$totalInterfaceTx} bps");

            // Calculate total activity rates from all users (for proportional allocation)
            $totalUserRxRate = 0;
            $totalUserTxRate = 0;
            $activeUserCount = 0;

            foreach ($activeConnections as $connection) {
                if (isset($connection['rx_rate_raw']) && isset($connection['tx_rate_raw'])) {
                    $totalUserRxRate += (int)$connection['rx_rate_raw'];
                    $totalUserTxRate += (int)$connection['tx_rate_raw'];
                    $activeUserCount++;
                }
            }

            $this->info("Total user activity: RX={$totalUserRxRate} bps, TX={$totalUserTxRate} bps from {$activeUserCount} users");

            // Calculate bytes transferred in the last minute (since this runs every minute)
            // Interface traffic is in bits per second, so multiply by 60 seconds then divide by 8 for bytes
            $intervalSeconds = 60; // Command runs every minute
            $totalInterfaceRxBytes = ($totalInterfaceRx * $intervalSeconds) / 8;
            $totalInterfaceTxBytes = ($totalInterfaceTx * $intervalSeconds) / 8;
            
            $this->info("Calculated bytes for last {$intervalSeconds} seconds: Download={$totalInterfaceRxBytes} bytes, Upload={$totalInterfaceTxBytes} bytes");

            // If no user activity detected, distribute equally among active users
            if ($totalUserRxRate == 0 && $totalUserTxRate == 0 && $activeUserCount > 0) {
                $this->info('No individual user activity detected, distributing interface traffic equally');
                $perUserRxBytes = $totalInterfaceRxBytes / $activeUserCount;
                $perUserTxBytes = $totalInterfaceTxBytes / $activeUserCount;
            }

            // Process each active connection
            foreach ($activeConnections as $connection) {
                if (!isset($connection['mac_address'])) {
                    continue;
                }
                
                $macAddress = $connection['mac_address'];
                $userRxRate = isset($connection['rx_rate_raw']) ? (int)$connection['rx_rate_raw'] : 0;
                $userTxRate = isset($connection['tx_rate_raw']) ? (int)$connection['tx_rate_raw'] : 0;
                
                // Find client by MAC address
                $client = Client::where('mac_address', $macAddress)->first();
                
                if (!$client) {
                    $this->warn("Client with MAC address {$macAddress} not found in database");
                    continue;
                }

                // Calculate this user's proportional share of interface traffic
                $allocatedRxBytes = 0;
                $allocatedTxBytes = 0;

                if ($totalUserRxRate > 0 && $totalUserTxRate > 0) {
                    // Proportional allocation based on user activity
                    $rxProportion = $userRxRate / $totalUserRxRate;
                    $txProportion = $userTxRate / $totalUserTxRate;
                    
                    $allocatedRxBytes = $totalInterfaceRxBytes * $rxProportion;
                    $allocatedTxBytes = $totalInterfaceTxBytes * $txProportion;
                } else if (isset($perUserRxBytes) && isset($perUserTxBytes)) {
                    // Equal distribution
                    $allocatedRxBytes = $perUserRxBytes;
                    $allocatedTxBytes = $perUserTxBytes;
                }

                // Only update if we have meaningful allocation
                if ($allocatedRxBytes > 0 || $allocatedTxBytes > 0) {
                    $previousDownloaded = $client->total_downloaded_bytes;
                    $previousUploaded = $client->total_uploaded_bytes;
                    
                    // Add the allocated bytes to the user's total (cumulative)
                    $client->total_downloaded_bytes += (int)$allocatedRxBytes;
                    $client->total_uploaded_bytes += (int)$allocatedTxBytes;
                    
                    // Save changes
                    $client->save();
                    
                    $this->info("Updated bandwidth for {$macAddress}: " . 
                              "Downloaded: +{$allocatedRxBytes} bytes (total: {$client->total_downloaded_bytes}), " .
                              "Uploaded: +{$allocatedTxBytes} bytes (total: {$client->total_uploaded_bytes})");
                    
                    Log::info("Allocated bandwidth to client {$client->id} ({$macAddress}): " . 
                              "RX: +{$allocatedRxBytes} bytes, TX: +{$allocatedTxBytes} bytes");
                }
            }
        } catch (\Exception $e) {
            $this->error('Error updating bandwidth usage in database: ' . $e->getMessage());
            Log::error('Error updating bandwidth usage in database: ' . $e->getMessage());
        }
    }
    
    /**
     * Update historical bandwidth usage from MikroTik accounting data
     * Note: This method is kept for compatibility but accounting data may also be unreliable
     * The main bandwidth tracking now uses direct interface traffic allocation
     * 
     * @param array $accountingData
     * @return void
     */
    private function updateHistoricalBandwidthUsage($accountingData)
    {
        if (empty($accountingData)) {
            $this->info('No accounting data available - relying on interface traffic allocation');
            return;
        }
        
        $this->info('Processing ' . count($accountingData) . ' accounting records (supplementary data)');
        
        try {
            foreach ($accountingData as $record) {
                // Skip if no MAC address - we don't use bytes data from accounting anymore
                if (!isset($record['mac-address'])) {
                    continue;
                }
                
                $macAddress = $record['mac-address'];
                
                // Find client by MAC address
                $client = Client::where('mac_address', $macAddress)->first();
                
                if ($client) {
                    // Just log that we found the user in accounting data
                    // The actual bandwidth allocation is done via interface traffic
                    $this->info("Found client {$macAddress} in accounting data - bandwidth tracked via interface allocation");
                    Log::debug("Client {$macAddress} found in accounting data");
                } else {
                    $this->warn("Client with MAC address {$macAddress} found in accounting but not in database");
                }
            }
        } catch (\Exception $e) {
            $this->error('Error processing historical bandwidth data: ' . $e->getMessage());
            Log::error('Error processing historical bandwidth data: ' . $e->getMessage());
        }
    }
}
