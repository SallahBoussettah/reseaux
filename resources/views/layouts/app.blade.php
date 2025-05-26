<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Restaurant Client Registration')</title>
    <link rel="stylesheet" href="{{ secure_asset('css/styles.css') }}">
    @yield('css')
</head>
<body>
    <div class="container">
        @yield('content')
    </div>
   



   @yield('scripts')
</body>
</html>