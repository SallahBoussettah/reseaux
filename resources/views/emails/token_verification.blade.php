<!DOCTYPE html>
<html>
<head>
    <title>WiFi Verification Code</title>
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
            background-color: #4CAF50;
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
            <h2>
                @if(isset($language) && $language === 'fr')
                    Votre code de vérification WiFi
                @else
                    Your WiFi Verification Code
                @endif
            </h2>
        </div>
        
        <p>
            @if(isset($language) && $language === 'fr')
                Bonjour,
            @else
                Hello,
            @endif
        </p>
        
        <p>
            @if(isset($language) && $language === 'fr')
                Merci d'avoir utilisé notre service WiFi. Pour accéder à votre connexion premium, veuillez utiliser le code de vérification ci-dessous:
            @else
                Thank you for using our WiFi service. To access your premium connection, please use the verification code below:
            @endif
        </p>
        
        <div class="token">{{ $verificationToken }}</div>
        
        <p>
            @if(isset($language) && $language === 'fr')
                Ce code est valable pendant 15 minutes. Si vous ne l'utilisez pas dans ce délai, vous devrez demander un nouveau code.
            @else
                This code is valid for 15 minutes. If you don't use it within this period, you'll need to request a new code.
            @endif
        </p>
        
        <div class="verification-link">
            <p>
                @if(isset($language) && $language === 'fr')
                    Cliquez sur le bouton ci-dessous pour entrer votre code:
                @else
                    Click the button below to enter your code:
                @endif
            </p>
            <a href="{{ url(route('token.verification')) }}">
                @if(isset($language) && $language === 'fr')
                    Vérifier mon code
                @else
                    Verify My Code
                @endif
            </a>
        </div>
        
        <p>
            @if(isset($language) && $language === 'fr')
                Si vous n'avez pas demandé ce code, veuillez ignorer cet email.
            @else
                If you didn't request this code, please ignore this email.
            @endif
        </p>
        
        <div class="footer">
            <p>
                @if(isset($language) && $language === 'fr')
                    &copy; {{ date('Y') }} Eureka Digital. Tous droits réservés.
                @else
                    &copy; {{ date('Y') }} Eureka Digital. All rights reserved.
                @endif
            </p>
        </div>
    </div>
</body>
</html> 