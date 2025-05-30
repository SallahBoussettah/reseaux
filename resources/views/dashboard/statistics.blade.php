@extends('layouts.dashboard')


@section('title', 'Statistics')

@section('styles')
<style>
    /* Custom styles for statistics page */
    .card {
        border: none;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .card.shadow-sm:hover {
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.08) !important;
        transform: translateY(-3px);
    }
    
    .card-header {
        border-bottom: 1px solid rgba(0,0,0,.05);
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }
    
    .bg-light {
        background-color: rgba(245, 247, 251, 0.7) !important;
    }
    
    .table th {
        font-weight: 600;
        font-size: 0.875rem;
        border-top: none;
    }
    
    .badge {
        padding: 0.5em 0.85em;
        font-weight: 500;
    }
    
    .badge.bg-primary {
        background-color: rgba(67, 97, 238, 0.15) !important;
        color: #4361ee;
    }
    
    .badge.bg-success {
        background-color: rgba(43, 205, 114, 0.15) !important;
        color: #2bcd72;
    }
    
    .text-primary {
        color: #4361ee !important;
    }
    
    .text-success {
        color: #2bcd72 !important;
    }
    
    .btn-outline-primary {
        color: #4361ee;
        border-color: #4361ee;
    }
    
    .btn-outline-primary:hover {
        background-color: #4361ee;
        border-color: #4361ee;
    }
    
    .btn-outline-success {
        color: #2bcd72;
        border-color: #2bcd72;
    }
    
    .btn-outline-success:hover {
        background-color: #2bcd72;
        border-color: #2bcd72;
    }
    
    .btn-danger {
        background-color: #e7515a;
        border-color: #e7515a;
    }
    
    .btn-danger:hover {
        background-color: #d62c35;
        border-color: #d62c35;
    }
    
    /* Dashboard metrics cards */
    .metric-card h2 {
        font-size: 1.75rem;
        margin-bottom: 0;
    }
    
    .metric-card h6 {
        font-size: 0.8125rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .metric-card h2 {
            font-size: 1.5rem;
        }
        
        .metric-card h6 {
            font-size: 0.75rem;
        }
        
        .card-header {
            padding: 0.75rem 1rem;
        }
    }

    /* Additional styles for button pulse effect */
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(67, 97, 238, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(67, 97, 238, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(67, 97, 238, 0);
        }
    }

    .btn-pulse {
        animation: pulse 1.5s infinite;
    }
</style>
@endsection

@section('content')
            <div class="geex-content__header">
                <div class="geex-content__header__content">
                    <h2 class="geex-content__header__title">Statistique</h2>
        <p class="geex-content__header__subtitle">Total Monthly Active Users: {{ $statistics->first()->monthly_active_users ?? 0 }}</p>
                </div> 
                
                <div class="geex-content__header__action">
                    <div class="geex-content__header__action__wrap">
                        <ul class="geex-content__header__quickaction">
                            <li class="geex-content__header__quickaction__item">
                                <a href="#" class="geex-content__header__quickaction__link">
                        <img class="user-img" src="{{ asset('assets/img/avatar/user.svg') }}" alt="user" />
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
    </div> 
</div>

