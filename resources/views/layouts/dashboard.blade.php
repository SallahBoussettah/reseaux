<!doctype html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Dashboard') - Wifi Gestion</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- inject:css-->
    <link rel="stylesheet" href="{{asset('assets/vendor/css/bootstrap/bootstrap.css')}}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.27.0/dist/apexcharts.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    <!-- endinject -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('assets/img/favicon.svg')}}">
    <!-- Fonts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@iconscout/unicons@4.0.8/css/line.min.css">
    
    <script>
        // Render localStorage JS:
        if (localStorage.theme) document.documentElement.setAttribute("data-theme", localStorage.theme);
        if (localStorage.layout) document.documentElement.setAttribute("data-nav", localStorage.navbar);
        if (localStorage.layout) document.documentElement.setAttribute("dir", localStorage.layout);
    </script>   
</head>

<body class="geex-dashboard demo-invoice">
    
<header class="geex-header">
    <div class="geex-header__wrapper">
        <div class="geex-header__logo-wrapper">
            <a href="index.html" class="geex-header__logo">
                <img class="logo-lite" src="{{ asset('assets/img/logo-dark.svg')}}" alt="Header logo" />
                <img class="logo-dark" src="{{ asset('assets/img/logo-lite.svg')}}" alt="Header logo" />
            </a>
        </div>
    </div>
</header>



    <main class="geex-main-content">
        

