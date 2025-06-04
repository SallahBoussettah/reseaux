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
                    <div class="dropdown me-2">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="refreshRateDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="uil uil-clock"></i> <span id="current-refresh-rate">1s</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="refreshRateDropdown">
                            <li><a class="dropdown-item refresh-rate" href="#" data-rate="1">1 seconde</a></li>
                            <li><a class="dropdown-item refresh-rate" href="#" data-rate="5">5 secondes</a></li>
                            <li><a class="dropdown-item refresh-rate" href="#" data-rate="10">10 secondes</a></li>
                            <li><a class="dropdown-item refresh-rate active" href="#" data-rate="30">30 secondes</a></li>
                            <li><a class="dropdown-item refresh-rate" href="#" data-rate="60">1 minute</a></li>
                            <li><a class="dropdown-item refresh-rate" href="#" data-rate="300">5 minutes</a></li>
                        </ul>
                    </div>
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
                                                {{ $formattedDatabaseTotals['downloaded'] }}
                                            </h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="card border-0 bg-light h-100 metric-card">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-2">Total Uploaded</h6>
                                            <h2 class="mb-0 fw-bold text-success">
                                                {{ $formattedDatabaseTotals['uploaded'] }}
                                            </h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-0 bg-light h-100 metric-card">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-2">Total Bandwidth Usage</h6>
                                            <h2 class="mb-0 fw-bold text-dark">
                                                {{ $formattedDatabaseTotals['total'] }}
                                            </h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Network load gauges -->
                <div class="row mb-4">
                    <div class="col-12 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-end align-items-center">
                                    <label for="networkCapacity" class="me-2 mb-0">Capacité maximale:</label>
                                    <select id="networkCapacity" class="form-select form-select-sm" style="width: auto;">
                                        <option value="50">50 Mbps</option>
                                        <option value="100" selected>100 Mbps</option>
                                        <option value="200">200 Mbps</option>
                                        <option value="500">500 Mbps</option>
                                        <option value="1000">1 Gbps</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
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
            </div>
        </div>
    </div>
    
    <!-- Active Users with Bandwidth Usage Table -->
    <div class="geex-content__section-wrapper mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Utilisateurs actifs avec utilisation de bande passante</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Adresse IP</th>
                                <th>Adresse MAC</th>
                                <th>Download</th>
                                <th>Upload</th>
                                <th>Temps de connexion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($activeConnections as $connection)
                            <tr>
                                <td>{{ $connection['username'] }}</td>
                                <td>{{ $connection['ip_address'] }}</td>
                                <td><span class="small text-muted">{{ $connection['mac_address'] }}</span></td>
                                <td>
                                    <div class="d-flex flex-column">
                                        @if((int)$connection['tx_rate_raw'] > 0)
                                            <span class="badge bg-success rounded-pill mb-1">{{ $connection['tx_rate'] }}</span>
                                        @endif
                                        <span class="text-success">{{ $connection['bytes_out_formatted'] }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <!-- @if((int)$connection['rx_rate_raw'] > 0)
                                            <span class="badge bg-primary rounded-pill mb-1">{{ $connection['rx_rate'] }}</span>
                                        @endif -->
                                        <span class="text-primary">{{ $connection['bytes_in_formatted'] }}</span>
                                    </div>
                                </td>
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
<!-- Required libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.3/dragula.min.js"></script>
<!-- Defer non-critical scripts to improve page load speed -->
<script>
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
    
    // Set up network capacity dropdown
    const networkCapacitySelect = document.getElementById('networkCapacity');
    if (networkCapacitySelect) {
        // Set initial value from localStorage if available
        const savedCapacity = localStorage.getItem('networkCapacity');
        if (savedCapacity) {
            networkCapacitySelect.value = savedCapacity;
        }
        
        networkCapacitySelect.addEventListener('change', function() {
            // Get the new capacity value
            const capacityMbps = parseInt(this.value, 10);
            
            // Store the value in localStorage for persistence
            localStorage.setItem('networkCapacity', capacityMbps);
            
            // Force refresh the data to update the gauge charts
            refreshBandwidthData();
            
            // Show notification
            showNotification(`Capacité maximale mise à jour à ${capacityMbps} Mbps`, 'info');
        });
    }
    
    // Set up initial refresh rate from localStorage if available
    const savedRefreshRate = localStorage.getItem('refreshRate');
    if (savedRefreshRate) {
        refreshRate = parseInt(savedRefreshRate, 10);
        document.getElementById('current-refresh-rate').textContent = refreshRate + 's';
        
        // Update active class in dropdown
        const refreshRateItems = document.querySelectorAll('.refresh-rate');
        refreshRateItems.forEach(ri => ri.classList.remove('active'));
        const savedOption = document.querySelector(`.refresh-rate[data-rate="${refreshRate}"]`);
        if (savedOption) savedOption.classList.add('active');
    } else {
        // Set default to 1s
        document.getElementById('current-refresh-rate').textContent = '1s';
        
        // Update active class in dropdown
        const refreshRateItems = document.querySelectorAll('.refresh-rate');
        refreshRateItems.forEach(ri => ri.classList.remove('active'));
        const oneSecondOption = document.querySelector('.refresh-rate[data-rate="1"]');
        if (oneSecondOption) oneSecondOption.classList.add('active');
    }
    
    // Add event listeners for refresh rate dropdown items
    const refreshRateItems = document.querySelectorAll('.refresh-rate');
    if (refreshRateItems.length > 0) {
        refreshRateItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Get the new refresh rate
                const newRate = parseInt(this.getAttribute('data-rate'), 10);
                refreshRate = newRate;
                
                // Store the value in localStorage for persistence
                localStorage.setItem('refreshRate', newRate);
                
                // Update the displayed rate
                document.getElementById('current-refresh-rate').textContent = newRate + 's';
                
                // Update active class
                refreshRateItems.forEach(ri => ri.classList.remove('active'));
                this.classList.add('active');
                
                // If auto-refresh is active, restart it with the new rate
                if (isAutoRefreshActive) {
                    clearInterval(autoRefreshInterval);
                    autoRefreshInterval = setInterval(refreshBandwidthData, refreshRate * 1000);
                    showNotification(`Taux d'actualisation modifié à ${refreshRate} secondes`, 'info');
                }
            });
        });
    }
    
    // Add a dedicated button for database updates
    const refreshBtnContainer = document.querySelector('.d-flex');
    if (refreshBtnContainer) {
        const updateDbBtn = document.createElement('button');
        updateDbBtn.id = 'update-db-btn';
        updateDbBtn.className = 'btn btn-sm btn-outline-primary ms-2';
        updateDbBtn.innerHTML = '<i class="uil uil-database"></i> Mettre à jour la BD';
        updateDbBtn.addEventListener('click', updateBandwidthUsageInDatabase);
        refreshBtnContainer.appendChild(updateDbBtn);
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
    
    // Set up automatic database updates every 30 seconds, independent of auto-refresh
    // This will run regardless of whether auto-refresh is enabled
    const dbUpdateInterval = 30000; // 30 seconds
    
    // Initial update when page loads
    setTimeout(() => {
        updateBandwidthUsageInDatabase();
        
        // Set up recurring updates
        setInterval(() => {
            updateBandwidthUsageInDatabase();
        }, dbUpdateInterval);
        
        // Show a notification that automatic updates are enabled
        showNotification('Mise à jour automatique de la base de données toutes les 30 secondes', 'info', 10000);
    }, 5000); // Wait 5 seconds after page load before starting
});

