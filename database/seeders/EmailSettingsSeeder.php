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
                'value' => 'Aqua Mirage Marrakech - Your WiFi Access Code',
                'group' => 'emails',
                'type' => 'text',
                'label' => 'Email Subject (English)',
                'description' => 'Subject line for verification emails in English',
                'order' => 1
            ],
            [
                'key' => 'email_verification_greeting_en',
                'value' => 'Dear Client,',
                'group' => 'emails',
                'type' => 'text',
                'label' => 'Email Greeting (English)',
                'description' => 'Greeting line in verification emails in English',
                'order' => 2
            ],
            [
                'key' => 'email_verification_intro_en',
                'value' => 'We are delighted to welcome you to Aqua Mirage Marrakech and wish you a most pleasant stay.',
                'group' => 'emails',
                'type' => 'textarea',
                'label' => 'Email Intro Text (English)',
                'description' => 'Introduction paragraph in verification emails in English',
                'order' => 3
            ],
            [
                'key' => 'email_verification_additional_info_en',
                'value' => 'As part of our commitment to providing excellent service, we are pleased to provide you with the WiFi access code (5 devices) for your convenience:',
                'group' => 'emails',
                'type' => 'textarea',
                'label' => 'Additional Information (English)',
                'description' => 'Additional text before showing the WiFi code in English',
                'order' => 4
            ],
            [
                'key' => 'email_verification_expiry_text_en',
                'value' => 'You will be disconnected after 15 minutes of inactivity. Please reconnect using the access code above.',
                'group' => 'emails',
                'type' => 'textarea',
                'label' => 'Expiry Text (English)',
                'description' => 'Text explaining the connection behavior in English',
                'order' => 5
            ],
            [
                'key' => 'email_verification_footer_en',
                'value' => '© 2025 Aqua Mirage Marrakech. All rights reserved.',
                'group' => 'emails',
                'type' => 'textarea',
                'label' => 'Footer Text (English)',
                'description' => 'Text at the bottom of verification emails in English',
                'order' => 6
            ],
            [
                'key' => 'email_premium_duration_days',
                'value' => '7',
                'group' => 'emails',
                'type' => 'text',
                'label' => 'Premium Duration (Days)',
                'description' => 'Number of days of premium access granted after verification',
                'order' => 7
            ],
            
            // French email settings
            [
                'key' => 'email_verification_subject_fr',
                'value' => 'Aqua Mirage Marrakech - Votre code d\'accès WiFi',
                'group' => 'emails',
                'type' => 'text',
                'label' => 'Email Subject (French)',
                'description' => 'Subject line for verification emails in French',
                'order' => 8
            ],
            [
                'key' => 'email_verification_greeting_fr',
                'value' => 'Cher(e) Client(e),',
                'group' => 'emails',
                'type' => 'text',
                'label' => 'Email Greeting (French)',
                'description' => 'Greeting line in verification emails in French',
                'order' => 9
            ],
            [
                'key' => 'email_verification_intro_fr',
                'value' => 'Nous sommes ravis de vous accueillir à l\'Aqua Mirage Marrakech et vous souhaitons un séjour des plus agréables.',
                'group' => 'emails',
                'type' => 'textarea',
                'label' => 'Email Intro Text (French)',
                'description' => 'Introduction paragraph in verification emails in French',
                'order' => 10
            ],
            [
                'key' => 'email_verification_additional_info_fr',
                'value' => 'Dans le cadre de notre engagement à fournir un excellent service, nous sommes heureux de vous communiquer les détails du code d\'accès au Wi-Fi (5 appareils) pour votre commodité :',
                'group' => 'emails',
                'type' => 'textarea',
                'label' => 'Additional Information (French)',
                'description' => 'Additional text before showing the WiFi code in French',
                'order' => 11
            ],
            [
                'key' => 'email_verification_expiry_text_fr',
                'value' => 'Vous serez déconnecté après 15 minutes d\'utilisation. Veuillez vous reconnecter au Wi-Fi en utilisant le code d\'accès ci-dessus.',
                'group' => 'emails',
                'type' => 'textarea',
                'label' => 'Expiry Text (French)',
                'description' => 'Text explaining the connection behavior in French',
                'order' => 12
            ],
            [
                'key' => 'email_verification_footer_fr',
                'value' => '© 2025 Aqua Mirage Marrakech. Tous droits réservés.',
                'group' => 'emails',
                'type' => 'textarea',
                'label' => 'Footer Text (French)',
                'description' => 'Text at the bottom of verification emails in French',
                'order' => 13
            ],
            // Email logo URL setting
            [
                'key' => 'email_logo_url',
                'value' => '',
                'group' => 'emails',
                'type' => 'text',
                'label' => 'Email Logo URL',
                'description' => 'URL for the logo displayed in email templates',
                'order' => 14
            ],
            
            // Feedback Email Settings - English
            [
                'key' => 'email_feedback_subject_en',
                'value' => 'Aqua Mirage Marrakech - Thank You for Your Stay',
                'group' => 'emails',
                'type' => 'text',
                'label' => 'Feedback Email Subject (English)',
                'description' => 'Subject line for feedback emails in English',
                'order' => 15
            ],
            [
                'key' => 'email_feedback_greeting_en',
                'value' => 'Dear Guest,',
                'group' => 'emails',
                'type' => 'text',
                'label' => 'Feedback Email Greeting (English)',
                'description' => 'Greeting line in feedback emails in English',
                'order' => 16
            ],
            [
                'key' => 'email_feedback_intro_en',
                'value' => 'We hope you enjoyed your stay at Aqua Mirage Marrakech. Thank you for choosing us for your visit.',
                'group' => 'emails',
                'type' => 'textarea',
                'label' => 'Feedback Email Intro (English)',
                'description' => 'Introduction text for feedback emails in English',
                'order' => 17
            ],
            [
                'key' => 'email_feedback_request_en',
                'value' => 'We would greatly appreciate your feedback on our WiFi service. Was it helpful during your stay? Do you have any suggestions for improvement?',
                'group' => 'emails',
                'type' => 'textarea',
                'label' => 'Feedback Request Text (English)',
                'description' => 'Text asking for feedback in English',
                'order' => 18
            ],
            [
                'key' => 'email_feedback_button_text_en',
                'value' => 'Share Your Feedback',
                'group' => 'emails',
                'type' => 'text',
                'label' => 'Feedback Button Text (English)',
                'description' => 'Text for the feedback button in English',
                'order' => 19
            ],
            [
                'key' => 'email_feedback_closing_en',
                'value' => 'We hope to welcome you back soon for another wonderful experience.',
                'group' => 'emails',
                'type' => 'textarea',
                'label' => 'Feedback Email Closing (English)',
                'description' => 'Closing message for feedback emails in English',
                'order' => 20
            ],
            [
                'key' => 'email_feedback_footer_en',
                'value' => '© 2025 Aqua Mirage Marrakech. All rights reserved.',
                'group' => 'emails',
                'type' => 'textarea',
                'label' => 'Feedback Footer Text (English)',
                'description' => 'Text at the bottom of feedback emails in English',
                'order' => 21
            ],
            
            // Feedback Email Settings - French
            [
                'key' => 'email_feedback_subject_fr',
                'value' => 'Aqua Mirage Marrakech - Merci pour votre séjour',
                'group' => 'emails',
                'type' => 'text',
                'label' => 'Feedback Email Subject (French)',
                'description' => 'Subject line for feedback emails in French',
                'order' => 22
            ],
            [
                'key' => 'email_feedback_greeting_fr',
                'value' => 'Cher(e) Client(e),',
                'group' => 'emails',
                'type' => 'text',
                'label' => 'Feedback Email Greeting (French)',
                'description' => 'Greeting line in feedback emails in French',
                'order' => 23
            ],
            [
                'key' => 'email_feedback_intro_fr',
                'value' => 'Nous espérons que vous avez apprécié votre séjour à l\'Aqua Mirage Marrakech. Merci de nous avoir choisi pour votre visite.',
                'group' => 'emails',
                'type' => 'textarea',
                'label' => 'Feedback Email Intro (French)',
                'description' => 'Introduction text for feedback emails in French',
                'order' => 24
            ],
            [
                'key' => 'email_feedback_request_fr',
                'value' => 'Nous apprécierions grandement vos commentaires sur notre service WiFi. A-t-il été utile pendant votre séjour? Avez-vous des suggestions d\'amélioration?',
                'group' => 'emails',
                'type' => 'textarea',
                'label' => 'Feedback Request Text (French)',
                'description' => 'Text asking for feedback in French',
                'order' => 25
            ],
            [
                'key' => 'email_feedback_button_text_fr',
                'value' => 'Partagez Votre Avis',
                'group' => 'emails',
                'type' => 'text',
                'label' => 'Feedback Button Text (French)',
                'description' => 'Text for the feedback button in French',
                'order' => 26
            ],
            [
                'key' => 'email_feedback_closing_fr',
                'value' => 'Nous espérons vous accueillir à nouveau bientôt pour une autre expérience merveilleuse.',
                'group' => 'emails',
                'type' => 'textarea',
                'label' => 'Feedback Email Closing (French)',
                'description' => 'Closing message for feedback emails in French',
                'order' => 27
            ],
            [
                'key' => 'email_feedback_footer_fr',
                'value' => '© 2025 Aqua Mirage Marrakech. Tous droits réservés.',
                'group' => 'emails',
                'type' => 'textarea',
                'label' => 'Feedback Footer Text (French)',
                'description' => 'Text at the bottom of feedback emails in French',
                'order' => 28
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