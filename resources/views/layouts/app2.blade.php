<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Restaurant Client Registration')</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- inject:css-->
    <link rel="stylesheet" href="{{asset('assets/vendor/css/bootstrap/bootstrap.css')}}">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    <!-- endinject -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('assets/img/favicon.svg')}}">
    <!-- Fonts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@iconscout/unicons@4.0.8/css/line.min.css">

    @yield('css')
</head>
<body class="geex-dashboard authentication-page">
        <main class="geex-content">
        <div class="geex-content__authentication">
            <div class="geex-content__authentication__content">
                @yield('content')
                
            </div>  
            <div class="geex-content__authentication__img">
                <img src="{{asset('assets/img/authentication.svg')}}" alt="">
            </div>
        </div>
    </main>
   @yield('scripts')
   <script src="{{asset('assets/vendor/js/jquery/jquery-3.5.1.min.js')}}"></script>
    <script src="{{asset('assets/vendor/js/jquery/jquery-ui.js')}}"></script>
    <script src="{{asset('assets/vendor/js/bootstrap/bootstrap.min.js')}}"></script>
    <script src="{{asset('assets/js/main.js')}}"></script>
</body>
</html>