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
                                <p class="small text-muted mt-2">{{ $formattedDatabaseTotals['downloaded'] }} total</p>
                                    </div>
                                </div>
                                </div>
                    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                        <div class="card border-0 bg-light h-100 metric-card">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Total Upload</h6>
                                <h2 class="mb-0 fw-bold text-success">{{ $totalTxRateFormatted }}</h2>
                                <p class="small text-muted mt-2">{{ $formattedDatabaseTotals['uploaded'] }} total</p>
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
                
                <!-- Total Bandwidth Usage from Database -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-center mb-3">Total Bandwidth Usage (Cumulative from Database)</h6>
                                <div class="d-flex justify-content-around">
                                    <div class="text-center">
                                        <h5 class="text-primary">Downloaded</h5>
                                        <h3>{{ $formattedDatabaseTotals['downloaded'] }}</h3>
                                    </div>
                                    <div class="text-center">
                                        <h5 class="text-success">Uploaded</h5>
                                        <h3>{{ $formattedDatabaseTotals['uploaded'] }}</h3>
                                    </div>
                                    <div class="text-center">
                                        <h5 class="text-info">Total</h5>
                                        <h3>{{ $formattedDatabaseTotals['total'] }}</h3>
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
        <!-- Total Cumulative Data Usage -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Total Cumulative Data Usage</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="card border-0 bg-light h-100 metric-card">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Total Downloaded</h6>
                                    <h2 class="mb-0 fw-bold text-primary">
                                        @if(!empty($statistics) && $statistics->count() > 0)
                                            {{ $statistics->first()->total_downloaded_formatted ?? '0 B' }}
                                        @else
                                            0 B
                                        @endif
                                    </h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="card border-0 bg-light h-100 metric-card">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Total Uploaded</h6>
                                    <h2 class="mb-0 fw-bold text-success">
                                        @if(!empty($statistics) && $statistics->count() > 0)
                                            {{ $statistics->first()->total_uploaded_formatted ?? '0 B' }}
                                        @else
                                            0 B
                                        @endif
                                    </h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-light h-100 metric-card">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Total Bandwidth Usage</h6>
                                    <h2 class="mb-0 fw-bold text-dark">
                                        @if(!empty($statistics) && $statistics->count() > 0)
                                            {{ $statistics->first()->total_bandwidth_formatted ?? '0 B' }}
                                        @else
                                            0 B
                                        @endif
                                    </h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
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
    // Ensure bytes is treated as a number
    bytes = Number(bytes);
    
    // Debug the incoming value
    console.log('Formatting bandwidth value:', bytes);
    
    // Handle zero value case explicitly
    if (bytes === 0 || isNaN(bytes)) {
        return '0 bps';
    } else if (bytes > 1000000) {
        return (bytes / 1000000).toFixed(2) + ' Mbps';
    } else if (bytes > 1000) {
        return (bytes / 1000).toFixed(2) + ' Kbps';
    } else {
        return Math.round(bytes) + ' bps';
    }
}