<div class="geex-content__wrapper">
    <!-- Real-time Network Bandwidth Usage Summary -->
    <div class="geex-content__section-wrapper mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0">Utilisation de la bande passante en temps réel</h5>
                <div class="d-flex">
                    <button id="refresh-btn" class="btn btn-sm btn-outline-primary me-2" onclick="refreshBandwidthData(); return false;">
                        <i class="uil uil-sync"></i> Actualiser
                    </button>
                    <button id="auto-refresh-btn" class="btn btn-sm btn-outline-success">
                        <i class="uil uil-play"></i> Démarrer l'actualisation automatique
                    </button>
                                    </div>
                                </div>
            <div class="card-body" id="bandwidth-content">
                <!-- Stats Cards Row -->
                <div class="row mb-4">
                    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                        <div class="card border-0 bg-light h-100 metric-card">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Utilisateurs actifs</h6>
                                <h2 class="mb-0 fw-bold">{{ count($activeConnections) }}</h2>
                    </div>
                </div> 
            </div>
                    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                        <div class="card border-0 bg-light h-100 metric-card">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Total Download</h6>
                                <h2 class="mb-0 fw-bold text-primary">{{ $totalRxRateFormatted }}</h2>
                                    </div>
                                </div>
                                </div>
                    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                        <div class="card border-0 bg-light h-100 metric-card">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Total Upload</h6>
                                <h2 class="mb-0 fw-bold text-success">{{ $totalTxRateFormatted }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card border-0 bg-light h-100 metric-card">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Moyenne par utilisateur</h6>
                                <div class="d-flex justify-content-center align-items-center">
                                    <div class="text-primary me-3">
                                        <i class="uil uil-arrow-down"></i> {{ $averageRxRate }}
                                    </div>
                                    <div class="text-success">
                                        <i class="uil uil-arrow-up"></i> {{ $averageTxRate }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Network load gauges -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-4 mb-md-0">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-center mb-3">Utilisation du réseau - Download</h6>
                                <div id="download-gauge-chart"></div>
                                    </div>
                                </div>
                                </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-center mb-3">Utilisation du réseau - Upload</h6>
                                <div id="upload-gauge-chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Real-time bandwidth usage chart -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3">Utilisation de la bande passante par utilisateur en temps réel</h6>
                        <div id="real-time-bandwidth-chart" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Active Users with Bandwidth Usage Table -->
    <div class="geex-content__section-wrapper mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Utilisateurs actifs avec utilisation de bande passante</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Adresse IP</th>
                                <th>Adresse MAC</th>
                                <th>Download (Rx)</th>
                                <th>Upload (Tx)</th>
                                <th>Temps de connexion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($activeConnections as $connection)
                            <tr>
                                <td>{{ $connection['username'] }}</td>
                                <td>{{ $connection['ip_address'] }}</td>
                                <td><span class="small text-muted">{{ $connection['mac_address'] }}</span></td>
                                <td><span class="badge bg-primary rounded-pill">{{ $connection['rx_rate'] }}</span></td>
                                <td><span class="badge bg-success rounded-pill">{{ $connection['tx_rate'] }}</span></td>
                                <td>{{ $connection['uptime'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">Aucun utilisateur actif trouvé</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Historical Data Section -->
    <div class="row mb-4">
        <!-- Daily Active Users Chart -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Utilisateurs actifs quotidiens (30 derniers jours)</h5>
                </div>
                <div class="card-body">
                    <div id="daily-active-users-chart"></div>
                </div>
            </div>
        </div>

        <!-- Bandwidth Usage per User Chart -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">Utilisation de la bande passante par utilisateur</h5>
                    <a href="#" class="btn btn-sm btn-outline-primary">Voir plus</a>
                </div>
                <div class="card-body">
                    <div id="bandwidth-usage-chart"></div>
                </div>
            </div>
        </div>
        
        <!-- User Activity by Hour Chart -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Activité des utilisateurs par heure de la journée</h5>
                </div>
                <div class="card-body">
                    <div id="user-activity-chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
@endsection

@section('scripts')
<!-- Defer non-critical scripts to improve page load speed -->
<script>
// Define base chart objects and data structures but don't initialize charts yet
let realTimeBandwidthData = {
    rxData: [
        @foreach($activeConnections as $connection)
            {{ $connection['rx_rate_raw'] ?? 0 }},
        @endforeach
    ],
    txData: [
        @foreach($activeConnections as $connection)
            {{ $connection['tx_rate_raw'] ?? 0 }},
        @endforeach
    ],
    labels: [
        @foreach($activeConnections as $connection)
            '{{ $connection['username'] ?? 'unknown' }}',
        @endforeach
    ]
};

// Store chart configurations but don't render yet
let realTimeBandwidthOptions = {
    series: [{
        name: 'Download (Rx)',
        data: realTimeBandwidthData.rxData
    }, {
        name: 'Upload (Tx)',
        data: realTimeBandwidthData.txData
    }],
    chart: {
        type: 'bar',
        height: 300,
        stacked: false,
        toolbar: {
            show: true
        },
        zoom: {
            enabled: true
        },
        fontFamily: 'inherit',
        animations: {
            enabled: false, // Disable animations initially for faster loading
        }
    },
    responsive: [{
        breakpoint: 480,
        options: {
            legend: {
                position: 'bottom',
                offsetX: -10,
                offsetY: 0
            }
        }
    }],
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '55%',
            borderRadius: 2,
            dataLabels: {
                position: 'top'
            }
        },
    },
    xaxis: {
        categories: realTimeBandwidthData.labels,
        labels: {
            style: {
                fontSize: '12px'
            }
        }
    },
    legend: {
        position: 'top',
        horizontalAlign: 'right',
        fontSize: '14px'
    },
    fill: {
        opacity: 1
    },
    colors: ['#4361ee', '#2bcd72'],
    tooltip: {
        y: {
            formatter: function (val) {
                return formatBandwidth(val);
            }
        }
    }
};

// Load crucial functionality first - only the bare minimum needed for user interaction
document.addEventListener('DOMContentLoaded', function() {
    // Initialize UI event handlers immediately
    const autoRefreshBtn = document.getElementById('auto-refresh-btn');
    const refreshBtn = document.getElementById('refresh-btn');
    
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function(e) {
            e.preventDefault();
            refreshBandwidthData();
        });
    }
    
    if (autoRefreshBtn) {
        autoRefreshBtn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleAutoRefresh();
        });
    }
    
    // Progressively load content - Critical content first, then everything else with delays
    
    // Phase 1: Load the critical section first (the active users table and cards)
    setTimeout(() => {
        // Show initial notification that page is still loading
        showNotification('Chargement des données...', 'info');
        
        // Phase 2: After 100ms, load the primary charts (gauges, bandwidth)
        setTimeout(() => {
            initializePrimaryCharts();
            
            // Phase 3: After another 500ms, load secondary charts
            setTimeout(() => {
                initializeSecondaryCharts();
                
                // Phase 4: After the UI is ready, attempt to refresh data
                setTimeout(() => {
                    refreshBandwidthData();
                }, 1000);
            }, 500);
        }, 100);
    }, 0);
});