// Format bandwidth for tooltips
function formatBandwidth(bytes) {
    // Ensure bytes is treated as a number
    bytes = Number(bytes);
    
    // Handle zero value case explicitly
    if (bytes === 0 || isNaN(bytes)) {
        return '0 bps';
    } else if (bytes > 1000000) {
        return (bytes / 1000000).toFixed(2) + ' Mbps';
    } else if (bytes > 1000) {
        return (bytes / 1000).toFixed(2) + ' Kbps';
    } else {
        // Ensure very small values are still displayed
        return Math.max(0.01, Math.round(bytes)) + ' bps';
    }
}

// Split chart initialization into phases for faster loading
function initializePrimaryCharts() {
    // Initialize only the gauge charts first - they're simple and lightweight
    const networkCapacityMbps = parseInt(document.getElementById('networkCapacity').value, 10);
    const maxNetworkSpeed = networkCapacityMbps * 1000000; // Convert Mbps to bps
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
}

function initializeSecondaryCharts() {
    // Daily Active Users Chart - the most complex chart with 30 days of data
    var dailyActiveUsers = [];

    // Get the daily active users history from statistics
    @if(!empty($statistics) && $statistics->count() > 0 && isset($statistics->first()->daily_active_users_history))
        dailyActiveUsers = @json($statistics->first()->daily_active_users_history);
        // Ensure at least one data point is visible
        if (dailyActiveUsers && dailyActiveUsers.length > 0) {
            let hasNonZeroValue = false;
            for (let i = 0; i < dailyActiveUsers.length; i++) {
                if (dailyActiveUsers[i] > 0) {
                    hasNonZeroValue = true;
                    break;
                }
            }
            if (!hasNonZeroValue) {
                // If all values are zero, add at least one user for today
                dailyActiveUsers[dailyActiveUsers.length - 1] = 1;
            }
        }
    @else
        // Fallback to empty array with at least one user for today
        dailyActiveUsers = Array(30).fill(0);
        dailyActiveUsers[29] = 1; // Today has at least one user (you)
    @endif

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
            toolbar: {
                show: false
            },
            fontFamily: 'inherit',
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800,
                animateGradually: {
                    enabled: true,
                    delay: 150
                },
                dynamicAnimation: {
                    enabled: true,
                    speed: 350
                }
            },
            dropShadow: {
                enabled: true,
                top: 3,
                left: 2,
                blur: 4,
                opacity: 0.1
            }
        },
        dataLabels: {
            enabled: false
        },
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
                style: {
                    fontSize: '12px',
                    fontWeight: 500,
                    colors: '#718096'
                }
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        },
        yaxis: {
            title: {
                text: 'Nombre d\'utilisateurs',
                style: {
                    fontSize: '13px',
                    fontWeight: 500
                }
            },
            min: 0,
            forceNiceScale: true,
            labels: {
                style: {
                    fontSize: '12px',
                    fontWeight: 500,
                    colors: ['#718096']
                },
                formatter: function(val) {
                    return Math.round(val);
                }
            }
        },
        markers: {
            size: 4,
            colors: ['#4361ee'],
            strokeColors: '#fff',
            strokeWidth: 2,
            hover: {
                size: 7
            }
        },
        tooltip: {
            theme: 'light',
            y: {
                formatter: function(val) {
                    return val + ' utilisateurs';
                },
                title: {
                    formatter: (seriesName) => seriesName,
                }
            },
            x: {
                show: true
            },
            marker: {
                show: true
            }
        },
        grid: {
            borderColor: '#e2e8f0',
            strokeDashArray: 4,
            padding: {
                top: 0,
                right: 0,
                bottom: 0,
                left: 10
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
    @else
        // Create default array with 24 hours (0-23) with at least 1 user for current hour
        userActivity = Array(24).fill(0);
        const currentHour = new Date().getHours();
        userActivity[currentHour] = 1; // At least one user for current hour
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
            animations: { 
                enabled: true,
                easing: 'easeinout',
                speed: 800,
                animateGradually: {
                    enabled: true,
                    delay: 150
                },
                dynamicAnimation: {
                    enabled: true,
                    speed: 350
                }
            },
            dropShadow: {
                enabled: true,
                top: 3,
                left: 2,
                blur: 4,
                opacity: 0.1
            }
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                columnWidth: '70%',
                distributed: false,
                rangeBarOverlap: true,
                rangeBarGroupRows: false,
                colors: {
                    ranges: [{
                        from: 0,
                        to: 0,
                        color: undefined
                    }],
                    backgroundBarColors: [],
                    backgroundBarOpacity: 1,
                },
                dataLabels: {
                    position: 'top'
                }
            }
        },
        colors: ['#4361ee'],
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'vertical',
                shadeIntensity: 0.1,
                gradientToColors: ['#2bc0e4'],
                inverseColors: false,
                opacityFrom: 1,
                opacityTo: 0.9,
            }
        },
        dataLabels: { 
            enabled: true,
            style: { 
                fontSize: '12px',
                fontWeight: 500,
                colors: ['#444']
            },
            offsetY: -20,
            formatter: function(val) {
                if (val === 0) return '';
                return val;
            }
        },
        xaxis: {
            categories: [...Array(24).keys()].map(hour => hour + 'h'),
            title: { 
                text: 'Heure de la journée',
                style: {
                    fontSize: '13px',
                    fontWeight: 500
                }
            },
            labels: {
                style: { 
                    fontSize: '12px',
                    fontWeight: 500,
                    colors: '#718096'
                }
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        },
        yaxis: {
            title: { 
                text: 'Nombre d\'utilisateurs',
                style: {
                    fontSize: '13px',
                    fontWeight: 500
                }
            },
            min: 0,
            forceNiceScale: true,
            labels: {
                style: {
                    fontSize: '12px',
                    fontWeight: 500,
                    colors: ['#718096']
                },
                formatter: function(val) {
                    return Math.round(val);
                }
            }
        },
        tooltip: {
            theme: 'light',
            y: {
                formatter: function (val) {
                    return val + ' utilisateurs';
                },
                title: {
                    formatter: (seriesName) => seriesName,
                }
            },
            marker: {
                show: true
            }
        },
        grid: {
            borderColor: '#e2e8f0',
            strokeDashArray: 4,
            padding: {
                top: 20,
                right: 0,
                bottom: 0,
                left: 10
            }
        },
        states: {
            hover: {
                filter: {
                    type: 'darken',
                    value: 0.9
                }
            },
            active: {
                filter: {
                    type: 'darken',
                    value: 0.85
                }
            }
        },
        responsive: [
            {
                breakpoint: 480,
                options: {
                    chart: {
                        height: 250
                    },
                    plotOptions: {
                        bar: {
                            columnWidth: '90%'
                        }
                    }
                }
            }
        ]
    };
        
    // Create the charts in the background
    setTimeout(() => {
        window.dailyActiveUsersChart = new ApexCharts(document.querySelector("#daily-active-users-chart"), dailyOptions);
        window.dailyActiveUsersChart.render();
    }, 0);
        
    setTimeout(() => {
        let bandwidthChart = new ApexCharts(document.querySelector("#bandwidth-usage-chart"), bandwidthOptions);
        bandwidthChart.render();
    }, 200);
        
    setTimeout(() => {
        window.userActivityChart = new ApexCharts(document.querySelector("#user-activity-chart"), activityOptions);
        window.userActivityChart.render();
        
        // Enable animations now that all charts are loaded
        enableAllChartAnimations();
    }, 400);
}

