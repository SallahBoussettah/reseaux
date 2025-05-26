@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
            <div class="geex-content__header">
                <div class="geex-content__header__content">
                    <h2 class="geex-content__header__title">Tableau de bord</h2>
                    <p class="geex-content__header__subtitle">Bienvenue sur Eureka Wifi Dashboard</p>
                </div> 
                
                <div class="geex-content__header__action">
                    <div class="geex-content__header__action__wrap">
                        <ul class="geex-content__header__quickaction">
                            <li class="geex-content__header__quickaction__item">
                                <a href="#" class="geex-content__header__quickaction__link">
                                    <img class="user-img" src="assets/img/avatar/user.svg" alt="user" />
                                </a>
                                <div class="geex-content__header__popup geex-content__header__popup--author">
                                    <div class="geex-content__header__popup__header">
                                        <div class="geex-content__header__popup__header__img">
                                            <img src="assets/img/avatar/user.svg" alt="user" />
                                        </div>
                                        <div class="geex-content__header__popup__header__content">
                                            <h3 class="geex-content__header__popup__header__title">Eureka Wifi</h3>
                                            <span class="geex-content__header__popup__header__subtitle">Admin</span>
                                        </div>
                                    </div>
                                    <div class="geex-content__header__popup__content">
                                        <ul class="geex-content__header__popup__items">
                                            <li class="geex-content__header__popup__item">
                                                <a class="geex-content__header__popup__link" href="#">
                                                    <i class="uil uil-user"></i>
                                                    Profile
                                                </a>
                                            </li>
                                            <li class="geex-content__header__popup__item">
                                                <a class="geex-content__header__popup__link" href="#">
                                                    <i class="uil uil-cog"></i>
                                                    Settings
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="geex-content__header__popup__footer">
                                        <a href="#" class="geex-content__header__popup__footer__link">
                                            <i class="uil uil-arrow-up-left"></i>Déconnexion
                                        </a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div> 
            </div> 

            <div class="geex-content__wrapper">
                <div class="geex-content__section-wrapper">
                    <div class="geex-content__summary">
                        <div class="geex-content__summary__count">
                            <div class="geex-content__summary__count__single primay-bg">
                                <div class="geex-content__summary__count__single__content">
                                    <h4 class="geex-content__summary__count__single__title">{{ $totalUsers }}</h4>
                                    <p class="geex-content__summary__count__single__subtitle"><h2>Utilisateurs total</h2></p>
                                </div>
                                <div class="geex-content__summary__count__single__icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                                        <path d="M26.9908 5.10791C26.7542 4.84524 26.4229 4.68728 26.0699 4.66878C25.7168 4.65027 25.3709 4.77274 25.1081 5.00925L12.7148 16.1626L6.94277 10.3906C6.81978 10.2632 6.67265 10.1617 6.50998 10.0918C6.34731 10.0219 6.17235 9.98512 5.99531 9.98358C5.81827 9.98204 5.6427 10.0158 5.47884 10.0828C5.31497 10.1499 5.16611 10.2489 5.04091 10.3741C4.91572 10.4992 4.81672 10.6481 4.74968 10.812C4.68264 10.9758 4.6489 11.1514 4.65044 11.3285C4.65198 11.5055 4.68876 11.6804 4.75864 11.8431C4.82852 12.0058 4.93009 12.1529 5.05744 12.2759L11.7241 18.9426C11.9656 19.184 12.2905 19.3235 12.6319 19.3325C12.9732 19.3414 13.305 19.219 13.5588 18.9906L26.8921 6.99058C27.1548 6.75397 27.3127 6.42272 27.3312 6.06968C27.3498 5.71663 27.2273 5.37069 26.9908 5.10791Z" fill="#464255"/>
                                        <path d="M25.1085 13.0093L12.7152 24.1626L6.94321 18.3906C6.69174 18.1478 6.35494 18.0134 6.00534 18.0164C5.65575 18.0195 5.32133 18.1597 5.07412 18.4069C4.82691 18.6541 4.68668 18.9885 4.68364 19.3381C4.68061 19.6877 4.815 20.0245 5.05788 20.276L11.7245 26.9426C11.966 27.1841 12.291 27.3236 12.6323 27.3325C12.9737 27.3415 13.3054 27.2191 13.5592 26.9906L26.8925 14.9906C27.1473 14.752 27.2983 14.423 27.3131 14.0742C27.3279 13.7255 27.2054 13.3848 26.9718 13.1254C26.7383 12.866 26.4123 12.7086 26.0639 12.6868C25.7155 12.6651 25.3725 12.7809 25.1085 13.0093Z" fill="#464255"/>
                                      </svg>
                                </div>
                            </div>
                            <div class="geex-content__summary__count__single danger-bg">
                                <div class="geex-content__summary__count__single__content">
                                    <h4 class="geex-content__summary__count__single__title">{{ $connectedThisMonth }}</h4>
                                    <p class="geex-content__summary__count__single__subtitle">Connectés ce mois</p>
                                </div>
                                <div class="geex-content__summary__count__single__icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                                        <path d="M15.9997 1.33321C13.0989 1.33321 10.2632 2.19339 7.85132 3.80498C5.4394 5.41658 3.55953 7.7072 2.44945 10.3872C1.33936 13.0672 1.04891 16.0161 1.61483 18.8612C2.18075 21.7063 3.57761 24.3196 5.62878 26.3708C7.67995 28.4219 10.2933 29.8188 13.1384 30.3847C15.9834 30.9506 18.9324 30.6602 21.6124 29.5501C24.2924 28.44 26.583 26.5602 28.1946 24.1482C29.8062 21.7363 30.6664 18.9007 30.6664 15.9999C30.6618 12.1114 29.1151 8.38358 26.3655 5.63404C23.616 2.8845 19.8881 1.33779 15.9997 1.33321ZM15.9997 27.9999C13.6263 27.9999 11.3062 27.2961 9.33284 25.9775C7.35945 24.6589 5.82138 22.7848 4.91313 20.5921C4.00488 18.3994 3.76724 15.9866 4.23026 13.6588C4.69328 11.331 5.83617 9.19282 7.5144 7.51459C9.19263 5.83636 11.3308 4.69347 13.6586 4.23045C15.9864 3.76743 18.3992 4.00507 20.5919 4.91332C22.7846 5.82157 24.6587 7.35964 25.9773 9.33303C27.2959 11.3064 27.9997 13.6265 27.9997 15.9999C27.9958 19.1813 26.7303 22.2313 24.4807 24.4809C22.2311 26.7305 19.1811 27.996 15.9997 27.9999Z" fill="#464255"/>
                                        <path d="M18.9433 7.55344C18.1839 7.05351 17.3108 6.75286 16.4046 6.67923C15.4984 6.60559 14.5882 6.76134 13.758 7.13211C12.8218 7.54836 12.0291 8.23143 11.4792 9.09587C10.9292 9.96031 10.6463 10.9677 10.666 11.9921V12.0001C10.6671 12.3537 10.8086 12.6925 11.0594 12.9417C11.3101 13.191 11.6497 13.3305 12.0033 13.3294C12.357 13.3284 12.6957 13.1869 12.945 12.9361C13.1943 12.6853 13.3337 12.3457 13.3327 11.9921C13.3191 11.4931 13.4503 11.0009 13.7106 10.5749C13.9709 10.149 14.3491 9.80763 14.7993 9.59211C15.224 9.3921 15.6928 9.30424 16.161 9.33692C16.6292 9.3696 17.0813 9.52172 17.474 9.77878C17.8246 10.0106 18.1154 10.3221 18.3227 10.6877C18.53 11.0533 18.6479 11.4627 18.6669 11.8826C18.6859 12.3025 18.6054 12.7209 18.4319 13.1037C18.2584 13.4865 17.9969 13.8229 17.6687 14.0854C16.756 14.7825 16.0122 15.6761 15.4923 16.7001C14.9725 17.7241 14.6901 18.852 14.666 20.0001C14.6666 20.1752 14.7017 20.3485 14.7693 20.51C14.8369 20.6715 14.9356 20.8182 15.0598 20.9416C15.1841 21.0649 15.3314 21.1626 15.4934 21.2291C15.6554 21.2955 15.8289 21.3294 16.004 21.3288C16.1791 21.3282 16.3524 21.2931 16.5139 21.2255C16.6754 21.1579 16.8221 21.0592 16.9454 20.935C17.0688 20.8107 17.1665 20.6634 17.233 20.5014C17.2994 20.3394 17.3333 20.1659 17.3327 19.9908C17.3582 19.2435 17.5512 18.5115 17.8974 17.8488C18.2435 17.186 18.734 16.6094 19.3327 16.1614C19.9879 15.6361 20.5098 14.9634 20.8559 14.1982C21.202 13.4329 21.3624 12.5968 21.3242 11.7578C21.286 10.9188 21.0502 10.1008 20.636 9.37015C20.2218 8.63954 19.6409 8.01708 18.9407 7.55344H18.9433Z" fill="#464255"/>
                                        <path d="M16.0003 25.3335C16.7367 25.3335 17.3337 24.7365 17.3337 24.0001C17.3337 23.2637 16.7367 22.6668 16.0003 22.6668C15.2639 22.6668 14.667 23.2637 14.667 24.0001C14.667 24.7365 15.2639 25.3335 16.0003 25.3335Z" fill="#464255"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="geex-content__summary__count__single success-bg">
                                <div class="geex-content__summary__count__single__content">
                                    <h4 class="geex-content__summary__count__single__title">{{ $newUsers }}</h4>
                                    <p class="geex-content__summary__count__single__subtitle">Nouveaux utilisateurs</p>
                                </div>
                                <div class="geex-content__summary__count__single__icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                                        <path d="M15.9997 1.33335C13.0989 1.33335 10.2632 2.19353 7.85132 3.80513C5.4394 5.41672 3.55953 7.70734 2.44945 10.3873C1.33936 13.0673 1.04891 16.0163 1.61483 18.8613C2.18075 21.7064 3.57761 24.3197 5.62878 26.3709C7.67995 28.4221 10.2933 29.8189 13.1384 30.3849C15.9834 30.9508 18.9324 30.6603 21.6124 29.5502C24.2924 28.4402 26.583 26.5603 28.1946 24.1484C29.8062 21.7365 30.6664 18.9008 30.6664 16C30.6618 12.1116 29.1151 8.38372 26.3655 5.63418C23.616 2.88464 19.8881 1.33793 15.9997 1.33335ZM15.9997 28C13.6263 28 11.3062 27.2962 9.33284 25.9776C7.35945 24.6591 5.82138 22.7849 4.91313 20.5922C4.00488 18.3995 3.76724 15.9867 4.23026 13.6589C4.69328 11.3312 5.83617 9.19296 7.5144 7.51473C9.19263 5.8365 11.3308 4.69361 13.6586 4.23059C15.9864 3.76757 18.3992 4.00521 20.5919 4.91346C22.7846 5.82171 24.6587 7.35978 25.9773 9.33317C27.2959 11.3066 27.9997 13.6266 27.9997 16C27.9962 19.1815 26.7307 22.2317 24.4811 24.4814C22.2314 26.7311 19.1812 27.9965 15.9997 28Z" fill="#464255"/>
                                        <path d="M21.7648 11.684L14.7061 18.1546L11.6088 15.0573C11.4858 14.93 11.3387 14.8284 11.176 14.7585C11.0133 14.6886 10.8384 14.6518 10.6613 14.6503C10.4843 14.6488 10.3087 14.6825 10.1449 14.7495C9.98099 14.8166 9.83212 14.9156 9.70693 15.0408C9.58174 15.166 9.48274 15.3148 9.41569 15.4787C9.34865 15.6426 9.31492 15.8181 9.31646 15.9952C9.318 16.1722 9.35478 16.3472 9.42466 16.5098C9.49453 16.6725 9.59611 16.8196 9.72346 16.9426L13.7235 20.9426C13.9664 21.1857 14.2939 21.3255 14.6374 21.3329C14.981 21.3404 15.3142 21.2149 15.5675 20.9826L23.5675 13.6493C23.8281 13.4103 23.9831 13.0775 23.9983 12.7241C24.0136 12.3708 23.8878 12.0259 23.6488 11.7653C23.4097 11.5047 23.077 11.3497 22.7236 11.3344C22.3703 11.3192 22.0254 11.4449 21.7648 11.684Z" fill="#464255"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="geex-content__summary__count__single success-bg">
                                <div class="geex-content__summary__count__single__content">
                                    <h4 class="geex-content__summary__count__single__title">{{ $connectedThisWeek }}</h4>
                                    <p class="geex-content__summary__count__single__subtitle">Connectés cette semaine</p>
                                </div>
                                <div class="geex-content__summary__count__single__icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                                        <path d="M15.9997 1.33335C13.0989 1.33335 10.2632 2.19353 7.85132 3.80513C5.4394 5.41672 3.55953 7.70734 2.44945 10.3873C1.33936 13.0673 1.04891 16.0163 1.61483 18.8613C2.18075 21.7064 3.57761 24.3197 5.62878 26.3709C7.67995 28.4221 10.2933 29.8189 13.1384 30.3849C15.9834 30.9508 18.9324 30.6603 21.6124 29.5502C24.2924 28.4402 26.583 26.5603 28.1946 24.1484C29.8062 21.7365 30.6664 18.9008 30.6664 16C30.6618 12.1116 29.1151 8.38372 26.3655 5.63418C23.616 2.88464 19.8881 1.33793 15.9997 1.33335ZM15.9997 28C13.6263 28 11.3062 27.2962 9.33284 25.9776C7.35945 24.6591 5.82138 22.7849 4.91313 20.5922C4.00488 18.3995 3.76724 15.9867 4.23026 13.6589C4.69328 11.3312 5.83617 9.19296 7.5144 7.51473C9.19263 5.8365 11.3308 4.69361 13.6586 4.23059C15.9864 3.76757 18.3992 4.00521 20.5919 4.91346C22.7846 5.82171 24.6587 7.35978 25.9773 9.33317C27.2959 11.3066 27.9997 13.6266 27.9997 16C27.9962 19.1815 26.7307 22.2317 24.4811 24.4814C22.2314 26.7311 19.1812 27.9965 15.9997 28Z" fill="#464255"/>
                                        <path d="M21.7648 11.684L14.7061 18.1546L11.6088 15.0573C11.4858 14.93 11.3387 14.8284 11.176 14.7585C11.0133 14.6886 10.8384 14.6518 10.6613 14.6503C10.4843 14.6488 10.3087 14.6825 10.1449 14.7495C9.98099 14.8166 9.83212 14.9156 9.70693 15.0408C9.58174 15.166 9.48274 15.3148 9.41569 15.4787C9.34865 15.6426 9.31492 15.8181 9.31646 15.9952C9.318 16.1722 9.35478 16.3472 9.42466 16.5098C9.49453 16.6725 9.59611 16.8196 9.72346 16.9426L13.7235 20.9426C13.9664 21.1857 14.2939 21.3255 14.6374 21.3329C14.981 21.3404 15.3142 21.2149 15.5675 20.9826L23.5675 13.6493C23.8281 13.4103 23.9831 13.0775 23.9983 12.7241C24.0136 12.3708 23.8878 12.0259 23.6488 11.7653C23.4097 11.5047 23.077 11.3497 22.7236 11.3344C22.3703 11.3192 22.0254 11.4449 21.7648 11.684Z" fill="#464255"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="geex-content__wrapper" style="margin-top: 40px;">
                        <div class="geex-content__section-wrapper">
                            <div class="row">
                                <div class="col-lg-6 md-mb-40">
                                    <div class="geex-content__section geex-content__visitor-count">
                                        <div class="geex-content__section__header">
                                            <div class="geex-content__section__header__title-part">
                                                <h4 class="geex-content__section__header__title">Connexions Utilisateurs - 7 Derniers Jours</h4>
                                            </div>
                                        </div>
                                        <div class="geex-content__section__content">
                                            <div class="geex-content__visitor-count__number">
                                                <h2 class="geex-content__visitor-count__number__title">6</h2>
                                                <div class="geex-content__visitor-count__number__text">
                                                    <p class="geex-content__visitor-count__number__desc">total of the week</p>
                                                </div>
                                            </div>
                                            <div id="column-chart"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="geex-content__section geex-content__chat-summary">
                                        <div class="geex-content__section__header">
                                            <div class="geex-content__section__header__title-part">
                                                <h4 class="geex-content__section__header__title">Répartition des Types d'Appareils - 7 Derniers Jours</h4>
                                            </div>
                                        </div>
                                        <div class="geex-content__section__content">
                                            <div id="pie-chart-device-type"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div style="margin-top: 40px;display: flex;gap: 40px;">
                            <div class="geex-content__section geex-content__transaction table-responsive">
                                <div class="geex-content__section__header">
                                    <div class="geex-content__section__header__title-part">
                                        <h4 class="geex-content__section__header__title">Les dernières connexions enregistrées</h4>
                                    </div>
                                </div>
                                <div class="geex-content__section__content">
                                    <table class="table-reviews-geex-1">
                                        <thead>
                                            <tr style="width: 100%;">
                                                <th style="width: 20%;">Identifiant</th>
                                                <th style="width: 20%;">MAC Address</th>
                                                <th style="width: 20%;">Visites</th>
                                                <th style="width: 20%;">Dernier Passage</th>
                                                <th style="width: 20%">Action</th>

                                            </tr>
                                        </thead>
                                        <tbody class="">
                                        @foreach($latestConnections as $client)
                                            <tr>
                                                <td>
                                                    {{ $client->email }}
                                                </td>
                                                <td>
                                                    <span class="designation">{{ $client->mac_address  }}</span>
                                                </td>
                                                <td>
                                                    <span class="name">{{ $client->login_count  }}</span>
                                                </td>
                                                <td>
                                                    {{ $client->last_login_at ? $client->last_login_at : 'N/A' }}</td>
                                                <td style="text-align: center;">

                                                    
                                                    @if ($client->status === 'deactivated')
                                                        <span class="badge badge-danger">Banned</span>
                                                    @else
                                                        <button style="color:red" class="ban-user" data-user-id="{{ $client->id }}">
                                                            <i class="uil-ban"></i>
                                                        </button >
                                                    @endif

                                                </td>

                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="geex-content__section geex-content__transaction table-responsive">
                                <div class="geex-content__section__header">
                                    <div class="geex-content__section__header__title-part">
                                        <h4 class="geex-content__section__header__title">Démographie</h4>
                                    </div>
                                </div>
                                <div class="geex-content__section__content">
                                    <table class="table-reviews-geex-1">
                                        <thead>
                                            <tr style="width: 100%;">
                                                <th style="width: 50%;">Population</th>
                                                <th style="width: 50%;">Taux (%)</th>

                                            </tr>
                                        </thead>
                                        <tbody class="">
                                        @foreach($demographics as $demo)
                                            <tr>
                                                <td>
                                                    {{ $demo->language }}
                                                </td>
                                                <td>
                                                    <span class="designation">{{ number_format(($demo->total / $totalUsers) * 100, 2) }}%</span>
                                                </td>

                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>




                
         