// Format bandwidth for tooltips
function formatBandwidth(bytes) {
    if (bytes > 1000000) {
        return (bytes / 1000000).toFixed(2) + ' Mbps';
    } else if (bytes > 1000) {
        return (bytes / 1000).toFixed(2) + ' Kbps';
    } else {
        return bytes + ' bps';
    }
}

// Split chart initialization into phases for faster loading
function initializePrimaryCharts() {
    // Initialize only the gauge charts first - they're simple and lightweight
    const maxNetworkSpeed = 100 * 1000000; // 100 Mbps in bps
    const totalRxRateRaw = {{ array_sum(array_column($activeConnections, 'rx_rate_raw') ?: [0]) }};
    const totalTxRateRaw = {{ array_sum(array_column($activeConnections, 'tx_rate_raw') ?: [0]) }};
    
    let downloadGaugeOptions = {
        series: [Math.min(100, (totalRxRateRaw / maxNetworkSpeed) * 100)],
        chart: {
            type: 'radialBar',
            height: 250,
            fontFamily: 'inherit',
            animations: { enabled: false }
        },
        plotOptions: {
            radialBar: {
                startAngle: -90,
                endAngle: 90,
                hollow: { margin: 0, size: '70%' },
                track: {
                    background: '#e7e7e7',
                    strokeWidth: '97%',
                    margin: 5,
                },
                dataLabels: {
                    name: {
                        show: true,
                        fontSize: '14px',
                        fontWeight: 600,
                        offsetY: -10,
                        color: '#4361ee'
                    },
                    value: {
                        show: true,
                        fontSize: '22px',
                        fontWeight: 'bold',
                        color: '#4361ee',
                        formatter: function(val) { return Math.round(val) + '%'; }
                    }
                }
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'horizontal',
                shadeIntensity: 0.5,
                gradientToColors: ['#4361ee'],
                inverseColors: true,
                opacityFrom: 1,
                opacityTo: 1,
                stops: [0, 100]
            }
        },
        stroke: { lineCap: 'round' },
        labels: ['Download']
    };

    let uploadGaugeOptions = {
        series: [Math.min(100, (totalTxRateRaw / maxNetworkSpeed) * 100)],
        chart: {
            type: 'radialBar',
            height: 250,
            fontFamily: 'inherit',
            animations: { enabled: false }
        },
        plotOptions: {
            radialBar: {
                startAngle: -90,
                endAngle: 90,
                hollow: { margin: 0, size: '70%' },
                track: {
                    background: '#e7e7e7',
                    strokeWidth: '97%',
                    margin: 5,
                },
                dataLabels: {
                    name: {
                        show: true,
                        fontSize: '14px',
                        fontWeight: 600,
                        offsetY: -10,
                        color: '#2bcd72'
                    },
                    value: {
                        show: true,
                        fontSize: '22px',
                        fontWeight: 'bold',
                        color: '#2bcd72',
                        formatter: function(val) { return Math.round(val) + '%'; }
                    }
                }
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'horizontal',
                shadeIntensity: 0.5,
                gradientToColors: ['#2bcd72'],
                inverseColors: true,
                opacityFrom: 1,
                opacityTo: 1,
                stops: [0, 100]
            }
        },
        stroke: { lineCap: 'round' },
        labels: ['Upload']
    };

    window.downloadGaugeChart = new ApexCharts(document.querySelector("#download-gauge-chart"), downloadGaugeOptions);
    window.uploadGaugeChart = new ApexCharts(document.querySelector("#upload-gauge-chart"), uploadGaugeOptions);
    
    downloadGaugeChart.render();
    uploadGaugeChart.render();
    
    // Create the real-time bandwidth chart if we have data
    if (realTimeBandwidthData.labels.length > 0) {
        window.realTimeBandwidthChart = new ApexCharts(document.querySelector("#real-time-bandwidth-chart"), realTimeBandwidthOptions);
        realTimeBandwidthChart.render();
    } else {
        document.getElementById('real-time-bandwidth-chart').innerHTML = '<div class="alert alert-info text-center my-4">Aucune connexion active pour afficher des données en temps réel</div>';
    }
}

