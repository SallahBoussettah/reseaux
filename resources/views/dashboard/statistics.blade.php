@extends('layouts.dashboard')

@section('title', 'Statistics')

@section('content')

            <div class="geex-content__header">
                <div class="geex-content__header__content">
                    <h2 class="geex-content__header__title">Statistique</h2>
                    <p class="geex-content__header__subtitle">Total Monthly Active Users: {{ $statistics->first()->monthly_active_users }}</p>
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
                                            <h3 class="geex-content__header__popup__header__title">Mahabub Alam</h3>
                                            <span class="geex-content__header__popup__header__subtitle">CEO, ThemeWant</span>
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
                                            <li class="geex-content__header__popup__item">
                                                <a class="geex-content__header__popup__link" href="#">
                                                    <i class="uil uil-dollar-alt"></i>
                                                    Billing
                                                </a>
                                            </li>
                                            <li class="geex-content__header__popup__item">
                                                <a class="geex-content__header__popup__link" href="#">
                                                    <i class="uil uil-users-alt"></i>
                                                    Activity
                                                </a>
                                            </li>
                                            <li class="geex-content__header__popup__item">
                                                <a class="geex-content__header__popup__link" href="#">
                                                    <i class="uil uil-bell"></i>
                                                    Help
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="geex-content__header__popup__footer">
                                        <a href="#" class="geex-content__header__popup__footer__link">
                                            <i class="uil uil-arrow-up-left"></i>Logout
                                        </a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div> 
            </div>

<!-- Add more chart containers as needed -->

            <div class="geex-content__wrapper">
                <div class="geex-content__section-wrapper">
                    <div class="row">
                        <div class="col-lg-12 mb-40">
                            <div class="geex-content__section geex-content__server-request">
                                <div class="geex-content__section__header">
                                    <div class="geex-content__section__header__title-part">
                                        <h4 class="geex-content__section__header__title">Utilisateurs actifs quotidiens (30 derniers jours)</h4>
                                    </div>
                                </div>
                                <div class="geex-content__section__content">
                                     <div id="daily-active-users-chart"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 md-mb-40">
                            <div class="geex-content__section geex-content__visitor-count">
                                <div class="geex-content__section__header">
                                    <div class="geex-content__section__header__title-part">
                                        <h4 class="geex-content__section__header__title">Utilisation de la bande passante par utilisateur</h4>
                                    </div>
                                    <div class="geex-content__section__header__content-part">
                                        <div class="geex-content__section__header__btn">
                                            <a href="#" class="geex-content__section__header__link">
                                            View More
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="geex-content__section__content">

                                    <div id="bandwidth-usage-chart"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="geex-content__section geex-content__chat-summary">
                                <div class="geex-content__section__header">
                                    <div class="geex-content__section__header__title-part">
                                        <h4 class="geex-content__section__header__title">Activité des utilisateurs par heure de la journée</h4>
                                    </div>
                                </div>
                                <div class="geex-content__section__content">
                                    <div id="user-activity-chart"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
@endsection

@section('scripts')
<script>

// Assuming your statistics already contain 30 days of data -- chart 1
var dailyActiveUsers = @json($statistics->pluck('daily_active_users'));

// Get the last 30 days in date format
var last30Days = [...Array(30).keys()].map(i => {
    let date = new Date();
    date.setDate(date.getDate() - (29 - i)); // Adjust for past days
    return date;
});

// Map days to French day names
var dayNamesInFrench = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];

// Map last 30 days to day names in French
var frenchDayLabels = last30Days.map(date => dayNamesInFrench[date.getDay()]);

let dailyOptions = {
    series: [{
        data: dailyActiveUsers
    }],
    chart: {
        height: 350,
        type: 'line',
        toolbar: { show: false }
    },
    xaxis: {
        categories: frenchDayLabels, // Use French day names
        title: { text: 'Derniers 30 jours' }
    },
    yaxis: {
        title: { text: 'Utilisateurs actifs' }
    }
};
let dailyChart = new ApexCharts(document.querySelector("#daily-active-users-chart"), dailyOptions);
dailyChart.render();


// -- chart 2
    var bandwidthUsage = @json($bandwidthUsagePerUser);

    let bandwidthOptions = {
        series: [{
            name: 'Bandwidth Usage',
            data: bandwidthUsage.map(user => user.data_usage)
        }],
        chart: {
            height: 350,
            type: 'bar'
        },
        xaxis: {
            categories: bandwidthUsage.map(user => user.full_name),
            title: { text: 'Users' }
        },
        yaxis: {
            title: { text: 'Bandwidth (MB)' }
        }
    };
    let bandwidthChart = new ApexCharts(document.querySelector("#bandwidth-usage-chart"), bandwidthOptions);
    bandwidthChart.render();

//-- chart 3
    var userActivity = @json($statistics->pluck('users_by_hour'));

    let activityOptions = {
        series: [{
            name: 'User Activity',
            data: userActivity
        }],
        chart: {
            height: 350,
            type: 'bar'
        },
        xaxis: {
            categories: [...Array(24).keys()], // 24 hours of the day
            title: { text: 'Hour of Day' }
        },
        yaxis: {
            title: { text: 'Number of Users' }
        }
    };
    let activityChart = new ApexCharts(document.querySelector("#user-activity-chart"), activityOptions);
    activityChart.render();
</script>




@endsection