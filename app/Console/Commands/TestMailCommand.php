<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;

class TestMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email : The email address to send the test to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email sending functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Attempting to send test email to: {$email}");
        
        try {
            // Log environment variables for debugging
            Log::info('Mail environment variables:', [
                'MAIL_MAILER' => env('MAIL_MAILER'),
                'MAIL_HOST' => env('MAIL_HOST'),
                'MAIL_PORT' => env('MAIL_PORT'),
                'MAIL_USERNAME' => env('MAIL_USERNAME'),
                'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
                'MAIL_FROM_NAME' => env('MAIL_FROM_NAME')
            ]);
            
            // Send a simple test email
            $result = Mail::raw('This is a test email from the TestMailCommand.', function (Message $message) use ($email) {
                $message->to($email)
                        ->subject('Test Email from Laravel App')
                        ->from(env('MAIL_FROM_ADDRESS', 'hotel@aquamiragemarrakech.com'), env('MAIL_FROM_NAME', 'Aqua Mirage Marrakech'));
            });
            
            Log::info('Mail send result', ['result' => $result]);
            $this->info('Email sent successfully! Check logs for more details.');
            
        } catch (\Exception $e) {
            $this->error("Failed to send email: " . $e->getMessage());
            Log::error('Failed to send test email', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 