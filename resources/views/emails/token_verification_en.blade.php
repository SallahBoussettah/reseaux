<!DOCTYPE html>
<html>
<head>
    <title>{{ \App\Models\Setting::get('email_verification_subject_en', 'Your Verification Code') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .header {
            text-align: center;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .token {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 5px;
            margin: 20px 0;
            letter-spacing: 5px;
        }
        .verification-link {
            text-align: center;
            margin: 20px 0;
        }
        .verification-link a {
            display: inline-block;
            padding: 10px 20px;
            background-color: {{ \App\Models\Setting::get('secondary_color', '#4CAF50') }};
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .verification-link a:hover {
            background-color: #45a049;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{{ \App\Models\Setting::get('email_verification_heading_en', 'Your WiFi Verification Code') }}</h2>
        </div>
        
        <p>{{ \App\Models\Setting::get('email_verification_greeting_en', 'Hello,') }}</p>
        
        <p>{{ \App\Models\Setting::get('email_verification_intro_en', 'Thank you for using our WiFi service. To access your premium connection, please use the verification code below:') }}</p>
        
        <div class="token">{{ $verificationToken }}</div>
        
        <p>{{ \App\Models\Setting::get('email_verification_expiry_text_en', 'This code is valid for 15 minutes. If you don\'t use it within this period, you\'ll need to request a new code.') }}</p>
        
        <div class="verification-link">
            <p>{{ \App\Models\Setting::get('email_verification_button_intro_en', 'Click the button below to enter your code:') }}</p>
            <a href="{{ url(route('token.verification')) }}">{{ \App\Models\Setting::get('email_verification_button_text_en', 'Verify My Code') }}</a>
        </div>
        
        <p>{{ \App\Models\Setting::get('email_verification_footer_en', 'If you didn\'t request this code, please ignore this email.') }}</p>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} Eureka Digital. All rights reserved.</p>
        </div>
    </div>
</body>
</html> 