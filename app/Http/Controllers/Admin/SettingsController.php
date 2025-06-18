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
            
        // Get the email logo URL if it exists
        $logo_url = Setting::get('email_logo_url', '');
            
        return view('dashboard.settings', compact('settings', 'logo_url'));
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
            'email_logo_url' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            Log::error('Settings validation failed:', $validator->errors()->toArray());
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Update the email logo URL if provided
        if ($request->has('email_logo_url')) {
            Setting::set('email_logo_url', $request->input('email_logo_url'));
            Log::info('Updated email logo URL', ['value' => $request->input('email_logo_url')]);
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
        $language = $request->query('language', 'en');
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'Invalid email address');
        }
        
        // Log attempt
        Log::info('Attempting to send test verification email', [
            'email' => $email, 
            'language' => $language,
            'mail_config' => [
                'driver' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ]
        ]);
        
        $verificationToken = '123456789'; // Test token
        $verificationUrl = route('token.verification') . '?token=' . $verificationToken;
        
        // Get the logo URL from settings
        $logo_url = Setting::get('email_logo_url', '');
        
        // Determine which email template to use based on the requested language
        $emailTemplate = 'emails.token_verification';
        $emailSubject = Setting::get('email_verification_subject_en', 'Aqua Mirage Marrakech - Your WiFi Access Code');
        
        if ($language === 'fr') {
            $emailTemplate = 'emails.token_verification_fr';
            $emailSubject = Setting::get('email_verification_subject_fr', 'Aqua Mirage Marrakech - Votre code d\'accès WiFi');
        } else {
            $emailTemplate = 'emails.token_verification_en';
            $emailSubject = Setting::get('email_verification_subject_en', 'Aqua Mirage Marrakech - Your WiFi Access Code');
        }
        
        try {
            // Prepare email data with all required variables that the template expects
            $emailData = [
                'verificationToken' => $verificationToken,
                'verificationUrl' => $verificationUrl,
                'language' => $language,
                'logo_url' => $logo_url,
                'hotel_name' => 'Aqua Mirage Marrakech',
                'subject' => $emailSubject,
                'greeting' => Setting::get($language === 'fr' ? 'email_verification_greeting_fr' : 'email_verification_greeting_en',
                    $language === 'fr' ? 'Cher(e) Client(e),' : 'Dear Guest,'),
                'intro' => Setting::get($language === 'fr' ? 'email_verification_intro_fr' : 'email_verification_intro_en',
                    $language === 'fr' ? 'Merci d\'utiliser notre service WiFi' : 'Thank you for using our WiFi service'),
                'instructions' => Setting::get($language === 'fr' ? 'email_verification_instructions_fr' : 'email_verification_instructions_en',
                    $language === 'fr' ? 'Veuillez utiliser le code ci-dessous pour accéder à notre WiFi' : 'Please use the code below to access our WiFi'),
                'button_text' => Setting::get($language === 'fr' ? 'email_verification_button_text_fr' : 'email_verification_button_text_en',
                    $language === 'fr' ? 'Accéder au WiFi' : 'Access WiFi'),
                'code_label' => Setting::get($language === 'fr' ? 'email_verification_code_label_fr' : 'email_verification_code_label_en',
                    $language === 'fr' ? 'Votre code d\'accès:' : 'Your access code:'),
                'footer' => Setting::get($language === 'fr' ? 'email_verification_footer_fr' : 'email_verification_footer_en',
                    $language === 'fr' ? '© 2025 Aqua Mirage Marrakech. Tous droits réservés.' : '© 2025 Aqua Mirage Marrakech. All rights reserved.')
            ];
            
            Log::info('Preparing to send verification email', [
                'template' => $emailTemplate,
                'data_keys' => array_keys($emailData)
            ]);
            
            // Using Mail facade
            Mail::send($emailTemplate, $emailData, function ($message) use ($email, $emailSubject) {
                $message->to($email)
                    ->subject($emailSubject)
                    ->from('hotel@aquamiragemarrakech.com', 'Aqua Mirage Marrakech');
            });
            
            // Log successful test email sending
            Log::info('Test verification email sent to: ' . $email . ' using template: ' . $emailTemplate);
            
            return redirect()->back()->with('success', 'Test email sent to ' . $email);
            
        } catch (\Exception $e) {
            // Log detailed email sending error
            Log::error('Failed to send test verification email', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'template' => $emailTemplate,
                'email' => $email
            ]);
            
            return redirect()->back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    /**
     * Show a preview of the email template
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showEmailPreview(Request $request)
    {
        try {
            $language = $request->query('language', 'en');
            $emailType = $request->query('email_type', 'verification');
            
            Log::info('Email preview requested', [
                'email_type' => $emailType,
                'language' => $language,
                'user_agent' => $request->header('User-Agent')
            ]);
            
            // Generate test data
            $verificationToken = '123456';
            $verificationUrl = route('token.verification') . '?token=' . $verificationToken;
            // Use a fixed URL instead of route('home') since it doesn't exist
            $feedbackUrl = url('/feedback');
            
            // Get the logo URL from settings or from the request if it's been changed
            $logo_url = $request->query('logo_url') ?: Setting::get('email_logo_url', '');
            
            // Base data for both email types
            $data = [
                'language' => $language,
                'logo_url' => $logo_url,
                'hotel_name' => 'Aqua Mirage Marrakech'
            ];
            
            // Determine which email template to use based on type and language
            if ($emailType == 'feedback') {
                $emailTemplate = ($language === 'fr') ? 'emails.feedback_fr' : 'emails.feedback_en';
                
                // Add feedback-specific data
                $data['subject'] = Setting::get($language === 'fr' ? 'email_feedback_subject_fr' : 'email_feedback_subject_en', 
                    $language === 'fr' ? 'Aqua Mirage Marrakech - Merci pour votre séjour' : 'Aqua Mirage Marrakech - Thank You for Your Stay');
                $data['greeting'] = Setting::get($language === 'fr' ? 'email_feedback_greeting_fr' : 'email_feedback_greeting_en',
                    $language === 'fr' ? 'Cher(e) Client(e),' : 'Dear Guest,');
                $data['intro'] = Setting::get($language === 'fr' ? 'email_feedback_intro_fr' : 'email_feedback_intro_en',
                    $language === 'fr' ? 'Nous espérons que vous avez apprécié votre séjour' : 'We hope you enjoyed your stay');
                $data['feedback_request'] = Setting::get($language === 'fr' ? 'email_feedback_request_fr' : 'email_feedback_request_en',
                    $language === 'fr' ? 'Nous apprécierions vos commentaires' : 'We would appreciate your feedback');
                $data['button_text'] = Setting::get($language === 'fr' ? 'email_feedback_button_text_fr' : 'email_feedback_button_text_en',
                    $language === 'fr' ? 'Partagez Votre Avis' : 'Share Your Feedback');
                $data['closing'] = Setting::get($language === 'fr' ? 'email_feedback_closing_fr' : 'email_feedback_closing_en',
                    $language === 'fr' ? 'Nous espérons vous accueillir à nouveau' : 'We hope to welcome you back soon');
                $data['footer'] = Setting::get($language === 'fr' ? 'email_feedback_footer_fr' : 'email_feedback_footer_en',
                    $language === 'fr' ? '© 2025 Aqua Mirage Marrakech. Tous droits réservés.' : '© 2025 Aqua Mirage Marrakech. All rights reserved.');
                $data['feedback_url'] = $feedbackUrl;
            } else { // verification is default
                $emailTemplate = ($language === 'fr') ? 'emails.token_verification_fr' : 'emails.token_verification_en';
                
                // Add verification-specific data
                $data['verificationToken'] = $verificationToken;
                $data['verificationUrl'] = $verificationUrl;
            }
            
            // Check if the view exists
            if (!view()->exists($emailTemplate)) {
                throw new \Exception("Email template not found: {$emailTemplate}");
            }
            
            Log::info('Rendering email template', [
                'template' => $emailTemplate, 
                'type' => $emailType,
                'language' => $language,
                'data_keys' => array_keys($data)
            ]);
            
            // Render the view with error handling
            $html = view($emailTemplate, $data)->render();
            
            return response()->json(['success' => true, 'html' => $html]);
        } catch (\Exception $e) {
            Log::error('Failed to generate email preview: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'email_type' => $request->query('email_type', 'verification'),
                'language' => $request->query('language', 'en')
            ]);
            return response()->json([
                'success' => false, 
                'message' => 'Failed to generate preview: ' . $e->getMessage(),
                'details' => [
                    'email_type' => $emailType,
                    'language' => $language,
                    'exception_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ]);
        }
    }

    /**
     * Upload email logo and save its path
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadLogo(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        try {
            // Create logos directory if it doesn't exist
            $uploadPath = public_path('uploads/logos');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            // Get the file from the request
            $logoFile = $request->file('logo');
            
            // Generate a unique file name with timestamp
            $fileName = 'logo_' . time() . '.' . $logoFile->getClientOriginalExtension();
            
            // Move the uploaded file to the destination folder
            $logoFile->move($uploadPath, $fileName);
            
            // Generate the URL for the logo
            $logoUrl = asset('uploads/logos/' . $fileName);
            
            // Save the URL to the settings table
            Setting::set('email_logo_url', $logoUrl);
            
            // Log the successful upload
            Log::info('Logo uploaded successfully', ['url' => $logoUrl]);
            
            // Return success response with the URL
            return response()->json([
                'success' => true,
                'message' => 'Logo uploaded successfully',
                'url' => $logoUrl
            ]);
            
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to upload logo: ' . $e->getMessage());
            
            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload logo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Remove uploaded logo
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeLogo()
    {
        try {
            // Get current logo URL
            $logoUrl = Setting::get('email_logo_url', '');
            
            // If there's a logo URL and it's a file in our server
            if ($logoUrl && !empty($logoUrl) && strpos($logoUrl, asset('uploads/logos/')) === 0) {
                // Extract the filename from the URL
                $fileName = basename($logoUrl);
                $filePath = public_path('uploads/logos/' . $fileName);
                
                // Delete the file if it exists
                if (file_exists($filePath)) {
                    unlink($filePath);
                    Log::info('Logo file deleted', ['file' => $filePath]);
                }
            }
            
            // Clear the logo URL in settings
            Setting::set('email_logo_url', '');
            
            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Logo removed successfully'
            ]);
            
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to remove logo: ' . $e->getMessage());
            
            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove logo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a test feedback email to the specified address
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendTestFeedbackEmail(Request $request)
    {
        $email = $request->query('email');
        $language = $request->query('language', 'en');
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'Invalid email address');
        }
        
        // Log attempt
        Log::info('Attempting to send test feedback email', [
            'email' => $email, 
            'language' => $language,
            'mail_config' => [
                'driver' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ]
        ]);
        
        // Get the logo URL from settings
        $logo_url = Setting::get('email_logo_url', '');
        
        // Generate a feedback URL for testing
        $feedback_url = url('/feedback');
        
        // Determine which email template to use based on the requested language
        $emailTemplate = 'emails.feedback';
        $emailSubject = Setting::get('email_feedback_subject_en', 'Aqua Mirage Marrakech - Thank You for Your Stay');
        
        if ($language === 'fr') {
            $emailTemplate = 'emails.feedback_fr';
            $emailSubject = Setting::get('email_feedback_subject_fr', 'Aqua Mirage Marrakech - Merci pour votre séjour');
        } else {
            $emailTemplate = 'emails.feedback_en';
            $emailSubject = Setting::get('email_feedback_subject_en', 'Aqua Mirage Marrakech - Thank You for Your Stay');
        }
        
        try {
            // Prepare email data with all required variables that the template expects
            $emailData = [
                'language' => $language,
                'logo_url' => $logo_url,
                'hotel_name' => 'Aqua Mirage Marrakech',
                'subject' => $emailSubject,
                'greeting' => Setting::get($language === 'fr' ? 'email_feedback_greeting_fr' : 'email_feedback_greeting_en',
                    $language === 'fr' ? 'Cher(e) Client(e),' : 'Dear Guest,'),
                'intro' => Setting::get($language === 'fr' ? 'email_feedback_intro_fr' : 'email_feedback_intro_en',
                    $language === 'fr' ? 'Nous espérons que vous avez apprécié votre séjour' : 'We hope you enjoyed your stay'),
                'feedback_request' => Setting::get($language === 'fr' ? 'email_feedback_request_fr' : 'email_feedback_request_en',
                    $language === 'fr' ? 'Nous apprécierions vos commentaires' : 'We would appreciate your feedback'),
                'button_text' => Setting::get($language === 'fr' ? 'email_feedback_button_text_fr' : 'email_feedback_button_text_en',
                    $language === 'fr' ? 'Partagez Votre Avis' : 'Share Your Feedback'),
                'closing' => Setting::get($language === 'fr' ? 'email_feedback_closing_fr' : 'email_feedback_closing_en',
                    $language === 'fr' ? 'Nous espérons vous accueillir à nouveau' : 'We hope to welcome you back soon'),
                'footer' => Setting::get($language === 'fr' ? 'email_feedback_footer_fr' : 'email_feedback_footer_en',
                    $language === 'fr' ? '© 2025 Aqua Mirage Marrakech. Tous droits réservés.' : '© 2025 Aqua Mirage Marrakech. All rights reserved.'),
                'feedback_url' => $feedback_url
            ];
            
            Log::info('Preparing to send feedback email', [
                'template' => $emailTemplate,
                'data_keys' => array_keys($emailData)
            ]);
            
            // Using Mail facade
            Mail::send($emailTemplate, $emailData, function ($message) use ($email, $emailSubject) {
                $message->to($email)
                    ->subject($emailSubject)
                    ->from('hotel@aquamiragemarrakech.com', 'Aqua Mirage Marrakech');
            });
            
            // Log successful test email sending
            Log::info('Test feedback email sent to: ' . $email . ' using template: ' . $emailTemplate);
            
            return redirect()->back()->with('success', 'Test feedback email sent to ' . $email);
            
        } catch (\Exception $e) {
            // Log detailed email sending error
            Log::error('Failed to send test feedback email', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'template' => $emailTemplate,
                'email' => $email
            ]);
            
            return redirect()->back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    /**
     * Send a test email directly with detailed error reporting
     *
     * @param string $email
     * @param string $language
     * @return void
     * @throws \Exception
     */
    public function sendTestEmailDirect($email, $language = 'en')
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Invalid email address');
        }
        
        // Log attempt
        Log::info('Attempting to send direct test verification email', [
            'email' => $email, 
            'language' => $language,
            'mail_config' => [
                'driver' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ]
        ]);
        
        $verificationToken = '123456789'; // Test token
        $verificationUrl = route('token.verification') . '?token=' . $verificationToken;
        
        // Get the logo URL from settings
        $logo_url = Setting::get('email_logo_url', '');
        
        // Determine which email template to use based on the requested language
        $emailTemplate = 'emails.token_verification';
        $emailSubject = Setting::get('email_verification_subject_en', 'Aqua Mirage Marrakech - Your WiFi Access Code');
        
        if ($language === 'fr') {
            $emailTemplate = 'emails.token_verification_fr';
            $emailSubject = Setting::get('email_verification_subject_fr', 'Aqua Mirage Marrakech - Votre code d\'accès WiFi');
        } else {
            $emailTemplate = 'emails.token_verification_en';
            $emailSubject = Setting::get('email_verification_subject_en', 'Aqua Mirage Marrakech - Your WiFi Access Code');
        }
        
        // Check if the template exists
        if (!view()->exists($emailTemplate)) {
            Log::error('Email template does not exist', ['template' => $emailTemplate]);
            throw new \Exception("Email template not found: {$emailTemplate}");
        }
        
        // Prepare email data with all required variables that the template expects
        $emailData = [
            'verificationToken' => $verificationToken,
            'verificationUrl' => $verificationUrl,
            'language' => $language,
            'logo_url' => $logo_url,
            'hotel_name' => 'Aqua Mirage Marrakech',
            'subject' => $emailSubject,
            'greeting' => Setting::get($language === 'fr' ? 'email_verification_greeting_fr' : 'email_verification_greeting_en',
                $language === 'fr' ? 'Cher(e) Client(e),' : 'Dear Guest,'),
            'intro' => Setting::get($language === 'fr' ? 'email_verification_intro_fr' : 'email_verification_intro_en',
                $language === 'fr' ? 'Merci d\'utiliser notre service WiFi' : 'Thank you for using our WiFi service'),
            'instructions' => Setting::get($language === 'fr' ? 'email_verification_instructions_fr' : 'email_verification_instructions_en',
                $language === 'fr' ? 'Veuillez utiliser le code ci-dessous pour accéder à notre WiFi' : 'Please use the code below to access our WiFi'),
            'button_text' => Setting::get($language === 'fr' ? 'email_verification_button_text_fr' : 'email_verification_button_text_en',
                $language === 'fr' ? 'Accéder au WiFi' : 'Access WiFi'),
            'code_label' => Setting::get($language === 'fr' ? 'email_verification_code_label_fr' : 'email_verification_code_label_en',
                $language === 'fr' ? 'Votre code d\'accès:' : 'Your access code:'),
            'footer' => Setting::get($language === 'fr' ? 'email_verification_footer_fr' : 'email_verification_footer_en',
                $language === 'fr' ? '© 2025 Aqua Mirage Marrakech. Tous droits réservés.' : '© 2025 Aqua Mirage Marrakech. All rights reserved.')
        ];
        
        Log::info('Preparing to send direct verification email', [
            'template' => $emailTemplate,
            'data_keys' => array_keys($emailData)
        ]);
        
        // Attempt to render the view to check for template errors
        try {
            $renderedView = view($emailTemplate, $emailData)->render();
            Log::info('View rendered successfully', ['length' => strlen($renderedView)]);
        } catch (\Exception $e) {
            Log::error('Failed to render email template', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Failed to render email template: ' . $e->getMessage());
        }
        
        // Using Mail facade with improved error handling
        try {
            Mail::send($emailTemplate, $emailData, function ($message) use ($email, $emailSubject) {
                $message->to($email)
                    ->subject($emailSubject)
                    ->from(config('mail.from.address', 'hotel@aquamiragemarrakech.com'), 
                          config('mail.from.name', 'Aqua Mirage Marrakech'));
            });
            
            // Log successful test email sending
            Log::info('Test direct verification email sent to: ' . $email . ' using template: ' . $emailTemplate);
        } catch (\Exception $e) {
            Log::error('Failed to send test email', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Failed to send test email: ' . $e->getMessage());
        }
    }
    
    /**
     * Send a test feedback email directly with detailed error reporting
     *
     * @param string $email
     * @param string $language
     * @return void
     * @throws \Exception
     */
    public function sendTestFeedbackEmailDirect($email, $language = 'en')
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Invalid email address');
        }
        
        // Log attempt
        Log::info('Attempting to send direct test feedback email', [
            'email' => $email, 
            'language' => $language,
            'mail_config' => [
                'driver' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ]
        ]);
        
        // Get the logo URL from settings
        $logo_url = Setting::get('email_logo_url', '');
        
        // Generate a feedback URL for testing
        $feedback_url = url('/feedback');
        
        // Determine which email template to use based on the requested language
        $emailTemplate = 'emails.feedback';
        $emailSubject = Setting::get('email_feedback_subject_en', 'Aqua Mirage Marrakech - Thank You for Your Stay');
        
        if ($language === 'fr') {
            $emailTemplate = 'emails.feedback_fr';
            $emailSubject = Setting::get('email_feedback_subject_fr', 'Aqua Mirage Marrakech - Merci pour votre séjour');
        } else {
            $emailTemplate = 'emails.feedback_en';
            $emailSubject = Setting::get('email_feedback_subject_en', 'Aqua Mirage Marrakech - Thank You for Your Stay');
        }
        
        // Check if the template exists
        if (!view()->exists($emailTemplate)) {
            Log::error('Email template does not exist', ['template' => $emailTemplate]);
            throw new \Exception("Email template not found: {$emailTemplate}");
        }
        
        // Prepare email data with all required variables that the template expects
        $emailData = [
            'language' => $language,
            'logo_url' => $logo_url,
            'hotel_name' => 'Aqua Mirage Marrakech',
            'subject' => $emailSubject,
            'greeting' => Setting::get($language === 'fr' ? 'email_feedback_greeting_fr' : 'email_feedback_greeting_en',
                $language === 'fr' ? 'Cher(e) Client(e),' : 'Dear Guest,'),
            'intro' => Setting::get($language === 'fr' ? 'email_feedback_intro_fr' : 'email_feedback_intro_en',
                $language === 'fr' ? 'Nous espérons que vous avez apprécié votre séjour' : 'We hope you enjoyed your stay'),
            'feedback_request' => Setting::get($language === 'fr' ? 'email_feedback_request_fr' : 'email_feedback_request_en',
                $language === 'fr' ? 'Nous apprécierions vos commentaires' : 'We would appreciate your feedback'),
            'button_text' => Setting::get($language === 'fr' ? 'email_feedback_button_text_fr' : 'email_feedback_button_text_en',
                $language === 'fr' ? 'Partagez Votre Avis' : 'Share Your Feedback'),
            'closing' => Setting::get($language === 'fr' ? 'email_feedback_closing_fr' : 'email_feedback_closing_en',
                $language === 'fr' ? 'Nous espérons vous accueillir à nouveau' : 'We hope to welcome you back soon'),
            'footer' => Setting::get($language === 'fr' ? 'email_feedback_footer_fr' : 'email_feedback_footer_en',
                $language === 'fr' ? '© 2025 Aqua Mirage Marrakech. Tous droits réservés.' : '© 2025 Aqua Mirage Marrakech. All rights reserved.'),
            'feedback_url' => $feedback_url
        ];
        
        Log::info('Preparing to send direct feedback email', [
            'template' => $emailTemplate,
            'data_keys' => array_keys($emailData)
        ]);
        
        // Attempt to render the view to check for template errors
        try {
            $renderedView = view($emailTemplate, $emailData)->render();
            Log::info('View rendered successfully', ['length' => strlen($renderedView)]);
        } catch (\Exception $e) {
            Log::error('Failed to render email template', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Failed to render email template: ' . $e->getMessage());
        }
        
        // Using Mail facade with improved error handling
        try {
            Mail::send($emailTemplate, $emailData, function ($message) use ($email, $emailSubject) {
                $message->to($email)
                    ->subject($emailSubject)
                    ->from(config('mail.from.address', 'hotel@aquamiragemarrakech.com'), 
                          config('mail.from.name', 'Aqua Mirage Marrakech'));
            });
            
            // Log successful test email sending
            Log::info('Test direct feedback email sent to: ' . $email . ' using template: ' . $emailTemplate);
        } catch (\Exception $e) {
            Log::error('Failed to send feedback email', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Failed to send feedback email: ' . $e->getMessage());
        }
    }

    /**
     * A direct API endpoint for testing email delivery with detailed debugging
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testEmailDelivery(Request $request)
    {
        $email = $request->input('email');
        $language = $request->input('language', 'en');
        $type = $request->input('type', 'verification'); // verification or feedback
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email address'
            ], 400);
        }
        
        // Gather detailed information about server and mail configuration
        $serverInfo = [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
            'mail_config' => [
                'driver' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'encryption' => config('mail.mailers.smtp.encryption'),
                'username' => config('mail.mailers.smtp.username') ? '********' : null,
                'password' => config('mail.mailers.smtp.password') ? '********' : null,
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ]
        ];
        
        Log::info('Email delivery test requested', [
            'email' => $email,
            'language' => $language,
            'type' => $type,
            'server_info' => $serverInfo
        ]);
        
        try {
            if ($type === 'feedback') {
                // Call the method directly rather than through routing
                $this->sendTestFeedbackEmailDirect($email, $language);
            } else {
                // Default to verification email
                $this->sendTestEmailDirect($email, $language);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully.',
                'details' => [
                    'recipient' => $email,
                    'language' => $language,
                    'type' => $type,
                    'server_info' => $serverInfo
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Email delivery test failed', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $email,
                'type' => $type,
                'language' => $language
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
                'details' => [
                    'exception_type' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'server_info' => $serverInfo
                ]
            ], 500);
        }
    }
}
