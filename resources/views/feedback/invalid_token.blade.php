<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Invalid Access') }}</title>
    <style>
        /* Reset CSS to prevent any inheritance issues */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Custom variables */
        :root {
            --primary-color: {{ \App\Models\Setting::get('primary_color', '#4CAF50') }};
            --secondary-color: {{ \App\Models\Setting::get('secondary_color', '#388E3C') }};
            --background-color: {{ \App\Models\Setting::get('background_color', '#f4f7fe') }};
            --card-bg: #ffffff;
            --text-color: #333333;
            --text-light: #6c757d;
            --border-color: #e0e0e0;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
        }

        /* Base styles */
        html, body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--background-color);
            width: 100%;
            height: 100%;
            position: relative;
        }

        /* Main container */
        .page-container {
            width: 100%;
            min-height: 100vh;
            padding: 2rem 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .message-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }

        .message-card {
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: var(--shadow);
            overflow: hidden;
            text-align: center;
            padding: 3rem 2rem;
        }

        .icon-container {
            margin-bottom: 2rem;
        }

        .icon-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #f8d7da;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        .icon {
            font-size: 3rem;
            color: #dc3545;
        }

        .message-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0 0 1rem;
            color: var(--text-color);
        }

        .message-text {
            font-size: 1.1rem;
            color: var(--text-light);
            margin: 0 0 2rem;
        }

        .btn-home {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
        }

        .btn-home:hover {
            background-color: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="message-container">
            <div class="message-card">
                <div class="icon-container">
                    <div class="icon-circle">
                        <span class="icon">&#10060;</span>
                    </div>
                </div>
                <h1 class="message-title">{{ __('Invalid Access Link') }}</h1>
                <p class="message-text">{{ __('The feedback link you are trying to use is invalid or has expired. Please use the link provided in your email or contact our support team for assistance.') }}</p>
                <a href="/" class="btn-home">{{ __('Return Home') }}</a>
            </div>
        </div>
    </div>
</body>
</html> 