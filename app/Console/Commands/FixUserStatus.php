<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FixUserStatus extends Command
{
    protected $signature = 'users:fix-status';
    protected $description = 'Fix users with inconsistent status after previous process failures';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Starting to fix user status...');
        Log::info('Starting to fix user status...');

        // Get all users with active status but who have scheduled_deletion_at in the past
        $usersToFix = Client::where('status', 'active')
                          ->whereNotNull('scheduled_deletion_at')
                          ->where('scheduled_deletion_at', '<', Carbon::now())
                          ->get();

        $this->info('Found ' . $usersToFix->count() . ' users to fix.');
        Log::info('Found ' . $usersToFix->count() . ' users to fix.');

        $fixedCount = 0;
        $errorCount = 0;

        foreach ($usersToFix as $user) {
            $this->info('Fixing user: ' . $user->email . ' (ID: ' . $user->id . ')');
            $this->info('  Current Status: ' . $user->status);
            $this->info('  Current Profile Type: ' . $user->profile_type);
            $this->info('  MAC Address: ' . ($user->mac_address ?? 'None'));
            $this->info('  Scheduled Deletion: ' . $user->scheduled_deletion_at);

            try {
                DB::beginTransaction();
                
                // Update user status and profile type
                $user->status = 'deactivated';
                $user->profile_type = 'expired';
                
                $saved = $user->save();
                
                if ($saved) {
                    DB::commit();
                    $fixedCount++;
                    $this->info('Successfully fixed user: ' . $user->email);
                    Log::info('Successfully fixed user: ' . $user->email, [
                        'user_id' => $user->id,
                        'new_status' => 'deactivated',
                        'new_profile_type' => 'expired'
                    ]);
                } else {
                    throw new \Exception('Failed to save user changes');
                }
            } catch (\Exception $e) {
                DB::rollBack();
                $errorCount++;
                $this->error('Error fixing user ' . $user->email . ': ' . $e->getMessage());
                Log::error('Error fixing user ' . $user->email . ': ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info('');
        $this->info('=== Processing Summary ===');
        $this->info('Total users processed: ' . $usersToFix->count());
        $this->info('Successfully fixed: ' . $fixedCount);
        $this->info('Errors encountered: ' . $errorCount);

        Log::info('User status fix completed', [
            'total_processed' => $usersToFix->count(),
            'fixed' => $fixedCount,
            'errors' => $errorCount
        ]);

        return Command::SUCCESS;
    }
} 