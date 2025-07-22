<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Restaurant Client Registration')</title>
    <link rel="stylesheet" href="{{ secure_asset('css/styles.css') }}">
    <style>
        :root {
            --primary-color: {{ \App\Models\Setting::get('primary_color', '#92E3A9') }};
            --secondary-color: {{ \App\Models\Setting::get('secondary_color', '#4CAF50') }};
            --background-color: {{ \App\Models\Setting::get('background_color', '#F4F7FE') }};
            --text-color: #333;
            --error-color: #FF5252;
        }
    </style>
    <!-- Debug info (remove in production) -->
    <!-- 
    Primary: {{ \App\Models\Setting::get('primary_color', 'default') }}
    Secondary: {{ \App\Models\Setting::get('secondary_color', 'default') }}
    Background: {{ \App\Models\Setting::get('background_color', 'default') }}
    -->
    @yield('css')
</head>
<body class="@yield('body-class')">
    <div class="container">
        @yield('content')
    </div>
   



   @yield('scripts')
</body>
</html>