<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($subject) ? $subject : \App\Models\Setting::get('email_feedback_subject_fr', 'Aqua Mirage Marrakech - Merci pour votre séjour') }}</title>
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
                <img src="{{ $logo_url }}" alt="Logo de l'Hôtel" class="hotel-logo">
            @else
                <h2 style="color: white; margin: 0;">{{ $hotel_name ?? 'Aqua Mirage Marrakech' }}</h2>
            @endif
        </div>
        
        <div class="email-content">
            <div class="greeting">
                {{ $greeting ?? \App\Models\Setting::get('email_feedback_greeting_fr', 'Cher(e) Client(e),') }}
            </div>
            
            <div class="message">
                {{ $intro ?? \App\Models\Setting::get('email_feedback_intro_fr', 'Nous espérons que vous avez apprécié votre séjour à l\'Aqua Mirage Marrakech. Merci de nous avoir choisi pour votre visite.') }}
            </div>
            
            <div class="feedback-container">
                <div class="feedback-title">Votre Avis Est Important</div>
                <p>
                    {{ $feedback_request ?? \App\Models\Setting::get('email_feedback_request_fr', 'Nous apprécierions grandement vos commentaires sur notre service WiFi. A-t-il été utile pendant votre séjour? Avez-vous des suggestions d\'amélioration?') }}
                </p>
            </div>
            
            <div class="button-container">
                <a href="{{ $feedback_url ?? url('/feedback') }}" class="feedback-button">
                    {{ $button_text ?? \App\Models\Setting::get('email_feedback_button_text_fr', 'Partagez Votre Avis') }}
                </a>
            </div>
            
            <div class="closing">
                {{ $closing ?? \App\Models\Setting::get('email_feedback_closing_fr', 'Nous espérons vous accueillir à nouveau bientôt pour une autre expérience merveilleuse.') }}
            </div>
        </div>
        
        <div class="email-footer">
            {{ $footer ?? \App\Models\Setting::get('email_feedback_footer_fr', '© 2025 Aqua Mirage Marrakech. Tous droits réservés.') }}
        </div>
    </div>
</body>
</html> 