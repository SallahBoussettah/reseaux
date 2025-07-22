<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - {{ __('Feedback Received') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    
    <style>
        /* CSS Reset */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        /* Variables */
        :root {
            --primary-color: #3B82F6;
            --primary-dark: #2563EB;
            --secondary-color: #10B981;
            --text-color: #1F2937;
            --text-light: #6B7280;
            --background-color: #F9FAFB;
            --border-color: #E5E7EB;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --success: #10B981;
            --danger: #EF4444;
            --warning: #F59E0B;
            --info: #3B82F6;
        }
        
        /* Base Styles */
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.5;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        /* Card Styles */
        .card {
            background-color: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .card-header {
            background-color: var(--secondary-color);
            color: white;
            padding: 1.5rem;
            text-align: center;
            position: relative;
        }
        
        .card-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }
        
        .card-body {
            padding: 2rem;
            text-align: center;
        }
        
        /* Icon Styles */
        .icon-circle {
            background-color: var(--secondary-color);
            color: white;
            border-radius: 50%;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        
        .icon-circle i {
            font-size: 2.5rem;
        }
        
        /* Message Styles */
        .message-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-color);
        }
        
        .message-text {
            color: var(--text-light);
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
        /* Button Styles */
        .btn {
            display: inline-block;
            background-color: var(--secondary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.2s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn:hover {
            background-color: #0DA271;
        }
        
        /* Footer */
        .footer {
            margin-top: auto;
            text-align: center;
            padding: 1.5rem;
            color: var(--text-light);
            font-size: 0.875rem;
        }
        
        /* Responsive */
        @media (max-width: 640px) {
            .container {
                padding: 1rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
            
            .message-title {
                font-size: 1.25rem;
            }
            
            .message-text {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>{{ __('Feedback Received') }}</h1>
            </div>
            <div class="card-body">
                <div class="icon-circle">
                    <i class="uil uil-check-circle"></i>
                </div>
                <h2 class="message-title">{{ __('Thank You for Your Feedback!') }}</h2>
                <p class="message-text">
                    {{ __('We appreciate you taking the time to share your experience with us. Your feedback is valuable and helps us improve our services for all guests.') }}
                </p>
                <a href="{{ url('/') }}" class="btn">
                    <i class="uil uil-estate"></i> {{ __('Return to Homepage') }}
                </a>
            </div>
        </div>
    </div>
    
    <footer class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</p>
    </footer>
</body>
</html> 