<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        // Run the command every 5 minutes
        $schedule->command('users:update-login-stats')->everyFiveMinutes();

        // Schedule the premium expiration check to run daily
        $schedule->command('premium:check-expired')->daily();
        
        // Schedule the premium user deletion check to run every minute (for testing)
        // Later this can be changed to run every hour or daily
        $schedule->command('users:delete-expired')->everyMinute();
        
        // Schedule the user banning check to run every minute (for testing)
        // Later this can be changed to run every hour or daily
        $schedule->command('users:ban-scheduled')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