function initializeSecondaryCharts() {
    // Daily Active Users Chart - the most complex chart with 30 days of data
var dailyActiveUsers = @json($statistics->pluck('daily_active_users'));

// Get the last 30 days in date format
var last30Days = [...Array(30).keys()].map(i => {
    let date = new Date();
        date.setDate(date.getDate() - (29 - i));
    return date;
});

// Map days to French day names
    var dayNamesInFrench = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
    
    // Map last 30 days to day names in French with dates
    var frenchDayLabels = last30Days.map(date => {
        return dayNamesInFrench[date.getDay()] + ' ' + date.getDate() + '/' + (date.getMonth() + 1);
    });

let dailyOptions = {
    series: [{
            name: 'Utilisateurs actifs',
        data: dailyActiveUsers
    }],
    chart: {
        height: 350,
            type: 'area',
            toolbar: { show: false },
            fontFamily: 'inherit',
            animations: { enabled: false }
        },
        dataLabels: { enabled: false },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        colors: ['#4361ee'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.2,
                stops: [0, 90, 100]
            }
    },
    xaxis: {
            categories: frenchDayLabels,
            labels: {
                rotate: -45,
                style: { fontSize: '12px' }
            }
    },
    yaxis: {
            title: { text: 'Nombre d\'utilisateurs' }
        },
        markers: {
            size: 4,
            strokeWidth: 0,
            hover: { size: 6 }
        },
        tooltip: {
            theme: 'light',
            y: {
                formatter: function (val) {
                    return val + ' utilisateurs';
                }
            }
        }
    };
    
    var bandwidthUsage = @json($bandwidthUsagePerUser);

    let bandwidthOptions = {
        series: [{
            name: 'Utilisation de bande passante',
            data: bandwidthUsage.map(user => user.data_usage)
        }],
        chart: {
            height: 350,
            type: 'bar',
            toolbar: { show: false },
            fontFamily: 'inherit',
            animations: { enabled: false }
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                horizontal: true,
                barHeight: '70%',
                distributed: false
            }
        },
        colors: ['#2bcd72'],
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return val + ' MB';
            },
            style: { fontSize: '12px' }
        },
        xaxis: {
            categories: bandwidthUsage.map(user => user.full_name),
            labels: {
                style: { fontSize: '12px' }
            }
        },
        tooltip: {
            theme: 'light',
            y: {
                formatter: function (val) {
                    return val + ' MB';
                }
            }
        }
    };
    
    // User activity by hour chart
    var userActivity = [];
    @if(!empty($statistics) && $statistics->count() > 0 && isset($statistics->first()->users_by_hour))
        userActivity = Object.values(@json($statistics->first()->users_by_hour));
    @endif

    let activityOptions = {
        series: [{
            name: 'Activité utilisateurs',
            data: userActivity
        }],
        chart: {
            height: 350,
            type: 'bar',
            toolbar: { show: false },
            fontFamily: 'inherit',
            animations: { enabled: false }
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                columnWidth: '60%',
                distributed: false
            }
        },
        colors: ['#4361ee'],
        dataLabels: { enabled: false },
        xaxis: {
            categories: [...Array(24).keys()].map(hour => hour + 'h'),
            title: { text: 'Heure de la journée' },
            labels: {
                style: { fontSize: '12px' }
            }
        },
        yaxis: {
            title: { text: 'Nombre d\'utilisateurs' }
        },
        tooltip: {
            theme: 'light',
            y: {
                formatter: function (val) {
                    return val + ' utilisateurs';
                }
            }
        }
    };
    
    // Create the charts in the background
    setTimeout(() => {
        let dailyChart = new ApexCharts(document.querySelector("#daily-active-users-chart"), dailyOptions);
        dailyChart.render();
    }, 0);
    
    setTimeout(() => {
        let bandwidthChart = new ApexCharts(document.querySelector("#bandwidth-usage-chart"), bandwidthOptions);
        bandwidthChart.render();
    }, 200);
    
    setTimeout(() => {
    let activityChart = new ApexCharts(document.querySelector("#user-activity-chart"), activityOptions);
    activityChart.render();
        
        // Enable animations now that all charts are loaded
        enableAllChartAnimations();
    }, 400);
}

