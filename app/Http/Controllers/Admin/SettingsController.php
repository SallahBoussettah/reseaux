<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SettingsController extends Controller
{
    /**
     * Display the settings page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        // Direct database query to check current values
        $dbSettings = DB::table('settings')->get();
        Log::info('Current settings in database:', $dbSettings->toArray());
        
        // Get settings grouped by their group
        $settings = Setting::orderBy('group')
            ->orderBy('order')
            ->get()
            ->groupBy('group');
            
        return view('dashboard.settings', compact('settings'));
    }
    
    /**
     * Update settings
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        // Debug: Log all request data
        Log::info('Settings update request data:', $request->all());
        
        $validator = Validator::make($request->all(), [
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            Log::error('Settings validation failed:', $validator->errors()->toArray());
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $settings = $request->input('settings', []);
        
        // Debug: Log settings array
        Log::info('Settings to update:', $settings);
        
        foreach ($settings as $setting) {
            if (isset($setting['key'])) {
                // Debug: Log each setting being updated
                Log::info('Updating setting:', [
                    'key' => $setting['key'],
                    'value' => $setting['value'] ?? 'null'
                ]);
                
                Setting::set($setting['key'], $setting['value'] ?? '');
                
                // Debug: Verify the setting was saved correctly
                $savedValue = Setting::get($setting['key']);
                Log::info('Setting after save:', [
                    'key' => $setting['key'],
                    'saved_value' => $savedValue
                ]);
                
                // Direct database check
                $dbValue = DB::table('settings')->where('key', $setting['key'])->value('value');
                Log::info('Database value after save:', [
                    'key' => $setting['key'],
                    'db_value' => $dbValue
                ]);
            }
        }
        
        // Clear all settings cache
        Cache::flush();
        
        // Verify all settings after cache flush
        $allSettings = Setting::all();
        Log::info('All settings after update:', $allSettings->toArray());
        
        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully');
    }

    /**
     * Send a test email to the specified address
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendTestEmail(Request $request)
    {
        $email = $request->query('email');
        $language = $request->query('lang', 'en');
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'Invalid email address');
        }
        
        $verificationToken = '123456'; // Test token
        $verificationUrl = route('token.verification');
        
        // Determine which email template to use based on the requested language
        $emailTemplate = 'emails.token_verification';
        $emailSubject = Setting::get('email_verification_subject_en', 'Your WiFi Verification Code');
        
        if ($language === 'fr') {
            $emailTemplate = 'emails.token_verification_fr';
            $emailSubject = Setting::get('email_verification_subject_fr', 'Votre code de vérification WiFi');
        } else {
            $emailTemplate = 'emails.token_verification_en';
            $emailSubject = Setting::get('email_verification_subject_en', 'Your WiFi Verification Code');
        }
        
        try {
            Mail::send($emailTemplate, [
                'verificationToken' => $verificationToken,
                'verificationUrl' => $verificationUrl,
                'language' => $language
            ], function ($message) use ($email, $emailSubject) {
                $message->to($email)
                    ->subject($emailSubject)
                    ->from('wifi@eureka-communication.com');
            });
            
            // Log successful test email sending
            Log::info('Test verification email sent to: ' . $email . ' using template: ' . $emailTemplate);
            
            return redirect()->back()->with('success', 'Test email sent to ' . $email);
            
        } catch (\Exception $e) {
            // Log email sending error
            Log::error('Failed to send test verification email: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }
}