// Split chart initialization into phases for faster loading
function initializePrimaryCharts() {
    // Initialize only the gauge charts first - they're simple and lightweight
    const maxNetworkSpeed = 1000 * 1000000; // 1 Gbps in bps (changed from 100 Mbps)
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
            name: 'Downloaded',
            data: bandwidthUsage.map(user => user.total_downloaded_bytes)
        }, {
            name: 'Uploaded',
            data: bandwidthUsage.map(user => user.total_uploaded_bytes)
        }],
        chart: {
            height: 350,
            type: 'bar',
            toolbar: { show: false },
            fontFamily: 'inherit',
            animations: { enabled: false },
            stacked: false
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                horizontal: true,
                barHeight: '70%',
                distributed: false
            }
        },
        colors: ['#4361ee', '#2bcd72'],
        dataLabels: {
            enabled: true,
            formatter: function (val, opt) {
                // Return the formatted byte value
                if (opt.w.globals.labels[opt.dataPointIndex]) {
                    return bandwidthUsage[opt.dataPointIndex][opt.seriesIndex === 0 ? 'downloaded_formatted' : 'uploaded_formatted'];
                }
                return val;
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
                formatter: function (val, opt) {
                    // Return the formatted byte value
                    if (opt.w.globals.labels[opt.dataPointIndex]) {
                        return bandwidthUsage[opt.dataPointIndex][opt.seriesIndex === 0 ? 'downloaded_formatted' : 'uploaded_formatted'];
                    }
                    return val;
                }
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right'
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
    
    console.log('Toggle auto-refresh. Current state:', isAutoRefreshActive);
    
    if (isAutoRefreshActive) {
        // Stop auto-refresh
        console.log('Stopping auto-refresh');
        clearInterval(autoRefreshInterval);
        autoRefreshBtn.innerHTML = '<i class="uil uil-play"></i> Démarrer l\'actualisation automatique';
        autoRefreshBtn.classList.remove('btn-danger');
        autoRefreshBtn.classList.add('btn-outline-success');
        isAutoRefreshActive = false;
        showNotification('Actualisation automatique désactivée', 'info');
    } else {
        // Start auto-refresh (every 10 seconds)
        console.log('Starting auto-refresh every 10 seconds');
        
        // Immediate refresh
        refreshBandwidthData();
        
        autoRefreshInterval = setInterval(() => {
            console.log('Auto-refresh triggered at', new Date().toLocaleTimeString());
            refreshBandwidthData();
            
            // Update database every 5 refreshes (50 seconds)
            if (Math.floor(Date.now() / 10000) % 5 === 0) {
                updateBandwidthUsageInDatabase();
            }
        }, 10000);
        
        autoRefreshBtn.innerHTML = '<i class="uil uil-stop"></i> Arrêter l\'actualisation automatique';
        autoRefreshBtn.classList.remove('btn-outline-success');
        autoRefreshBtn.classList.add('btn-danger');
        isAutoRefreshActive = true;
        showNotification('Actualisation automatique activée (10 secondes)', 'success');
        
        // Update database when auto-refresh is started
        updateBandwidthUsageInDatabase();
    }
}

// Function to show a notification
function showNotification(message, type = 'success', duration = 3000) {
    // Create or get the single toast container
    let toastContainer = document.getElementById('toast-notification-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-notification-container';
        toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '1050';
        document.body.appendChild(toastContainer);
    }
    
    // Remove ALL existing toasts
    const existingToasts = toastContainer.querySelectorAll('.toast');
    existingToasts.forEach(toast => {
        if (toast && toast.classList.contains('show')) {
            const toastInstance = bootstrap.Toast.getInstance(toast);
            if (toastInstance) {
                toastInstance.hide();
            }
        }
        // Remove immediately to prevent stacking
        toast.remove();
    });
    
    // Create a unique ID for this toast
    const toastId = 'toast-' + Date.now();
    
    // Set the appropriate icon based on notification type
    let icon = 'uil-check-circle';
    let spinnerClass = '';
    
    if (type === 'danger') {
        icon = 'uil-exclamation-triangle';
    } else if (type === 'warning') {
        icon = 'uil-exclamation-circle';
    } else if (type === 'info') {
        icon = 'uil-info-circle';
        spinnerClass = 'spinner-border spinner-border-sm text-primary me-2';
    }
    
    // Create the toast element
    const toastElement = document.createElement('div');
    toastElement.id = toastId;
    toastElement.className = 'toast mb-2';
    toastElement.setAttribute('role', 'alert');
    toastElement.setAttribute('aria-live', 'assertive');
    toastElement.setAttribute('aria-atomic', 'true');
    
    // Set toast HTML content with a consistent header
    toastElement.innerHTML = `
        <div class="toast-header">
            <strong class="me-auto">Information</strong>
            <small class="text-muted">${new Date().toLocaleTimeString()}</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <div class="d-flex align-items-center">
                ${spinnerClass ? `<div class="${spinnerClass}" role="status"><span class="visually-hidden">Loading...</span></div>` : ''}
                <i class="uil ${icon} me-2 fs-5 ${type === 'success' ? 'text-success' : type === 'danger' ? 'text-danger' : type === 'warning' ? 'text-warning' : 'text-info'} ${!spinnerClass ? '' : 'd-none'}"></i>
                <span>${message}</span>
            </div>
        </div>
    `;
    
    // Add the toast to the container
    toastContainer.appendChild(toastElement);
    
    // Initialize and show the toast using Bootstrap's Toast API
    const toastInstance = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: duration
    });
    
    toastInstance.show();
    
    // Remove the toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        setTimeout(() => {
            toastElement.remove();
            
            // Remove the container if it's empty
            if (toastContainer.children.length === 0) {
                toastContainer.remove();
            }
        }, 150);
    });
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
    const timeoutId = setTimeout(() => controller.abort(), 8000); // 8 second timeout - increased from 5s for more reliable data
    
    // Debug timestamp to ensure we're not getting cached data
    const timestamp = new Date().getTime();
    console.log('Refreshing bandwidth data at:', new Date().toLocaleTimeString());
    
    // Check if we're in local development and should use mock data
    const useMockData = {{ app()->environment('local') ? 'true' : 'false' }};
    const url = '{{ route('bandwidth.data') }}' + 
                '?force_refresh=1' + 
                '&_=' + timestamp + 
                (useMockData ? '&use_mock_data=1' : '');
    
    // Always force refresh to bypass cache
    fetch(url, {
        signal: controller.signal,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Cache-Control': 'no-cache, no-store, must-revalidate', // Stronger cache prevention
            'Pragma': 'no-cache',
            'Expires': '0'
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
        // Debug the received data
        console.log('Received bandwidth data:', data);
        
        // Re-enable refresh button
        if (refreshBtn) {
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = '<i class="uil uil-sync"></i> Actualiser';
        }
        
        if (data.success) {
            // Display appropriate notifications based on data source
            if (data.fromCache) {
                showNotification('Utilisation des données en cache: ' + (data.cacheReason || 'Données récentes disponibles'), 'info', 3000);
            } else if (data.fromFallback) {
                showNotification('Utilisation des données de secours: ' + (data.fallbackReason || 'Problème de connexion au routeur'), 'warning', 5000);
                document.querySelector('h5.mb-0').innerHTML = 'Utilisation de la bande passante (DONNÉES DE SECOURS)';
            } else if (data.source === 'mock') {
                showNotification('Utilisation de données simulées pour le développement local', 'info', 3000);
                document.querySelector('h5.mb-0').innerHTML = 'Utilisation de la bande passante (DONNÉES SIMULÉES)';
            } else if (data.source === 'interface_fallback') {
                showNotification('Données directes des interfaces réseau', 'info', 3000);
                document.querySelector('h5.mb-0').innerHTML = 'Utilisation de la bande passante (INTERFACES RÉSEAU)';
            } else {
                // Make sure we're in normal mode, not demo mode
                document.querySelector('h5.mb-0').innerHTML = 'Utilisation de la bande passante en temps réel';
            }
            
            // Update the page header to show data source and timestamp
            const header = document.querySelector('h5.mb-0');
            if (header) {
                const lastUpdated = new Date(data.timestamp * 1000).toLocaleTimeString();
                let sourceText = '';
                
                if (data.source === 'mock') {
                    sourceText = ' (SIMULATION)';
                } else if (data.source === 'interface_fallback') {
                    sourceText = ' (INTERFACES)';
                } else if (data.fromCache) {
                    sourceText = ' (CACHE)';
                } else if (data.fromFallback) {
                    sourceText = ' (SECOURS)';
                }
                
                header.innerHTML = `Utilisation de la bande passante en temps réel${sourceText} <small class="text-muted">(Mise à jour: ${lastUpdated})</small>`;
            }
            
            // Update statistics cards without animation for speed
            const activeUsersElement = document.querySelectorAll('.card-body h2')[0];
            if (activeUsersElement) {
                activeUsersElement.textContent = data.activeUsers;
            }
            
            // Update the total download and upload metrics
            const metricCards = document.querySelectorAll('.metric-card');
            if (metricCards.length >= 4) {
                // Update Total Download (second card)
                const totalDownloadCard = metricCards[1];
                const totalDownloadValue = totalDownloadCard.querySelector('h2');
                if (totalDownloadValue) {
                    console.log('Setting download value to:', data.totalRx);
                    totalDownloadValue.innerHTML = data.totalRx || '0 bps';
                    if (data.totalRxBytes) {
                        const smallText = totalDownloadCard.querySelector('p.small');
                        if (smallText) {
                            smallText.textContent = data.totalRxBytes + ' total';
                        }
                    }
                }
                
                // Update Total Upload (third card)
                const totalUploadCard = metricCards[2];
                const totalUploadValue = totalUploadCard.querySelector('h2');
                if (totalUploadValue) {
                    console.log('Setting upload value to:', data.totalTx);
                    totalUploadValue.innerHTML = data.totalTx || '0 bps';
                    if (data.totalTxBytes) {
                        const smallText = totalUploadCard.querySelector('p.small');
                        if (smallText) {
                            smallText.textContent = data.totalTxBytes + ' total';
                        }
                    }
                }
                
                // Update Average per user (fourth card)
                const averageCard = metricCards[3];
                const averageContainer = averageCard.querySelector('.d-flex');
                if (averageContainer) {
                    const rxElement = averageContainer.querySelector('.text-primary');
                    const txElement = averageContainer.querySelector('.text-success');
                    
                    if (rxElement) rxElement.innerHTML = `<i class="uil uil-arrow-down"></i> ${data.averageRx || '0 bps'}`;
                    if (txElement) txElement.innerHTML = `<i class="uil uil-arrow-up"></i> ${data.averageTx || '0 bps'}`;
                } else {
                    // If the container doesn't exist, create it
                    const cardBody = averageCard.querySelector('.card-body');
                    if (cardBody) {
                        const header = cardBody.querySelector('h6');
                        if (header) {
                            const container = document.createElement('div');
                            container.className = 'd-flex justify-content-center align-items-center';
                            container.innerHTML = `
                                <div class="text-primary me-3">
                                    <i class="uil uil-arrow-down"></i> ${data.averageRx || '0 bps'}
                                </div>
                                <div class="text-success">
                                    <i class="uil uil-arrow-up"></i> ${data.averageTx || '0 bps'}
                                </div>
                            `;
                            
                            // Replace any existing content after the header
                            while (cardBody.childNodes.length > 1) {
                                cardBody.removeChild(cardBody.lastChild);
                            }
                            
                            cardBody.appendChild(container);
                        }
                    }
                }
            }
            
            // Update the database totals section
            const databaseTotalsSection = document.querySelector('.card-body .d-flex.justify-content-around');
            if (databaseTotalsSection && data.databaseTotals) {
                const totals = databaseTotalsSection.querySelectorAll('h3');
                if (totals.length >= 3) {
                    totals[0].textContent = data.databaseTotals.downloaded || totals[0].textContent;
                    totals[1].textContent = data.databaseTotals.uploaded || totals[1].textContent;
                    totals[2].textContent = data.databaseTotals.total || totals[2].textContent;
                }
            }
            
            // Update chart data - if chart exists and we have data
            if (window.realTimeBandwidthChart && data.labels && data.labels.length > 0) {
                // Check if we have valid numeric data
                const hasValidData = data.rxData && data.txData && 
                                   data.rxData.every(val => !isNaN(parseFloat(val))) && 
                                   data.txData.every(val => !isNaN(parseFloat(val)));
                
                if (hasValidData) {
                    // Update the chart
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
                } else {
                    console.warn('Invalid chart data received:', data.rxData, data.txData);
                    chartElement.innerHTML = '<div class="alert alert-warning text-center my-4">Données de bande passante invalides reçues du serveur</div>';
                }
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
            if (tableBody && data.connections) {
                tableBody.innerHTML = '';
                
                if (data.connections && data.connections.length > 0) {
                    data.connections.forEach(conn => {
                        tableBody.innerHTML += `
                            <tr>
                                <td>${conn.username}</td>
                                <td>${conn.ip_address}</td>
                                <td><span class="small text-muted">${conn.mac_address}</span></td>
                                <td>
                                    <span class="badge bg-primary rounded-pill">${conn.rx_rate}</span>
                                    ${conn.bytes_in_formatted ? `<small class="d-block text-muted mt-1">${conn.bytes_in_formatted}</small>` : ''}
                                </td>
                                <td>
                                    <span class="badge bg-success rounded-pill">${conn.tx_rate}</span>
                                    ${conn.bytes_out_formatted ? `<small class="d-block text-muted mt-1">${conn.bytes_out_formatted}</small>` : ''}
                                </td>
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
                // Make sure we have valid data
                const validRxData = data.rxData.filter(val => !isNaN(parseFloat(val)));
                const validTxData = data.txData.filter(val => !isNaN(parseFloat(val)));
                
                if (validRxData.length > 0 && validTxData.length > 0) {
                    const totalRxRateRaw = validRxData.reduce((sum, val) => sum + parseFloat(val), 0);
                    const totalTxRateRaw = validTxData.reduce((sum, val) => sum + parseFloat(val), 0);
                    const maxNetworkSpeed = 1000 * 1000000; // 1 Gbps in bps
                    
                    // Calculate percentage of network capacity
                    const downloadPercentage = Math.min(100, (totalRxRateRaw / maxNetworkSpeed) * 100);
                    const uploadPercentage = Math.min(100, (totalTxRateRaw / maxNetworkSpeed) * 100);
                    
                    // Update the gauge charts
                    window.downloadGaugeChart.updateSeries([downloadPercentage]);
                    window.uploadGaugeChart.updateSeries([uploadPercentage]);
                }
            }
            
            // Show success message only for live data
            if (!data.fromCache && !data.fromFallback && data.source !== 'mock') {
                showNotification(`Données en temps réel mises à jour à ${new Date().toLocaleTimeString()}`);
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

// Function to update bandwidth usage in database
function updateBandwidthUsageInDatabase() {
    // Show a subtle notification that we're updating the database
    showNotification('Mise à jour des données de bande passante dans la base de données...', 'info', 2000);
    
    // Fetch to update bandwidth usage in the database
    fetch('{{ route('update.bandwidth.usage') }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Cache-Control': 'no-cache'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Réponse réseau non valide: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('Données de bande passante mises à jour dans la base de données', 'success');
            
            // Refresh the page data to show updated totals
            setTimeout(() => {
                refreshBandwidthData();
            }, 1000);
        } else {
            throw new Error(data.message || 'Une erreur est survenue lors de la mise à jour des données');
        }
    })
    .catch(error => {
        console.error('Error updating bandwidth usage:', error);
        showNotification('Erreur lors de la mise à jour des données: ' + error.message, 'danger', 5000);
    });
}

// Add event listener to the auto-refresh button
document.addEventListener('DOMContentLoaded', function() {
    const autoRefreshBtn = document.getElementById('auto-refresh-btn');
    if (autoRefreshBtn) {
        autoRefreshBtn.addEventListener('click', toggleAutoRefresh);
    }
    
    // Add a dedicated button for database updates
    const refreshBtnContainer = document.querySelector('.d-flex');
    if (refreshBtnContainer) {
        const updateDbBtn = document.createElement('button');
        updateDbBtn.id = 'update-db-btn';
        updateDbBtn.className = 'btn btn-sm btn-outline-primary';
        updateDbBtn.innerHTML = '<i class="uil uil-database"></i> Mettre à jour la BD';
        updateDbBtn.addEventListener('click', updateBandwidthUsageInDatabase);
        refreshBtnContainer.appendChild(updateDbBtn);
    }
    
    // Set up automatic database updates every 30 seconds, independent of auto-refresh
    // This will run regardless of whether auto-refresh is enabled
    const dbUpdateInterval = 30000; // 30 seconds
    
    // Initial update when page loads
    setTimeout(() => {
        updateBandwidthUsageInDatabase();
        
        // Set up recurring updates
        setInterval(() => {
            console.log('Automatic database update triggered');
            updateBandwidthUsageInDatabase();
        }, dbUpdateInterval);
        
        // Show a notification that automatic updates are enabled
        showNotification('Mise à jour automatique de la base de données toutes les 30 secondes', 'info', 10000);
    }, 5000); // Wait 5 seconds after page load before starting
});
</script>
@endsection