// Function to enable animations once everything is loaded
function enableAllChartAnimations() {
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

// Global variables for auto-refresh functionality
let isAutoRefreshActive = false;
let autoRefreshInterval = null;
let refreshRate = 1; // Default refresh rate in seconds - changed to 1 second

// Function to toggle auto-refresh
function toggleAutoRefresh() {
    const autoRefreshBtn = document.getElementById('auto-refresh-btn');
    
    if (!isAutoRefreshActive) {
        // Start auto-refresh
        isAutoRefreshActive = true;
        autoRefreshBtn.innerHTML = '<i class="uil uil-pause"></i> Pause automatique';
        autoRefreshBtn.classList.remove('btn-outline-success');
        autoRefreshBtn.classList.add('btn-success', 'btn-pulse');
        
        // First refresh immediately - with notification
        refreshBandwidthData(true);
        
        // Then set up interval - without notifications on each refresh
        autoRefreshInterval = setInterval(() => refreshBandwidthData(false), refreshRate * 1000);
        
        showNotification(`Actualisation automatique démarrée (toutes les ${refreshRate} secondes)`, 'success');
        
        // Update dropdown to show 1s
        document.getElementById('current-refresh-rate').textContent = refreshRate + 's';
        
        // Update active class in dropdown
        const refreshRateItems = document.querySelectorAll('.refresh-rate');
        refreshRateItems.forEach(ri => ri.classList.remove('active'));
        const activeOption = document.querySelector(`.refresh-rate[data-rate="${refreshRate}"]`);
        if (activeOption) activeOption.classList.add('active');
    } else {
        // Stop auto-refresh
        isAutoRefreshActive = false;
        clearInterval(autoRefreshInterval);
        autoRefreshBtn.innerHTML = '<i class="uil uil-play"></i> Démarrer l\'actualisation automatique';
        autoRefreshBtn.classList.remove('btn-success', 'btn-pulse');
        autoRefreshBtn.classList.add('btn-outline-success');
        autoRefreshBtn.removeAttribute('title');
        
        // Reset page title
        document.title = 'Statistiques';
        
        showNotification('Actualisation automatique arrêtée', 'info');
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
function refreshBandwidthData(showNotifications = true) {
    // Disable refresh button while loading
    const refreshBtn = document.getElementById('refresh-btn');
    if (refreshBtn) {
        refreshBtn.disabled = true;
        refreshBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Chargement...';
    }
    
    // Create a controller to be able to abort the fetch request if it takes too long
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 20000); // 20 second timeout - increased for network issues
    
    // Debug timestamp to ensure we're not getting cached data
    const timestamp = new Date().getTime();
    
    // Construct URL with force refresh
    const url = '{{ route('bandwidth.data') }}' + 
                '?force_refresh=1' + 
                '&_=' + timestamp;
    
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
            throw new Error('Réponse réseau non valide: ' + response.status + ' ' + response.statusText);
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
            // Display appropriate notifications based on data source - but only if not in auto-refresh mode or showNotifications is true
            if ((data.fromCache || data.from_cache) && showNotifications && !isAutoRefreshActive) {
                showNotification('Utilisation des données en cache: ' + (data.cacheReason || 'Délai d\'actualisation non expiré') + ' (' + (data.cache_time || 'time unknown') + ')', 'info', 5000);
            }
            
            // Display information about interface traffic if available
            if (data.debug && data.debug.interface_traffic) {
                const interfaceTraffic = data.debug.interface_traffic;
                if (interfaceTraffic.success) {
                    const interfaceType = interfaceTraffic.type || 'unknown';
                    const interfaceName = interfaceTraffic.interface || 'all';
                    
                    // Also log formatted values for better debugging
                    const formattedRx = formatBandwidth(interfaceTraffic.rx || 0);
                    const formattedTx = formatBandwidth(interfaceTraffic.tx || 0);
                    
                    // If it's an external interface, show a subtle notification - but only if not in auto-refresh mode or showNotifications is true
                    if (interfaceType === 'external' && showNotifications && !isAutoRefreshActive) {
                        showNotification(`Données de trafic en temps réel depuis l'interface externe: ${interfaceName}`, 'info', 3000);
                    }
                }
            }
            
            // Update page title to show we're using real data
            document.querySelector('h5.mb-0').innerHTML = 'Utilisation de la bande passante en temps réel';
            
            // Update statistics cards without animation for speed
            const activeUsersElement = document.querySelectorAll('.card-body h2')[0];
            if (activeUsersElement) {
                activeUsersElement.textContent = data.activeUsers || 0;
            }
            
            // Update the total download and upload metrics
            const metricCards = document.querySelectorAll('.metric-card');
            if (metricCards.length >= 4) {
                // Update Total Download (second card)
                const totalDownloadCard = metricCards[1];
                const totalDownloadValue = totalDownloadCard.querySelector('h2');
                if (totalDownloadValue) {
                    const downloadValue = data.totalRx || data.total_rx_rate_formatted || '0 bps';
                    totalDownloadValue.innerHTML = downloadValue;
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
                    const uploadValue = data.totalTx || data.total_tx_rate_formatted || '0 bps';
                    totalUploadValue.innerHTML = uploadValue;
                    if (data.totalTxBytes) {
                        const smallText = totalUploadCard.querySelector('p.small');
                        if (smallText) {
                            smallText.textContent = data.totalTxBytes + ' total';
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
            
            // Also update the second database totals section (historical data)
            const historicalTotalsSection = document.querySelector('.row.mb-4 .col-12.mb-4 .card-body');
            if (historicalTotalsSection && data.databaseTotals) {
                const historyTotals = historicalTotalsSection.querySelectorAll('h2.fw-bold');
                if (historyTotals.length >= 3) {
                    historyTotals[0].textContent = data.databaseTotals.downloaded || historyTotals[0].textContent;
                    historyTotals[1].textContent = data.databaseTotals.uploaded || historyTotals[1].textContent;
                    historyTotals[2].textContent = data.databaseTotals.total || historyTotals[2].textContent;
                }
            }
            
            // Calculate total bandwidth for gauge charts
            if ((data.debug && data.debug.bandwidth_raw) && window.downloadGaugeChart && window.uploadGaugeChart) {
                // Get total bandwidth in bps from the bandwidth_raw object (more reliable)
                const totalRxRateRaw = data.debug.bandwidth_raw.rx || 0;
                const totalTxRateRaw = data.debug.bandwidth_raw.tx || 0;
                
                // Get the selected network capacity from the dropdown
                const networkCapacitySelect = document.getElementById('networkCapacity');
                const networkCapacityMbps = networkCapacitySelect ? parseInt(networkCapacitySelect.value, 10) : 100;
                
                // Calculate max network speed in bps
                const maxNetworkSpeed = networkCapacityMbps * 1000000; // Convert Mbps to bps
                
                
                // Calculate percentage of network capacity (max 100%)
                const downloadPercentage = Math.min(100, Math.max(0, (totalRxRateRaw / maxNetworkSpeed) * 100));
                const uploadPercentage = Math.min(100, Math.max(0, (totalTxRateRaw / maxNetworkSpeed) * 100));
                
                
                // Update the gauge charts
                window.downloadGaugeChart.updateSeries([downloadPercentage]);
                window.uploadGaugeChart.updateSeries([uploadPercentage]);
            }
            
            // Show success message only for live data and only if not in auto-refresh mode or showNotifications is true
            if (!data.fromCache && !data.from_cache && showNotifications && !isAutoRefreshActive) {
                showNotification(`Données mises à jour à ${new Date().toLocaleTimeString()}`);
            }
            
            // Update the daily active users chart if user count has changed
            if (data.dailyActiveUsers && window.dailyActiveUsersChart) {
                // Update today's value in the chart only if the new count is higher
                const dailyData = window.dailyActiveUsersChart.w.config.series[0].data;
                if (Array.isArray(dailyData) && dailyData.length > 0) {
                    // Update the last value (today) only if the new value is higher than current value
                    const currentValue = dailyData[dailyData.length - 1];
                    const newValue = data.dailyActiveUsers;
                    
                    // Only update if the new value is higher (preserving historical maximum)
                    if (newValue > currentValue) {
                        dailyData[dailyData.length - 1] = newValue;
                        window.dailyActiveUsersChart.updateSeries([{
                            name: 'Utilisateurs actifs',
                            data: dailyData
                        }]);
                        
                        // Optionally show a notification when a new maximum is reached
                        if (showNotifications && !isAutoRefreshActive) {
                            showNotification(`Nouveau record d'utilisateurs actifs aujourd'hui: ${newValue}`, 'success');
                        }
                    }
                }
            }
            
            // Update the user activity by hour chart if available
            if (data.usersByHour && window.userActivityChart) {
                // Convert usersByHour object to array
                let hourlyData;
                if (Array.isArray(data.usersByHour)) {
                    hourlyData = data.usersByHour;
                } else {
                    // If it's an object with hour keys, convert to array
                    hourlyData = Array(24).fill(0);
                    for (const hour in data.usersByHour) {
                        if (hour >= 0 && hour < 24) {
                            hourlyData[hour] = data.usersByHour[hour];
                        }
                    }
                }
                
                // Ensure current hour has at least 1 user (someone is viewing the dashboard)
                const currentHour = new Date().getHours();
                if (hourlyData[currentHour] < 1) {
                    hourlyData[currentHour] = 1;
                }
                
                // Update the chart
                window.userActivityChart.updateSeries([{
                    name: 'Activité utilisateurs',
                    data: hourlyData
                }]);
            }
            
            // Update the auto-refresh button with current time without showing a notification
            if (isAutoRefreshActive) {
                const autoRefreshBtn = document.getElementById('auto-refresh-btn');
                if (autoRefreshBtn) {
                    // Add the current time to the button text
                    const timeString = new Date().toLocaleTimeString();
                    autoRefreshBtn.setAttribute('title', `Dernière mise à jour: ${timeString}`);
                    
                    // Update the page title with last update time
                    document.title = `Statistiques [${timeString}]`;
                }
            }
        } else {
            throw new Error(data.message || 'Une erreur inconnue est survenue');
        }
    })
    .catch(error => {
        
        if (refreshBtn) {
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = '<i class="uil uil-sync"></i> Actualiser';
        }
        
        // Provide more specific error message - but only show notification if not in auto-refresh mode or it's a new error
        let errorMessage = 'Erreur lors de la récupération des données';
        if (error.name === 'AbortError') {
            errorMessage = 'La requête a pris trop de temps et a été interrompue (plus de 20 secondes). Vérifiez la connexion à votre routeur MikroTik.';
        } else if (error.message) {
            errorMessage += ': ' + error.message;
        }
        
        // Only show error notifications if not in auto-refresh mode or showNotifications is true
        if (showNotifications || !isAutoRefreshActive) {
            showNotification(errorMessage, 'danger', 10000);
        }
        
        // Implement a retry mechanism after 30 seconds if auto-refresh is active
        if (isAutoRefreshActive) {
            // Only show the retry notification once, not for every failed attempt
            if (showNotifications) {
                showNotification('Nouvelle tentative prévue selon le taux d\'actualisation configuré...', 'info');
            }
            // Don't set a timeout here - the interval is already running
        }
    });
}

// Function to check router settings
function checkRouterSettings() {
    // Show information about the router configuration
    let routerInfo = `
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">Paramètres de connexion au routeur</h6>
            </div>
            <div class="card-body">
                <p class="mb-3">Vérifiez les paramètres suivants dans votre fichier .env :</p>
                <ul class="list-group mb-3">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        MIKROTIK_HOST
                        <span class="badge bg-primary">Adresse IP ou nom d'hôte</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        MIKROTIK_PORT
                        <span class="badge bg-primary">Port API (généralement 8728)</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        MIKROTIK_USER
                        <span class="badge bg-primary">Nom d'utilisateur API</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        MIKROTIK_PASS
                        <span class="badge bg-primary">Mot de passe API</span>
                    </li>
                </ul>
                <p>Assurez-vous que l'API est activée sur votre routeur MikroTik et que les identifiants sont corrects.</p>
            </div>
        </div>
    `;
    
    // Display the information in a modal
    const modalContent = document.createElement('div');
    modalContent.innerHTML = routerInfo;
    
    // Append to body and show
    document.body.appendChild(modalContent);
    
    // Use Bootstrap modal if available
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const modalElement = document.createElement('div');
        modalElement.className = 'modal fade';
        modalElement.id = 'routerSettingsModal';
        modalElement.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Paramètres du routeur MikroTik</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ${routerInfo}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modalElement);
        
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
        
        // Remove from DOM when hidden
        modalElement.addEventListener('hidden.bs.modal', function() {
            document.body.removeChild(modalElement);
        });
    } else {
        // Fallback if Bootstrap is not available
        alert('Vérifiez les paramètres de connexion dans votre fichier .env:\n\nMIKROTIK_HOST (Adresse IP ou nom d\'hôte)\nMIKROTIK_PORT (Port API, généralement 8728)\nMIKROTIK_USER (Nom d\'utilisateur API)\nMIKROTIK_PASS (Mot de passe API)');
    }
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
        showNotification('Erreur lors de la mise à jour des données: ' + error.message, 'danger', 5000);
    });
}

// Initialize tooltips
const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
</script>
@endsection