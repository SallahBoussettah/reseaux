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
    protected $description = 'Update bandwidth usage data from MikroTik router';

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
            $mikrotikService = new MikroTikService($mikrotikClient);
            
            // Test connection to ensure it's working
            if (!$mikrotikService->testConnection()) {
                $this->error("Cannot establish connection to MikroTik router");
                Log::error("Cannot establish connection to MikroTik router during scheduled task");
                return Command::FAILURE;
            }
            
            $this->info('Connected to MikroTik router successfully');
            
            // Get active connections with bandwidth usage
            $activeConnections = $mikrotikService->getActiveConnectionsWithBandwidth();
            
            $this->info('Found ' . count($activeConnections) . ' active connections');
            
            // Update database with bandwidth usage
            $this->updateBandwidthUsageInDatabase($activeConnections);
            
            // Get historical data from MikroTik's accounting
            $accountingData = $mikrotikService->getAccountingData();
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
     * Update database with bandwidth usage from active connections
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
                        
                        $this->info("Updated bandwidth usage for client {$client->id} ({$client->mac_address}): " . 
                                  "Downloaded: {$previousDownloaded} -> {$client->total_downloaded_bytes}, " .
                                  "Uploaded: {$previousUploaded} -> {$client->total_uploaded_bytes}");
                        
                        Log::info("Updated bandwidth usage for client {$client->id} ({$client->mac_address}): " . 
                                  "Downloaded: {$previousDownloaded} -> {$client->total_downloaded_bytes}, " .
                                  "Uploaded: {$previousUploaded} -> {$client->total_uploaded_bytes}");
                    }
                } else {
                    $this->warn("Client with MAC address {$macAddress} not found in database");
                    Log::warning("Client with MAC address {$macAddress} not found in database");
                }
            }
        } catch (\Exception $e) {
            $this->error('Error updating bandwidth usage in database: ' . $e->getMessage());
            Log::error('Error updating bandwidth usage in database: ' . $e->getMessage());
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
            $this->info('No accounting data available');
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
                        
                        $this->info("Updated historical bandwidth usage for client {$client->id} ({$client->mac_address}): " . 
                                  "Downloaded: {$client->total_downloaded_bytes}, " .
                                  "Uploaded: {$client->total_uploaded_bytes}");
                        
                        Log::info("Updated historical bandwidth usage for client {$client->id} ({$client->mac_address}): " . 
                                  "Downloaded: {$client->total_downloaded_bytes}, " .
                                  "Uploaded: {$client->total_uploaded_bytes}");
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error('Error updating historical bandwidth usage: ' . $e->getMessage());
            Log::error('Error updating historical bandwidth usage: ' . $e->getMessage());
        }
    }
}
