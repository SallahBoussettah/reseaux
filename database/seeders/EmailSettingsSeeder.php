<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class EmailSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            // English email settings
            [
                'key' => 'email_verification_subject_en',
                'value' => 'Your WiFi Verification Code',
                'group' => 'emails',
                'type' => 'text',
                'label' => 'Email Subject (English)',
                'description' => 'Subject line for verification emails in English',
                'order' => 1
            ],
            [
                'key' => 'email_verification_heading_en',
                'value' => 'Your WiFi Verification Code',
                'group' => 'emails',
                'type' => 'text',
                'label' => 'Email Heading (English)',
                'description' => 'Heading displayed at the top of verification emails in English',
                'order' => 2
            ],
            [
                'key' => 'email_verification_greeting_en',
                'value' => 'Hello,',
                'group' => 'emails',
                'type' => 'text',
                'label' => 'Email Greeting (English)',
                'description' => 'Greeting line in verification emails in English',
                'order' => 3
            ],
            [
                'key' => 'email_verification_intro_en',
                'value' => 'Thank you for using our WiFi service. To access your premium connection, please use the verification code below:',
                'group' => 'emails',
                'type' => 'textarea',
                'label' => 'Email Intro Text (English)',
                'description' => 'Introduction paragraph in verification emails in English',
                'order' => 4
            ],
            [
                'key' => 'email_verification_expiry_text_en',
                'value' => 'This code is valid for 15 minutes. If you don\'t use it within this period, you\'ll need to request a new code.',
                'group' => 'emails',
                'type' => 'textarea',
                'label' => 'Expiry Text (English)',
                'description' => 'Text explaining the code expiration in English',
                'order' => 5
            ],
            [
                'key' => 'email_verification_button_text_en',
                'value' => 'Verify My Code',
                'group' => 'emails',
                'type' => 'text',
                'label' => 'Button Text (English)',
                'description' => 'Text for the verification button in English',
                'order' => 6
            ],
            [
                'key' => 'email_verification_button_intro_en',
                'value' => 'Click the button below to enter your code:',
                'group' => 'emails',
                'type' => 'text',
                'label' => 'Button Intro (English)',
                'description' => 'Text before the verification button in English',
                'order' => 7
            ],
            [
                'key' => 'email_verification_footer_en',
                'value' => 'If you didn\'t request this code, please ignore this email.',
                'group' => 'emails',
                'type' => 'textarea',
                'label' => 'Footer Text (English)',
                'description' => 'Text at the bottom of verification emails in English',
                'order' => 8
            ],
            [
                'key' => 'email_premium_duration_days',
                'value' => '7',
                'group' => 'emails',
                'type' => 'text',
                'label' => 'Premium Duration (Days)',
                'description' => 'Number of days of premium access granted after verification',
                'order' => 9
            ],
            
            // French email settings
            [
                'key' => 'email_verification_subject_fr',
                'value' => 'Votre code de vérification WiFi',
                'group' => 'emails',
                'type' => 'text',
                'label' => 'Email Subject (French)',
                'description' => 'Subject line for verification emails in French',
                'order' => 10
            ],
            [
                'key' => 'email_verification_heading_fr',
                'value' => 'Votre code de vérification WiFi',
                'group' => 'emails',
                'type' => 'text',
                'label' => 'Email Heading (French)',
                'description' => 'Heading displayed at the top of verification emails in French',
                'order' => 11
            ],
            [
                'key' => 'email_verification_greeting_fr',
                'value' => 'Bonjour,',
                'group' => 'emails',
                'type' => 'text',
                'label' => 'Email Greeting (French)',
                'description' => 'Greeting line in verification emails in French',
                'order' => 12
            ],
            [
                'key' => 'email_verification_intro_fr',
                'value' => 'Merci d\'avoir utilisé notre service WiFi. Pour accéder à votre connexion premium, veuillez utiliser le code de vérification ci-dessous:',
                'group' => 'emails',
                'type' => 'textarea',
                'label' => 'Email Intro Text (French)',
                'description' => 'Introduction paragraph in verification emails in French',
                'order' => 13
            ],
            [
                'key' => 'email_verification_expiry_text_fr',
                'value' => 'Ce code est valable pendant 15 minutes. Si vous ne l\'utilisez pas dans ce délai, vous devrez demander un nouveau code.',
                'group' => 'emails',
                'type' => 'textarea',
                'label' => 'Expiry Text (French)',
                'description' => 'Text explaining the code expiration in French',
                'order' => 14
            ],
            [
                'key' => 'email_verification_button_text_fr',
                'value' => 'Vérifier mon code',
                'group' => 'emails',
                'type' => 'text',
                'label' => 'Button Text (French)',
                'description' => 'Text for the verification button in French',
                'order' => 15
            ],
            [
                'key' => 'email_verification_button_intro_fr',
                'value' => 'Cliquez sur le bouton ci-dessous pour entrer votre code:',
                'group' => 'emails',
                'type' => 'text',
                'label' => 'Button Intro (French)',
                'description' => 'Text before the verification button in French',
                'order' => 16
            ],
            [
                'key' => 'email_verification_footer_fr',
                'value' => 'Si vous n\'avez pas demandé ce code, veuillez ignorer cet email.',
                'group' => 'emails',
                'type' => 'textarea',
                'label' => 'Footer Text (French)',
                'description' => 'Text at the bottom of verification emails in French',
                'order' => 17
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
} 