// Function to enable animations once everything is loaded
function enableAllChartAnimations() {
    if (window.realTimeBandwidthChart) {
        window.realTimeBandwidthChart.updateOptions({
            chart: { animations: { enabled: true } }
        });
    }
    
    if (window.downloadGaugeChart) {
        window.downloadGaugeChart.updateOptions({
            chart: { animations: { enabled: true } }
        });
    }
    
    if (window.uploadGaugeChart) {
        window.uploadGaugeChart.updateOptions({
            chart: { animations: { enabled: true } }
        });
    }
}

// Auto-refresh functionality
let autoRefreshInterval;
let isAutoRefreshActive = false;

// Function to toggle auto-refresh
function toggleAutoRefresh() {
    const autoRefreshBtn = document.getElementById('auto-refresh-btn');
    
    if (isAutoRefreshActive) {
        // Stop auto-refresh
        clearInterval(autoRefreshInterval);
        autoRefreshBtn.innerHTML = '<i class="uil uil-play"></i> Démarrer l\'actualisation automatique';
        autoRefreshBtn.classList.remove('btn-danger');
        autoRefreshBtn.classList.add('btn-outline-success');
        isAutoRefreshActive = false;
        showNotification('Actualisation automatique désactivée', 'info');
    } else {
        // Start auto-refresh (every 10 seconds)
        autoRefreshInterval = setInterval(refreshBandwidthData, 10000);
        autoRefreshBtn.innerHTML = '<i class="uil uil-stop"></i> Arrêter l\'actualisation automatique';
        autoRefreshBtn.classList.remove('btn-outline-success');
        autoRefreshBtn.classList.add('btn-danger');
        isAutoRefreshActive = true;
        showNotification('Actualisation automatique activée (10 secondes)', 'success');
        
        // Refresh immediately when auto-refresh is started
        refreshBandwidthData();
    }
}

