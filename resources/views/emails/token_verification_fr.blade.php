<!DOCTYPE html>
<html>
<head>
    <title>Votre code de vérification</title>
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
            <h2>Votre code de vérification WiFi</h2>
        </div>
        
        <p>Bonjour,</p>
        
        <p>Merci d'avoir utilisé notre service WiFi. Pour accéder à votre connexion premium, veuillez utiliser le code de vérification ci-dessous:</p>
        
        <div class="token">{{ $verificationToken }}</div>
        
        <p>Ce code est valable pendant 15 minutes. Si vous ne l'utilisez pas dans ce délai, vous devrez demander un nouveau code.</p>
        
        <div class="verification-link">
            <p>Cliquez sur le bouton ci-dessous pour entrer votre code:</p>
            <a href="{{ url(route('token.verification')) }}">Vérifier mon code</a>
        </div>
        
        <p>Si vous n'avez pas demandé ce code, veuillez ignorer cet email.</p>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} Eureka Digital. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html> 