@endsection

@section('scripts')

<script type="text/javascript">
        // Bar Chart
    let barOptions = {
        series: [{
            data: @json($data)
        }],
        chart: {
            height: 250,
            type: 'bar',
            toolbar: {
                show: false,
            },
        },
        colors: ["#AB54DB26"],
        plotOptions: {
        bar: {
            columnWidth: 50,
            borderRadius: 12,
        }
        },
        dataLabels: {
        enabled: false,
        },
        
        xaxis: {
            categories: @json($categories),
            position: 'bottom',
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            },
            crosshairs: {
                show: false,
            },
            tooltip: {
                enabled: false,
            }
        },
        yaxis: {
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false,
            },
            labels: {
                show: false,
            },
        },

        grid: {
            show: false,
            padding: { left: -20, right: -20, top: 0, bottom: 0 },
        },

        tooltip: {
            enabled: true,

            custom: function ({ series, seriesIndex, dataPointIndex, w }) {
                // Calculate the percentage based on the max value
                let value = w.globals.series[seriesIndex][dataPointIndex]
                var maxValue = Math.max(...series[0]);
                var percentage = ((value / maxValue) * 10).toFixed(0);

                return '<div class="custom-tooltip">' +
                '<span class="custom-tooltip__title">' + percentage + '%</span>' +
                '<span class="custom-tooltip__subtitle">' + value + ' Visitors</span>' +
                '</div>';
            },
        },

    };

    let barChartContainer = document.querySelector("#column-chart");
    let barChart = barChartContainer && new ApexCharts(barChartContainer, barOptions);
    barChart && barChart.render();