// Function to show a notification
function showNotification(message, type = 'success', duration = 3000) {
    const alertElement = document.createElement('div');
    alertElement.classList.add('alert', `alert-${type}`, 'alert-dismissible', 'fade', 'show', 'mt-3');
    
    let icon = 'uil-check-circle';
    if (type === 'danger') {
        icon = 'uil-exclamation-triangle';
    } else if (type === 'warning') {
        icon = 'uil-exclamation-circle';
    } else if (type === 'info') {
        icon = 'uil-info-circle';
    }
    
    alertElement.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="uil ${icon} me-2 fs-5"></i>
            <div>${message}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    const container = document.getElementById('bandwidth-content');
    if (container) {
        container.prepend(alertElement);
        
        // Auto-dismiss the alert
        setTimeout(() => {
            alertElement.classList.remove('show');
            setTimeout(() => alertElement.remove(), 150);
        }, duration);
    }
}

// Function to refresh bandwidth data via AJAX
function refreshBandwidthData() {
    // Show loading indicator
    const chartElement = document.getElementById('real-time-bandwidth-chart');
    if (!chartElement) return; // Prevent errors if element doesn't exist yet
    
    chartElement.innerHTML = '<div class="d-flex justify-content-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    // Disable refresh button while loading
    const refreshBtn = document.getElementById('refresh-btn');
    if (refreshBtn) {
        refreshBtn.disabled = true;
        refreshBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Chargement...';
    }
    
    // Create a controller to be able to abort the fetch request if it takes too long
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 5000); // 5 second timeout - reduced from 10s
    
    // Fetch updated data from server with force_refresh=1 to bypass cache if needed
    fetch('{{ route('bandwidth.data') }}' + (isAutoRefreshActive ? '' : '?force_refresh=1'), {
        signal: controller.signal,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Cache-Control': 'no-cache' // Prevent browser caching
        }
    })
    .then(response => {
        clearTimeout(timeoutId);
        if (!response.ok) {
            throw new Error('Réponse réseau non valide: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        // Re-enable refresh button
        if (refreshBtn) {
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = '<i class="uil uil-sync"></i> Actualiser';
        }
        
        if (data.success) {
            // Display appropriate notifications based on data source
            if (data.fromCache) {
                showNotification('Utilisation des données en cache: ' + data.cacheReason, 'info', 5000);
            } else {
                // Make sure we're in normal mode, not demo mode
                document.querySelector('h5.mb-0').innerHTML = 'Utilisation de la bande passante en temps réel';
            }
            
            // Update statistics cards without animation for speed
            const activeUsersElement = document.querySelectorAll('.card-body h2')[0];
            if (activeUsersElement) {
                activeUsersElement.textContent = data.activeUsers;
            }
            
            // Update the other metrics
            const elements = document.querySelectorAll('.card-body h2');
            if (elements.length > 2) {
                elements[1].textContent = data.totalRx;
                elements[2].textContent = data.totalTx;
            }
            
            const rxElement = document.querySelectorAll('.card-body .text-primary')[0];
            const txElement = document.querySelectorAll('.card-body .text-success')[0];
            if (rxElement) rxElement.innerHTML = `<i class="uil uil-arrow-down"></i> ${data.averageRx}`;
            if (txElement) txElement.innerHTML = `<i class="uil uil-arrow-up"></i> ${data.averageTx}`;
            
            // Update chart data - if chart exists and we have data
            if (window.realTimeBandwidthChart && data.labels && data.labels.length > 0) {
                realTimeBandwidthChart.updateOptions({
                    xaxis: {
                        categories: data.labels
                    }
                });
                
                realTimeBandwidthChart.updateSeries([
                    {
                        name: 'Download (Rx)',
                        data: data.rxData
                    },
                    {
                        name: 'Upload (Tx)',
                        data: data.txData
                    }
                ]);
            } else if (chartElement && data.labels && data.labels.length > 0) {
                // If chart doesn't exist yet but we have data, create it
                realTimeBandwidthData = {
                    rxData: data.rxData,
                    txData: data.txData,
                    labels: data.labels
                };
                
                realTimeBandwidthOptions.series[0].data = data.rxData;
                realTimeBandwidthOptions.series[1].data = data.txData;
                realTimeBandwidthOptions.xaxis.categories = data.labels;
                
                window.realTimeBandwidthChart = new ApexCharts(document.querySelector("#real-time-bandwidth-chart"), realTimeBandwidthOptions);
                window.realTimeBandwidthChart.render();
            } else if (chartElement) {
                // No active connections
                chartElement.innerHTML = '<div class="alert alert-info text-center my-4">Aucune connexion active pour afficher des données en temps réel</div>';
            }
            
            // Update table
            const tableBody = document.querySelector('table tbody');
            if (tableBody) {
                tableBody.innerHTML = '';
                
                if (data.connections && data.connections.length > 0) {
                    data.connections.forEach(conn => {
                        tableBody.innerHTML += `
                            <tr>
                                <td>${conn.username}</td>
                                <td>${conn.ip_address}</td>
                                <td><span class="small text-muted">${conn.mac_address}</span></td>
                                <td><span class="badge bg-primary rounded-pill">${conn.rx_rate}</span></td>
                                <td><span class="badge bg-success rounded-pill">${conn.tx_rate}</span></td>
                                <td>${conn.uptime}</td>
                            </tr>
                        `;
                    });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4">Aucun utilisateur actif trouvé</td></tr>';
                }
            }
            
            // Calculate total bandwidth for gauge charts
            if (data.rxData && data.txData && window.downloadGaugeChart && window.uploadGaugeChart) {
                const totalRxRateRaw = data.rxData.reduce((sum, val) => sum + val, 0);
                const totalTxRateRaw = data.txData.reduce((sum, val) => sum + val, 0);
                const maxNetworkSpeed = 100 * 1000000; // 100 Mbps in bps
                
                // Calculate percentage of network capacity
                const downloadPercentage = Math.min(100, (totalRxRateRaw / maxNetworkSpeed) * 100);
                const uploadPercentage = Math.min(100, (totalTxRateRaw / maxNetworkSpeed) * 100);
                
                // Update the gauge charts
                window.downloadGaugeChart.updateSeries([downloadPercentage]);
                window.uploadGaugeChart.updateSeries([uploadPercentage]);
            }
            
            // Show success message only for live data
            if (!data.fromCache) {
                showNotification(`Données mises à jour à ${new Date().toLocaleTimeString()}`);
            }
        } else {
            throw new Error(data.message || 'Une erreur inconnue est survenue');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        if (refreshBtn) {
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = '<i class="uil uil-sync"></i> Actualiser';
        }
        
        // Provide more specific error message
        let errorMessage = 'Erreur lors de la récupération des données';
        if (error.name === 'AbortError') {
            errorMessage = 'La requête a pris trop de temps et a été interrompue';
        } else if (error.message) {
            errorMessage += ': ' + error.message;
        }
        
        if (chartElement) {
            chartElement.innerHTML = `<div class="alert alert-danger text-center my-4">${errorMessage}</div>`;
        }
        
        showNotification(errorMessage, 'danger');
        
        // Implement a retry mechanism after 30 seconds if auto-refresh is active
        if (isAutoRefreshActive) {
            showNotification('Nouvelle tentative prévue dans 30 secondes...', 'info');
            setTimeout(refreshBandwidthData, 30000);
        }
    });
}

// Animated counter for numeric values - extremely simplified for performance
function animateValue(obj, start, end, duration) {
    // Just set the value directly for speed
    if (obj) obj.textContent = end;
}
</script>
@endsection