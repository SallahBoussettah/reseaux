<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($subject) ? $subject : \App\Models\Setting::get('email_feedback_subject_en', 'Aqua Mirage Marrakech - Thank You for Your Stay') }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background-color: #1e88e5;
            padding: 20px;
            text-align: center;
        }
        .hotel-logo {
            max-width: 200px;
            max-height: 150px;
            margin: 0 auto;
            display: block;
        }
        .email-content {
            padding: 30px;
            line-height: 1.6;
        }
        .greeting {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 15px;
            color: #1e88e5;
        }
        .message {
            margin-bottom: 25px;
        }
        .feedback-container {
            background-color: #f0f7ff;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .feedback-title {
            font-weight: bold;
            color: #1e88e5;
            margin-bottom: 10px;
        }
        .button-container {
            text-align: center;
            margin: 25px 0;
        }
        .feedback-button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #1e88e5;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
        .closing {
            margin-top: 25px;
        }
        .email-footer {
            background-color: #f5f5f5;
            padding: 15px;
            text-align: center;
            font-size: 0.8rem;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            @if(isset($logo_url) && !empty($logo_url))
                <img src="{{ $logo_url }}" alt="Hotel Logo" class="hotel-logo">
            @else
                <h2 style="color: white; margin: 0;">{{ $hotel_name ?? 'Aqua Mirage Marrakech' }}</h2>
            @endif
        </div>
        
        <div class="email-content">
            <div class="greeting">
                {{ $greeting ?? \App\Models\Setting::get('email_feedback_greeting_en', 'Dear Guest,') }}
            </div>
            
            <div class="message">
                {{ $intro ?? \App\Models\Setting::get('email_feedback_intro_en', 'We hope you enjoyed your stay at Aqua Mirage Marrakech. Thank you for choosing us for your visit.') }}
            </div>
            
            <div class="feedback-container">
                <div class="feedback-title">Your Feedback Matters</div>
                <p>
                    {{ $feedback_request ?? \App\Models\Setting::get('email_feedback_request_en', 'We would greatly appreciate your feedback on our WiFi service. Was it helpful during your stay? Do you have any suggestions for improvement?') }}
                </p>
            </div>
            
            <div class="button-container">
                <a href="{{ $feedback_url ?? url('/feedback') }}" class="feedback-button">
                    {{ $button_text ?? \App\Models\Setting::get('email_feedback_button_text_en', 'Share Your Feedback') }}
                </a>
            </div>
            
            <div class="closing">
                {{ $closing ?? \App\Models\Setting::get('email_feedback_closing_en', 'We hope to welcome you back soon for another wonderful experience.') }}
            </div>
        </div>
        
        <div class="email-footer">
            {{ $footer ?? \App\Models\Setting::get('email_feedback_footer_en', '© 2025 Aqua Mirage Marrakech. All rights reserved.') }}
        </div>
    </div>
</body>
</html> 