<div class="geex-sidebar">
    <a href="#" class="geex-sidebar__close">
        <i class="uil uil-times"></i>
    </a>
    <div class="geex-sidebar__wrapper">
        <div class="geex-sidebar__header">
            <a href="index.html" class="geex-sidebar__logo">
                <img class="logo-lite" src="{{asset('assets/img/wifi-logo.png')}}" alt="logo" />
                <img class="logo-dark" src="{{asset('assets/img/wifi-logo.png')}}" alt="logo" />
            </a>
        </div>
        <nav class="geex-sidebar__menu-wrapper">
            <ul class="geex-sidebar__menu">
                <li class="geex-sidebar__menu__item has-children">
                    <a href="{{ route('dashboard') }}" class="geex-sidebar__menu__link">
                        <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_1139_9707)">
                            <path d="M21.1943 8.31319L14.2413 1.35936C13.3808 0.501345 12.2152 0.0195312 11 0.0195312C9.78482 0.0195312 8.61921 0.501345 7.75868 1.35936L0.805761 8.31319C0.549484 8.56782 0.3463 8.8708 0.207987 9.20454C0.0696733 9.53829 -0.00101787 9.89617 1.10729e-05 10.2574V19.2564C1.10729e-05 19.9857 0.289742 20.6852 0.805467 21.2009C1.32119 21.7166 2.02067 22.0064 2.75001 22.0064H19.25C19.9794 22.0064 20.6788 21.7166 21.1946 21.2009C21.7103 20.6852 22 19.9857 22 19.2564V10.2574C22.001 9.89617 21.9303 9.53829 21.792 9.20454C21.6537 8.8708 21.4505 8.56782 21.1943 8.31319ZM13.75 20.173H8.25001V16.5669C8.25001 15.8375 8.53974 15.138 9.05547 14.6223C9.57119 14.1066 10.2707 13.8169 11 13.8169C11.7294 13.8169 12.4288 14.1066 12.9446 14.6223C13.4603 15.138 13.75 15.8375 13.75 16.5669V20.173ZM20.1667 19.2564C20.1667 19.4995 20.0701 19.7326 19.8982 19.9045C19.7263 20.0764 19.4931 20.173 19.25 20.173H15.5833V16.5669C15.5833 15.3513 15.1005 14.1855 14.2409 13.3259C13.3814 12.4664 12.2156 11.9835 11 11.9835C9.78444 11.9835 8.61865 12.4664 7.75911 13.3259C6.89956 14.1855 6.41668 15.3513 6.41668 16.5669V20.173H2.75001C2.5069 20.173 2.27374 20.0764 2.10183 19.9045C1.92992 19.7326 1.83334 19.4995 1.83334 19.2564V10.2574C1.83419 10.0145 1.93068 9.78168 2.10193 9.60935L9.05485 2.65827C9.57157 2.14396 10.271 1.85522 11 1.85522C11.7291 1.85522 12.4285 2.14396 12.9452 2.65827L19.8981 9.61211C20.0687 9.78375 20.1651 10.0155 20.1667 10.2574V19.2564Z" fill="#B9BBBD"/>
                            </g>
                            <defs>
                            <clipPath id="clip0_1139_9707">
                            <rect width="22" height="22" fill="white"/>
                            </clipPath>
                            </defs>
                        </svg>                          
                        <span>Accueil</span>
                    </a>  
                </li>

                <li class="geex-sidebar__menu__item has-children">
                    <a href="{{ route('clients') }}" class="geex-sidebar__menu__link">
                        <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_1139_9709)">
                                <path d="M1.83333 10.0833H11.9167C12.4029 10.0833 12.8692 9.89018 13.213 9.54636C13.5568 9.20255 13.75 8.73623 13.75 8.25V1.83333C13.75 1.3471 13.5568 0.880788 13.213 0.536971C12.8692 0.193154 12.4029 0 11.9167 0L1.83333 0C1.3471 0 0.880788 0.193154 0.536971 0.536971C0.193154 0.880788 0 1.3471 0 1.83333L0 8.25C0 8.73623 0.193154 9.20255 0.536971 9.54636C0.880788 9.89018 1.3471 10.0833 1.83333 10.0833ZM1.83333 1.83333H11.9167V8.25H1.83333V1.83333Z" fill="#B9BBBD"/>
                                <path d="M20.1673 0H17.4173C16.9311 0 16.4648 0.193154 16.121 0.536971C15.7771 0.880788 15.584 1.3471 15.584 1.83333V8.25C15.584 8.73623 15.7771 9.20255 16.121 9.54636C16.4648 9.89018 16.9311 10.0833 17.4173 10.0833H20.1673C20.6535 10.0833 21.1199 9.89018 21.4637 9.54636C21.8075 9.20255 22.0007 8.73623 22.0007 8.25V1.83333C22.0007 1.3471 21.8075 0.880788 21.4637 0.536971C21.1199 0.193154 20.6535 0 20.1673 0V0ZM20.1673 8.25H17.4173V1.83333H20.1673V8.25Z" fill="#B9BBBD"/>
                                <path d="M4.58333 11.917H1.83333C1.3471 11.917 0.880788 12.1101 0.536971 12.454C0.193154 12.7978 0 13.2641 0 13.7503L0 20.167C0 20.6532 0.193154 21.1195 0.536971 21.4633C0.880788 21.8072 1.3471 22.0003 1.83333 22.0003H4.58333C5.06956 22.0003 5.53588 21.8072 5.8797 21.4633C6.22351 21.1195 6.41667 20.6532 6.41667 20.167V13.7503C6.41667 13.2641 6.22351 12.7978 5.8797 12.454C5.53588 12.1101 5.06956 11.917 4.58333 11.917ZM4.58333 20.167H1.83333V13.7503H4.58333V20.167Z" fill="#B9BBBD"/>
                                <path d="M20.1667 11.917H10.0833C9.5971 11.917 9.13079 12.1101 8.78697 12.454C8.44315 12.7978 8.25 13.2641 8.25 13.7503V20.167C8.25 20.6532 8.44315 21.1195 8.78697 21.4633C9.13079 21.8072 9.5971 22.0003 10.0833 22.0003H20.1667C20.6529 22.0003 21.1192 21.8072 21.463 21.4633C21.8068 21.1195 22 20.6532 22 20.167V13.7503C22 13.2641 21.8068 12.7978 21.463 12.454C21.1192 12.1101 20.6529 11.917 20.1667 11.917ZM20.1667 20.167H10.0833V13.7503H20.1667V20.167Z" fill="#B9BBBD"/>
                            </g>
                            <defs>
                                <clipPath id="clip0_1139_9709">
                                    <rect width="22" height="22" fill="white"/>
                                </clipPath>
                            </defs>
                        </svg>                          
                        <span>Clients</span>
                    </a>  

                </li>
                <li class="geex-sidebar__menu__item has-children">
                    <a href="{{ route('statistics') }}" class="geex-sidebar__menu__link">
                        <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_1139_9714)">
                                <path d="M6.41667 0H3.66667C2.69421 0 1.76158 0.386308 1.07394 1.07394C0.386308 1.76158 0 2.69421 0 3.66667L0 6.41667C0 7.38913 0.386308 8.32176 1.07394 9.00939C1.76158 9.69702 2.69421 10.0833 3.66667 10.0833H6.41667C7.38913 10.0833 8.32176 9.69702 9.00939 9.00939C9.69702 8.32176 10.0833 7.38913 10.0833 6.41667V3.66667C10.0833 2.69421 9.69702 1.76158 9.00939 1.07394C8.32176 0.386308 7.38913 0 6.41667 0V0ZM8.25 6.41667C8.25 6.9029 8.05684 7.36921 7.71303 7.71303C7.36921 8.05684 6.9029 8.25 6.41667 8.25H3.66667C3.18044 8.25 2.71412 8.05684 2.3703 7.71303C2.02649 7.36921 1.83333 6.9029 1.83333 6.41667V3.66667C1.83333 3.18044 2.02649 2.71412 2.3703 2.3703C2.71412 2.02649 3.18044 1.83333 3.66667 1.83333H6.41667C6.9029 1.83333 7.36921 2.02649 7.71303 2.3703C8.05684 2.71412 8.25 3.18044 8.25 3.66667V6.41667Z" fill="#B9BBBD"/>
                                <path d="M18.3327 0H15.5827C14.6102 0 13.6776 0.386309 12.99 1.07394C12.3023 1.76158 11.916 2.69421 11.916 3.66667V6.41667C11.916 7.38913 12.3023 8.32176 12.99 9.0094C13.6776 9.69703 14.6102 10.0833 15.5827 10.0833H18.3327C19.3051 10.0833 20.2378 9.69703 20.9254 9.0094C21.613 8.32176 21.9993 7.38913 21.9993 6.41667V3.66667C21.9993 2.69421 21.613 1.76158 20.9254 1.07394C20.2378 0.386309 19.3051 0 18.3327 0V0ZM20.166 6.41667C20.166 6.9029 19.9728 7.36922 19.629 7.71303C19.2852 8.05685 18.8189 8.25 18.3327 8.25H15.5827C15.0964 8.25 14.6301 8.05685 14.2863 7.71303C13.9425 7.36922 13.7493 6.9029 13.7493 6.41667V3.66667C13.7493 3.18044 13.9425 2.71412 14.2863 2.37031C14.6301 2.02649 15.0964 1.83333 15.5827 1.83333H18.3327C18.8189 1.83333 19.2852 2.02649 19.629 2.37031C19.9728 2.71412 20.166 3.18044 20.166 3.66667V6.41667Z" fill="#B9BBBD"/>
                                <path d="M6.41667 11.9167H3.66667C2.69421 11.9167 1.76158 12.3031 1.07394 12.9907C0.386308 13.6783 0 14.611 0 15.5834L0 18.3334C0 19.3059 0.386308 20.2385 1.07394 20.9262C1.76158 21.6138 2.69421 22.0001 3.66667 22.0001H6.41667C7.38913 22.0001 8.32176 21.6138 9.00939 20.9262C9.69702 20.2385 10.0833 19.3059 10.0833 18.3334V15.5834C10.0833 14.611 9.69702 13.6783 9.00939 12.9907C8.32176 12.3031 7.38913 11.9167 6.41667 11.9167ZM8.25 18.3334C8.25 18.8197 8.05684 19.286 7.71303 19.6298C7.36921 19.9736 6.9029 20.1668 6.41667 20.1668H3.66667C3.18044 20.1668 2.71412 19.9736 2.3703 19.6298C2.02649 19.286 1.83333 18.8197 1.83333 18.3334V15.5834C1.83333 15.0972 2.02649 14.6309 2.3703 14.2871C2.71412 13.9432 3.18044 13.7501 3.66667 13.7501H6.41667C6.9029 13.7501 7.36921 13.9432 7.71303 14.2871C8.05684 14.6309 8.25 15.0972 8.25 15.5834V18.3334Z" fill="#B9BBBD"/>
                                <path d="M18.3327 11.9167H15.5827C14.6102 11.9167 13.6776 12.3031 12.99 12.9907C12.3023 13.6783 11.916 14.611 11.916 15.5834V18.3334C11.916 19.3059 12.3023 20.2385 12.99 20.9262C13.6776 21.6138 14.6102 22.0001 15.5827 22.0001H18.3327C19.3051 22.0001 20.2378 21.6138 20.9254 20.9262C21.613 20.2385 21.9993 19.3059 21.9993 18.3334V15.5834C21.9993 14.611 21.613 13.6783 20.9254 12.9907C20.2378 12.3031 19.3051 11.9167 18.3327 11.9167ZM20.166 18.3334C20.166 18.8197 19.9728 19.286 19.629 19.6298C19.2852 19.9736 18.8189 20.1668 18.3327 20.1668H15.5827C15.0964 20.1668 14.6301 19.9736 14.2863 19.6298C13.9425 19.286 13.7493 18.8197 13.7493 18.3334V15.5834C13.7493 15.0972 13.9425 14.6309 14.2863 14.2871C14.6301 13.9432 15.0964 13.7501 15.5827 13.7501H18.3327C18.8189 13.7501 19.2852 13.9432 19.629 14.2871C19.9728 14.6309 20.166 15.0972 20.166 15.5834V18.3334Z" fill="#B9BBBD"/>
                            </g>
                            <defs>
                                <clipPath id="clip0_1139_9714">
                                    <rect width="22" height="22" fill="white"/>
                                </clipPath>
                            </defs>
                        </svg>                          
                        <span>Statistique</span>
                    </a>  
                </li>
            </ul>
        </nav>
        <div class="geex-sidebar__footer">
         
            <p class="geex-sidebar__footer__copyright">© 2025 All Rights Reserved</p>
            <p class="geex-sidebar__footer__author">Made with <span class="heart-icon">♥</span> by <a href="#">Eureka Digital</a></p>
        </div>
    </div>
</div>
    
 
        

        <div class="geex-content">
             @yield('content')
        </div>
    </main>

    <!-- inject:js-->
    <script src="{{asset('assets/vendor/js/jquery/jquery-3.5.1.min.js')}}"></script>
    <script src="{{asset('assets/vendor/js/jquery/jquery-ui.js')}}"></script>
    <script src="{{asset('assets/vendor/js/bootstrap/bootstrap.min.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.27.0/dist/apexcharts.min.js"></script>
    <!--<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>-->
    <script src="{{asset('assets/js/main.js')}}"></script>
    @yield('scripts')
    <!-- endinject-->
</body>

</html>