//pie chart

    let pieOptions = {
        series: @json($datadevice),  // Dynamic data from the controller
        labels: @json($labels),  // Dynamic labels (device types) from the controller
        colors: ["#AB54DB", "#EF9A91", "#F1E6B9","#ff5b5b"],
        plotOptions: {
            pie: {
                expandOnClick: false,
                dataLabels: {
                    enabled: false,
                },
            },
        },
        chart: {
            height: '350px',
            type: 'donut',
        },
        legend: {
            show: true,
            position: "bottom",
            fontSize: '14px',
            fontWeight: 500,
            formatter: function (seriesName, opts) {
                let data = opts.w.globals.seriesTotals[opts.seriesIndex];
                return seriesName + ":  " + data;
            },
        },
    };

    let pieChartContainer = document.querySelector("#pie-chart-device-type");
    let pieChart = pieChartContainer && new ApexCharts(pieChartContainer, pieOptions);
    pieChart && pieChart.render();

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$('.ban-user').on('click', function () {
    const userId = $(this).data('user-id'); // Get the user ID from the data attribute

    $.ajax({
        url: 'http://localhost/mywifi/public/ban-user',
        type: 'POST',
        data: {
            user_id: userId
        },
        success: function (response) {
            alert(response.message);
            location.reload(); // Reload the page to reflect changes
        },
        error: function (error) {
            alert('Failed to ban user.');
            console.error(error.responseJSON.message);
        }
    });
});



</script>
@endsection