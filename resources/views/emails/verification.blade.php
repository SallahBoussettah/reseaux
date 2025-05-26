<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Vérifiez votre adresse e-mail</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Vérifiez votre adresse e-mail</h1>
        <p>Merci de vous être inscrit ! Veuillez cliquer sur le bouton ci-dessous pour vérifier votre adresse e-mail:</p>
        <a href="{{ $verificationLink }}" class="button">Vérifier l'e-mail</a>
    </div>
</body>
</html>