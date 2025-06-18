<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \App\Models\Setting::get('email_verification_subject_en', 'Aqua Mirage Marrakech - Your WiFi Access Code') }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .email-header {
            text-align: center;
            padding: 30px 0;
            background: linear-gradient(135deg, #001d3d, #003566);
        }
        .wifi-logo {
            width: 120px;
            height: 120px;
            margin: 0 auto;
            display: block;
        }
        .hotel-logo {
            margin: 0 auto;
            max-width: 200px;
            max-height: 150px;
            height: auto;
            display: block;
        }
        .email-content {
            padding: 30px;
            color: #333;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #001d3d;
        }
        .message {
            font-size: 15px;
            color: #444;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        .token-container {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }
        .token-label {
            font-size: 15px;
            color: #4a5568;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        .token {
            font-size: 32px;
            font-weight: bold;
            color: #003566;
            letter-spacing: 3px;
            margin: 0;
            font-family: 'Courier New', monospace;
        }
        .expiry {
            font-size: 14px;
            color: #e67e22;
            margin-top: 25px;
            font-style: italic;
            text-align: center;
        }
        .email-footer {
            margin-top: 30px;
            padding: 20px 30px;
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
            font-size: 12px;
            color: #718096;
            text-align: center;
        }
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100%;
                border-radius: 0;
            }
            .email-content {
                padding: 20px;
            }
            .token {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <!-- Only show the hotel logo if available -->
            @if(!empty(\App\Models\Setting::get('email_logo_url')))
            <img src="{{ \App\Models\Setting::get('email_logo_url') }}" alt="Aqua Mirage Marrakech Logo" class="hotel-logo">
            @else
            <!-- Fallback if no logo is uploaded -->
            <h2 style="color: white; margin: 0;">Aqua Mirage Marrakech</h2>
            @endif
        </div>
        
        <div class="email-content">
            <p class="greeting">
                {{ \App\Models\Setting::get('email_verification_greeting_en', 'Dear Client,') }}
            </p>
            
            <p class="message">
                {{ \App\Models\Setting::get('email_verification_intro_en', 'We are delighted to welcome you to Aqua Mirage Marrakech and wish you a most pleasant stay.') }}
            </p>
            
            <p class="message">
                {{ \App\Models\Setting::get('email_verification_additional_info_en', 'As part of our commitment to providing excellent service, we are pleased to provide you with the WiFi access code (5 devices) for your convenience:') }}
            </p>
            
            <div class="token-container">
                <p class="token-label">WiFi Access Code</p>
                <p class="token">{{ $verificationToken }}</p>
            </div>
            
            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ $verificationUrl }}" style="display: inline-block; background: linear-gradient(135deg, #003566, #0353a4); color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; font-weight: 600; letter-spacing: 0.5px; transition: all 0.3s;">
                    Verify Email & Connect to WiFi
                </a>
            </div>
            
            <p class="expiry">
                {{ \App\Models\Setting::get('email_verification_expiry_text_en', 'You will be disconnected after 15 minutes of inactivity. Please reconnect using the access code above.') }}
            </p>
        </div>
        
        <div class="email-footer">
            <p>{{ \App\Models\Setting::get('email_verification_footer_en', '© 2025 Aqua Mirage Marrakech. All rights reserved.') }}</p>
        </div>
    </div>
</body>
</html> 