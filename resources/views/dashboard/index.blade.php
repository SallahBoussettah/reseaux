@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
    <div class="dashboard-container">
        <!-- Notification element -->
        <div id="notification" class="notification success">
            <i class="uil uil-check-circle"></i>
            <div class="notification-content">
                <div class="notification-title">Succès</div>
                <div class="notification-message">Données actualisées avec succès</div>
            </div>
        </div>

        <div class="dashboard-header">
            <div class="header-content">
                <h2 class="header-title">Tableau de bord</h2>
                <p class="header-subtitle">Bienvenue sur Eureka Wifi Dashboard</p>
            </div>
            <div class="header-profile">
                <div class="profile-image">
                    <img src="{{ asset('assets/img/avatar/user.svg') }}" alt="Profile" />
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card purple">
                <div class="stat-content">
                    <div class="stat-value">{{ $totalUsers }}</div>
                    <div class="stat-label">Utilisateurs total</div>
                </div>
                <div class="stat-icon">
                    <i class="uil uil-users-alt"></i>
                </div>
            </div>

            <div class="stat-card pink">
                <div class="stat-content">
                    <div class="stat-value">{{ $connectedThisMonth }}</div>
                    <div class="stat-label">Connectés ce mois</div>
                </div>
                <div class="stat-icon">
                    <i class="uil uil-calendar-alt"></i>
                </div>
            </div>

            <div class="stat-card green">
                <div class="stat-content">
                    <div class="stat-value">{{ $newUsers }}</div>
                    <div class="stat-label">Nouveaux utilisateurs</div>
                </div>
                <div class="stat-icon">
                    <i class="uil uil-user-plus"></i>
                </div>
            </div>

            <div class="stat-card blue">
                <div class="stat-content">
                    <div class="stat-value">{{ $connectedThisWeek }}</div>
                    <div class="stat-label">Connectés cette semaine</div>
                </div>
                <div class="stat-icon">
                    <i class="uil uil-signal"></i>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row charts-section">
            <!-- Daily Connections Chart -->
            <div class="col-md-8">
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="table-icon-title">
                            <i class="uil uil-chart-line"></i>
                            <h3 class="table-title">Connexions journalières</h3>
                        </div>
                    </div>
                    <div class="chart-body">
                        <div id="dailyConnectionsChart" class="chart-container"></div>
                    </div>
                </div>
            </div>

            <!-- Platform Statistics Chart -->
            <div class="col-md-4">
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="table-icon-title">
                            <i class="uil uil-desktop"></i>
                            <h3 class="table-title">Plateformes utilisées</h3>
                        </div>
                    </div>
                    <div class="chart-body">
                        <div id="platformChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Section -->
        <div class="row tables-section">
            <!-- Recent Connections Table -->
            <div class="col-md-12 mb-4">
                <div class="table-card">
                    <div class="table-header">
                        <div class="table-icon-title">
                            <i class="uil uil-users-alt"></i>
                            <h3 class="table-title">Connexions récentes</h3>
                        </div>
                        <div class="table-actions">
                            <button class="refresh-btn">
                                <i class="uil uil-sync"></i>
                                Actualiser
                            </button>
                        </div>
                    </div>
                    <div class="table-body">
                        <div class="table-scroll-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>IDENTIFIANT</th>
                                        <th>MAC ADDRESS</th>
                                        <th>EMAIL</th>
                                        <th>DOWNLOAD</th>
                                        <th>UPLOAD</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $connectionCount = 0; @endphp
                                    @foreach($users as $user)
                                        @if(strpos($user->email, 'token') !== false && $connectionCount < 5)
                                            @php $connectionCount++; @endphp
                                            <tr>
                                                <td class="user-info">
                                                    <i class="uil uil-user"></i>
                                                    {{ $user->full_name ?? $user->name ?? explode('@', $user->email)[0] }}
                                                </td>
                                                <td class="mac-address">
                                                    {{ $user->mac_address ?? 'N/A' }}
                                                </td>
                                                <td class="email">
                                                    @if(strpos($user->email, 'token_user') === 0)
                                                        <span class="token-user">Token User</span>
                                                    @else
                                                        {{ $user->email }}
                                                    @endif
                                                </td>
                                                <td class="text-right">
                                                    <span class="download-value">
                                                        <i class="uil uil-download-alt"></i>
                                                        {{ isset($user->total_uploaded_bytes) ? number_format($user->total_uploaded_bytes / 1048576, 2) . ' MB' : '0 MB' }}
                                                    </span>
                                                </td>
                                                <td class="text-right">
                                                    <span class="upload-value">
                                                        <i class="uil uil-upload-alt"></i>
                                                        {{ isset($user->total_downloaded_bytes) ? number_format($user->total_downloaded_bytes / 1048576, 2) . ' MB' : '0 MB' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Demographics Table -->
            <div class="col-md-6">
                <div class="table-card">
                    <div class="table-header">
                        <div class="table-icon-title">
                            <i class="uil uil-chart-pie"></i>
                            <h3 class="table-title">Démographie</h3>
                        </div>
                    </div>
                    <div class="table-body">
                        <div class="table-scroll-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>POPULATION</th>
                                        <th>TAUX (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($demographics as $demographic)
                                        <tr>
                                            <td class="language-info">
                                                <i class="uil uil-globe"></i>
                                                {{ $demographic->name }}
                                            </td>
                                            <td>
                                                <div class="percentage-bar-container">
                                                    <div class="percentage-bar" style="width: {{ $demographic->percentage }}%">
                                                    </div>
                                                    <span class="percentage-value">{{ $demographic->percentage }}%</span>
                                                </div>
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
    <!-- ApexCharts CDN -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0/dist/apexcharts.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0/dist/apexcharts.css">

    <script>
        // Fix for problematic functions
        (function () {
            // Fix for dragula
            if (typeof window.dragula === 'undefined') {
                window.dragula = function () {
                    return {
                        on: function () { }
                    };
                };
            }

            // Fix for countdown
            if (typeof window.setCountdown === 'undefined') {
                window.setCountdown = function () { };
            }
        })();

        // Main dashboard functionality
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize charts
            initializeCharts();

            // Refresh button functionality
            var refreshBtn = document.querySelector('.refresh-btn');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function () {
                    showNotification('Actualisation en cours...', 'info');
                    setTimeout(function () {
                        location.reload();
                    }, 500);
                });
            }

            // Check if page was just refreshed
            if (performance.navigation && performance.navigation.type === 1) {
                showNotification('Données actualisées avec succès', 'success');
            }

            // Function to show notification
            function showNotification(message, type) {
                type = type || 'success';
                var notification = document.getElementById('notification');
                if (!notification) return;

                // Set type and message
                notification.className = 'notification ' + type;
                var messageElement = notification.querySelector('.notification-message');
                if (messageElement) {
                    messageElement.textContent = message;
                }

                // Show notification
                notification.classList.add('show');

                // Hide after 3 seconds
                setTimeout(function () {
                    notification.classList.remove('show');
                }, 3000);
            }

            // Function to initialize charts
            function initializeCharts() {
                // Daily connections chart
                renderDailyConnectionsChart();

                // Platform chart
                renderPlatformChart();
            }

            function renderDailyConnectionsChart() {
                var dailyConnectionsContainer = document.getElementById('dailyConnectionsChart');
                if (!dailyConnectionsContainer) return;

                // Clear the container
                dailyConnectionsContainer.innerHTML = '';

                // Get data
                var dates = @json(array_keys($data));
                var connectionCounts = @json(array_values($data));
                var totalConnections = {{ array_sum($data) }};

                // Generate last 7 days if dates are not available or invalid
                var today = new Date();
                var last7Days = [];
                var formattedDates = [];

                // Create array of the last 7 days
                for (var i = 6; i >= 0; i--) {
                    var date = new Date(today);
                    date.setDate(today.getDate() - i);
                    last7Days.push(date);
                }

                // Format dates to be more readable (e.g., "Lun 5", "Mar 6", etc.)
                for (var i = 0; i < last7Days.length; i++) {
                    var date = last7Days[i];
                    var days = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
                    var dayName = days[date.getDay()];
                    var dateNum = date.getDate();
                    formattedDates.push(dayName + ' ' + dateNum);
                }

                // Create chart info element
                var chartInfoElement = document.createElement('div');
                chartInfoElement.className = 'chart-info';
                chartInfoElement.innerHTML = `
                                <div class="chart-value">${totalConnections}</div>
                                <div class="chart-label">Connexions totales (7 derniers jours)</div>
                            `;
                dailyConnectionsContainer.appendChild(chartInfoElement);

                // Create chart element
                var chartElement = document.createElement('div');
                chartElement.id = 'dailyConnectionsChartGraph';
                dailyConnectionsContainer.appendChild(chartElement);

                // Configure chart options
                var options = {
                    series: [{
                        name: 'Connexions',
                        data: connectionCounts
                    }],
                    chart: {
                        type: 'area',
                        height: 200,
                        toolbar: {
                            show: false
                        },
                        fontFamily: 'inherit',
                        animations: {
                            enabled: false
                        },
                        sparkline: {
                            enabled: false
                        }
                    },
                    colors: ['#6366F1'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.7,
                            opacityTo: 0.3,
                            stops: [0, 90, 100]
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    xaxis: {
                        type: 'category',
                        categories: formattedDates,
                        labels: {
                            show: true,
                            style: {
                                fontSize: '11px',
                                fontFamily: 'inherit',
                                fontWeight: 500,
                                colors: 'var(--gray-600)'
                            },
                            rotate: 0
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        },
                        crosshairs: {
                            show: true,
                            position: 'back',
                            stroke: {
                                color: 'var(--purple)',
                                width: 1,
                                dashArray: 3
                            }
                        },
                        tooltip: {
                            enabled: false
                        }
                    },
                    yaxis: {
                        labels: {
                            show: false
                        }
                    },
                    grid: {
                        show: false,
                        padding: {
                            left: 0,
                            right: 0,
                            top: 0,
                            bottom: 0
                        }
                    },
                    tooltip: {
                        enabled: true,
                        style: {
                            fontSize: '12px',
                            fontFamily: 'inherit'
                        },
                        y: {
                            formatter: function (value) {
                                return value + ' connexions';
                            }
                        },
                        marker: {
                            show: true
                        },
                        theme: 'light'
                    },
                    markers: {
                        size: 4,
                        colors: ['#fff'],
                        strokeColors: 'var(--purple)',
                        strokeWidth: 2,
                        hover: {
                            size: 6
                        }
                    }
                };

                // Initialize chart
                try {
                    var chart = new ApexCharts(document.getElementById('dailyConnectionsChartGraph'), options);
                    chart.render();
                } catch (error) {
                    console.error('Error rendering daily connections chart:', error);
                    // Fallback to placeholder
                    dailyConnectionsContainer.innerHTML = `
                                    <div class="chart-placeholder">
                                        <div class="chart-placeholder-text">${totalConnections}</div>
                                        <div class="chart-placeholder-label">Connexions totales (7 derniers jours)</div>
                                    </div>
                                `;
                }
            }

            function renderPlatformChart() {
                var platformContainer = document.getElementById('platformChart');
                if (!platformContainer) return;

                // Clear the container
                platformContainer.innerHTML = '';

                // Get data
                var labels = @json($labels);
                var platformData = @json($datadevice);

                // Check if we have data
                if (!labels || !labels.length || !platformData || !platformData.length) {
                    // Use default data if none available
                    labels = ['Android', 'Windows', 'iOS'];
                    platformData = [1, 1, 1];
                }

                // Calculate total platforms
                var totalPlatforms = 0;
                for (var i = 0; i < platformData.length; i++) {
                    totalPlatforms += parseInt(platformData[i]);
                }

                // Create chart info element
                var chartInfoElement = document.createElement('div');
                chartInfoElement.className = 'chart-info platform-info';
                chartInfoElement.innerHTML = `
                                <div class="chart-value">${totalPlatforms}</div>
                                <div class="chart-label">Plateformes connectées</div>
                            `;
                platformContainer.appendChild(chartInfoElement);

                // Create chart element
                var chartElement = document.createElement('div');
                chartElement.id = 'platformChartGraph';
                platformContainer.appendChild(chartElement);

                // Define platform-specific colors and icons
                var platformColors = {
                    'Windows': '#0078D4',
                    'Android': '#3DDC84',
                    'iOS': '#007AFF',
                    'macOS': '#000000',
                    'Linux': '#FCC624',
                    'Chrome OS': '#4285F4'
                };

                // Get colors for current platforms
                var colors = labels.map(function (label) {
                    return platformColors[label] || '#6366F1';
                });

                // Configure chart options
                var options = {
                    series: platformData,
                    labels: labels,
                    chart: {
                        type: 'donut',
                        height: 280,
                        fontFamily: 'inherit',
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        },
                        background: 'transparent',
                        offsetY: 0
                    },
                    colors: colors,
                    stroke: {
                        width: 0
                    },
                    legend: {
                        show: true,
                        position: 'bottom',
                        fontSize: '11px',
                        fontFamily: 'inherit',
                        fontWeight: 500,
                        offsetY: 10,
                        height: 50,
                        markers: {
                            width: 8,
                            height: 8,
                            radius: 12,
                            offsetX: -2
                        },
                        itemMargin: {
                            horizontal: 6,
                            vertical: 2
                        },
                        labels: {
                            colors: ['#374151'],
                            useSeriesColors: false
                        }
                    },
                    plotOptions: {
                        pie: {
                            expandOnClick: false,
                            donut: {
                                size: '60%',
                                background: 'transparent',
                                labels: {
                                    show: true,
                                    name: {
                                        show: false
                                    },
                                    value: {
                                        show: true,
                                        fontSize: '24px',
                                        fontFamily: 'inherit',
                                        fontWeight: 700,
                                        color: '#374151',
                                        offsetY: -5,
                                        formatter: function (val) {
                                            return totalPlatforms;
                                        }
                                    },
                                    total: {
                                        show: true,
                                        showAlways: true,
                                        label: 'Total',
                                        fontSize: '12px',
                                        fontFamily: 'inherit',
                                        fontWeight: 500,
                                        color: '#6B7280',
                                        formatter: function (w) {
                                            return totalPlatforms;
                                        }
                                    }
                                }
                            },
                            dataLabels: {
                                offset: 0,
                                minAngleToShowLabel: 15
                            }
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    tooltip: {
                        enabled: true,
                        style: {
                            fontSize: '12px',
                            fontFamily: 'inherit'
                        },
                        y: {
                            formatter: function (value) {
                                var percentage = ((value / totalPlatforms) * 100).toFixed(1);
                                return value + ' utilisateurs (' + percentage + '%)';
                            }
                        },
                        theme: 'light'
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                height: 220
                            },
                            plotOptions: {
                                pie: {
                                    donut: {
                                        size: '55%',
                                        labels: {
                                            value: {
                                                fontSize: '20px'
                                            },
                                            total: {
                                                fontSize: '10px'
                                            }
                                        }
                                    }
                                }
                            },
                            legend: {
                                position: 'bottom',
                                offsetY: 5,
                                fontSize: '10px'
                            }
                        }
                    }]
                };

                // Initialize custom circular progress chart
                try {
                    renderCustomCircularChart();

                } catch (error) {
                    console.error('Error rendering platform chart:', error);
                    // Fallback to enhanced list view
                    var platformItemsHtml = '';
                    for (var i = 0; i < labels.length; i++) {
                        var percentage = totalPlatforms > 0 ? ((platformData[i] / totalPlatforms) * 100).toFixed(1) : 0;
                        var color = colors[i] || '#6366F1';

                        platformItemsHtml += `
                                        <div class="platform-item">
                                            <div class="platform-info">
                                                <div class="platform-indicator" style="background-color: ${color}"></div>
                                                <span class="platform-name">${labels[i]}</span>
                                            </div>
                                            <div class="platform-stats">
                                                <span class="platform-value">${platformData[i]}</span>
                                                <span class="platform-percentage">${percentage}%</span>
                                            </div>
                                        </div>
                                    `;
                    }

                    platformContainer.innerHTML = `
                                    <div class="chart-info platform-info">
                                        <div class="chart-value">${totalPlatforms}</div>
                                        <div class="chart-label">Plateformes connectées</div>
                                    </div>
                                    <div class="platform-list">
                                        ${platformItemsHtml}
                                    </div>
                                `;
                }

                // Custom circular progress chart function
                function renderCustomCircularChart() {
                    var container = document.getElementById('platformChartGraph');
                    if (!container) return;

                    // Clear container
                    container.innerHTML = '';

                    // Calculate percentages
                    var percentages = [];
                    var cumulativePercentage = 0;

                    for (var i = 0; i < platformData.length; i++) {
                        var percentage = (platformData[i] / totalPlatforms) * 100;
                        percentages.push({
                            label: labels[i],
                            value: platformData[i],
                            percentage: percentage,
                            color: colors[i],
                            startAngle: cumulativePercentage * 3.6, // Convert to degrees
                            endAngle: (cumulativePercentage + percentage) * 3.6
                        });
                        cumulativePercentage += percentage;
                    }

                    // Create SVG
                    var svgSize = 200;
                    var center = svgSize / 2;
                    var radius = 70;
                    var strokeWidth = 20;

                    var svg = `
                            <div class="circular-progress-container">
                                <svg width="${svgSize}" height="${svgSize}" class="circular-progress-svg">
                                    <circle 
                                        cx="${center}" 
                                        cy="${center}" 
                                        r="${radius}" 
                                        fill="none" 
                                        stroke="#f3f4f6" 
                                        stroke-width="${strokeWidth}"
                                        class="progress-bg"
                                    />
                        `;

                    // Add progress segments
                    var circumference = 2 * Math.PI * radius;
                    var currentOffset = 0;

                    percentages.forEach(function (platform, index) {
                        var segmentLength = (platform.percentage / 100) * circumference;
                        var dashArray = segmentLength + ' ' + circumference;
                        var dashOffset = -currentOffset;

                        svg += `
                                <circle 
                                    cx="${center}" 
                                    cy="${center}" 
                                    r="${radius}" 
                                    fill="none" 
                                    stroke="${platform.color}" 
                                    stroke-width="${strokeWidth}"
                                    stroke-dasharray="${dashArray}"
                                    stroke-dashoffset="${dashOffset}"
                                    stroke-linecap="round"
                                    class="progress-segment"
                                    data-platform="${platform.label}"
                                    data-value="${platform.value}"
                                    data-percentage="${platform.percentage.toFixed(1)}"
                                    style="transform: rotate(-90deg); transform-origin: ${center}px ${center}px; transition: all 0.3s ease;"
                                />
                            `;
                        currentOffset += segmentLength;
                    });

                    // Add center text
                    svg += `
                                    <text x="${center}" y="${center - 5}" text-anchor="middle" class="center-value">${totalPlatforms}</text>
                                    <text x="${center}" y="${center + 15}" text-anchor="middle" class="center-label">Total</text>
                                </svg>

                                <div class="platform-legend">
                        `;

                    // Add legend
                    percentages.forEach(function (platform) {
                        svg += `
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: ${platform.color}"></div>
                                    <span class="legend-text">${platform.label}</span>
                                    <span class="legend-value">${platform.value} (${platform.percentage.toFixed(1)}%)</span>
                                </div>
                            `;
                    });

                    svg += `
                                </div>
                            </div>
                        `;

                    container.innerHTML = svg;

                    // Add hover effects
                    var segments = container.querySelectorAll('.progress-segment');
                    segments.forEach(function (segment) {
                        segment.addEventListener('mouseenter', function () {
                            this.style.strokeWidth = (strokeWidth + 4) + 'px';
                            this.style.filter = 'brightness(1.1)';
                        });

                        segment.addEventListener('mouseleave', function () {
                            this.style.strokeWidth = strokeWidth + 'px';
                            this.style.filter = 'brightness(1)';
                        });
                    });
                }
            }
        });
    </script>

    <style>
        /* Modern Dashboard Styles - Aligned with side navbar */
        :root {
            --purple: #6366F1;
            --purple-light: #8B5CF6;
            --pink: #F43F5E;
            --pink-light: #FB7185;
            --green: #10B981;
            --green-light: #34D399;
            --blue: #3B82F6;
            --blue-light: #60A5FA;
            --gray-50: #F9FAFB;
            --gray-100: #F3F4F6;
            --gray-200: #E5E7EB;
            --gray-300: #D1D5DB;
            --gray-400: #9CA3AF;
            --gray-500: #6B7280;
            --gray-600: #4B5563;
            --gray-700: #374151;
            --gray-800: #1F2937;
            --gray-900: #111827;
            --radius: 12px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --animation-duration: 0.5s;
        }

        /* Main container */
        .dashboard-container {
            padding: 0;
            width: 100%;
        }

        /* Header */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 0;
            animation: fadeInDown var(--animation-duration) ease-out;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            border-radius: var(--radius);
            padding: 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.2s, box-shadow 0.2s;
            animation: fadeInUp var(--animation-duration) ease-out;
            animation-fill-mode: both;
        }

        .stat-card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .stat-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .stat-card:nth-child(3) {
            animation-delay: 0.3s;
        }

        .stat-card:nth-child(4) {
            animation-delay: 0.4s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .stat-card.purple {
            background: linear-gradient(135deg, var(--purple), var(--purple-light));
        }

        .stat-card.pink {
            background: linear-gradient(135deg, var(--pink), var(--pink-light));
        }

        .stat-card.green {
            background: linear-gradient(135deg, var(--green), var(--green-light));
        }

        .stat-card.blue {
            background: linear-gradient(135deg, var(--blue), var(--blue-light));
        }

        /* Charts Section */
        .charts-section {
            margin-bottom: 2rem;
            animation: fadeIn calc(var(--animation-duration) * 1.5) ease-out;
            animation-delay: 0.5s;
            animation-fill-mode: both;
        }

        .chart-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 1.5rem;
            height: 100%;
        }

        .chart-header {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chart-body {
            padding: 0.5rem 1rem;
            display: flex;
            flex-direction: column;
            height: auto;
            min-height: 320px;
        }

        .chart-container {
            position: relative;
            height: auto;
            min-height: 280px;
            display: flex;
            flex-direction: column;
            background-color: white;
            border-radius: 8px;
            padding: 0 10px;
            overflow: visible;
        }

        /* Chart styles */
        .chart-info {
            display: flex;
            flex-direction: column;
            margin-bottom: 1rem;
        }

        .chart-value {
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--gray-800);
            line-height: 1;
        }

        .chart-label {
            font-size: 0.875rem;
            color: var(--gray-500);
            margin-top: 0.25rem;
        }

        #dailyConnectionsChartGraph {
            width: 100%;
            height: 180px;
        }

        #platformChartGraph {
            width: 100%;
            height: 280px;
            padding: 10px 0;
        }

        /* Fallback styles */
        .chart-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 1rem;
            width: 100%;
            height: 100%;
        }

        .chart-placeholder-text {
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
            line-height: 1;
        }

        .chart-placeholder-label {
            font-size: 0.875rem;
            color: var(--gray-500);
        }

        /* Platform Chart Styles */
        .platform-info {
            text-align: center;
            margin-bottom: 1rem;
        }

        .platform-list {
            width: 100%;
            margin-top: 1rem;
        }

        .platform-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--gray-100);
            transition: background-color 0.2s ease;
        }

        .platform-item:last-child {
            border-bottom: none;
        }

        .platform-item:hover {
            background-color: var(--gray-50);
            border-radius: 6px;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        .platform-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .platform-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .platform-name {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--gray-700);
        }

        .platform-stats {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .platform-value {
            font-weight: 600;
            color: var(--gray-800);
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
            font-size: 0.9rem;
        }

        .platform-percentage {
            font-size: 0.8rem;
            color: var(--gray-500);
            font-weight: 500;
            background-color: var(--gray-100);
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            min-width: 45px;
            text-align: center;
        }

        /* Custom Circular Progress Chart Styles */
        .circular-progress-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            padding: 20px 0;
        }

        .circular-progress-svg {
            margin-bottom: 20px;
        }

        .progress-bg {
            opacity: 0.3;
        }

        .progress-segment {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .progress-segment:hover {
            filter: brightness(1.1);
        }

        .center-value {
            font-size: 28px;
            font-weight: 700;
            fill: var(--gray-800);
            font-family: inherit;
        }

        .center-label {
            font-size: 12px;
            font-weight: 500;
            fill: var(--gray-500);
            font-family: inherit;
        }

        .platform-legend {
            display: flex;
            flex-direction: column;
            gap: 8px;
            width: 100%;
            max-width: 250px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background-color: var(--gray-50);
            border-radius: 8px;
            transition: background-color 0.2s ease;
        }

        .legend-item:hover {
            background-color: var(--gray-100);
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .legend-text {
            font-size: 13px;
            font-weight: 500;
            color: var(--gray-700);
            flex-grow: 1;
        }

        .legend-value {
            font-size: 12px;
            font-weight: 600;
            color: var(--gray-600);
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .circular-progress-svg {
                width: 160px;
                height: 160px;
            }

            .center-value {
                font-size: 24px;
            }

            .center-label {
                font-size: 10px;
            }

            .legend-item {
                padding: 4px 8px;
            }

            .legend-text {
                font-size: 12px;
            }

            .legend-value {
                font-size: 11px;
            }
        }

        /* Legacy device type styles for backward compatibility */
        .device-type-list {
            width: 100%;
            margin-top: 1rem;
            text-align: left;
        }

        .device-type-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0.5rem;
            border-bottom: 1px solid var(--gray-200);
            align-items: center;
        }

        .device-type-item:last-child {
            border-bottom: none;
        }

        .device-type-name {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--gray-700);
            display: flex;
            align-items: center;
        }

        .device-type-name::before {
            content: "";
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .device-type-item:nth-child(1) .device-type-name::before {
            background-color: var(--purple);
        }

        .device-type-item:nth-child(2) .device-type-name::before {
            background-color: var(--pink);
        }

        .device-type-value {
            font-weight: 600;
            color: var(--gray-800);
            font-family: monospace;
        }

        .device-total-center,
        .platform-total-center {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            pointer-events: none;
        }

        .device-total-center .total-value,
        .platform-total-center .total-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--gray-800);
            line-height: 1;
        }

        .device-total-center .total-label,
        .platform-total-center .total-label {
            font-size: 0.875rem;
            color: var(--gray-500);
            margin-top: 0.25rem;
        }

        /* Tables Section */
        .tables-section {
            margin-bottom: 2rem;
            animation: fadeIn calc(var(--animation-duration) * 1.5) ease-out;
            animation-delay: 0.6s;
            animation-fill-mode: both;
        }

        .table-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 1.5rem;
            height: 100%;
            animation: fadeInUp var(--animation-duration) ease-out;
            animation-fill-mode: both;
        }

        .table-card:nth-child(1) {
            animation-delay: 0.7s;
        }

        .table-card:nth-child(2) {
            animation-delay: 0.8s;
        }

        .table-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: white;
        }

        .table-icon-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .table-icon-title i {
            color: var(--purple);
            font-size: 1.25rem;
        }

        .table-title {
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
            color: var(--gray-700);
        }

        .table-actions {
            display: flex;
            gap: 0.5rem;
        }

        .refresh-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            border: 1px solid var(--purple);
            background: white;
            color: var(--purple);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: var(--shadow-sm);
        }

        .refresh-btn:hover {
            background: var(--purple);
            color: white;
        }

        .refresh-btn i {
            font-size: 1rem;
        }

        /* Table Styles Improvements */
        .table-body {
            padding: 0;
        }

        .table-scroll-container {
            overflow-x: auto;
            border-radius: 0 0 var(--radius) var(--radius);
            max-height: none;
            overflow-y: visible;
        }

        .col-md-12 .table-scroll-container {
            max-height: 300px;
            overflow-y: auto;
        }

        .mb-4 {
            margin-bottom: 2rem !important;
        }

        .data-table {
            table-layout: fixed;
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .data-table thead {
            background-color: var(--gray-50);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .data-table th {
            padding: 0.875rem 1.25rem;
            text-align: left;
            font-weight: 600;
            color: var(--gray-700);
            border-bottom: 1px solid var(--gray-200);
            white-space: nowrap;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        .data-table th:nth-child(1) {
            width: 15%;
        }

        /* IDENTIFIANT */
        .data-table th:nth-child(2) {
            width: 15%;
        }

        /* MAC ADDRESS */
        .data-table th:nth-child(3) {
            width: 40%;
        }

        /* EMAIL */
        .data-table th:nth-child(4) {
            width: 15%;
            text-align: right;
        }

        /* DOWNLOAD */
        .data-table th:nth-child(5) {
            width: 15%;
            text-align: right;
        }

        /* UPLOAD */

        .data-table td {
            padding: 0.875rem 1.25rem;
            border-bottom: 1px solid var(--gray-200);
            color: var(--gray-700);
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .data-table tbody tr {
            transition: background-color 0.2s;
        }

        .data-table tbody tr:hover {
            background-color: var(--gray-50);
        }

        .user-info,
        .language-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-info i,
        .language-info i {
            color: var(--purple);
            font-size: 1.125rem;
            width: 1.5rem;
            height: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--gray-100);
            border-radius: 50%;
        }

        .mac-address {
            font-family: monospace;
            font-size: 0.85rem;
            color: var(--gray-600);
            letter-spacing: 0.05em;
        }

        /* Percentage bar styles */
        .percentage-bar-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
        }

        .percentage-bar {
            height: 8px;
            background: linear-gradient(90deg, var(--purple), var(--purple-light));
            border-radius: 4px;
            min-width: 4px;
            transition: width 1s ease-out;
        }

        .percentage-value {
            font-weight: 600;
            color: var(--gray-700);
            white-space: nowrap;
        }

        /* Notifications */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.25rem;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: var(--shadow-md);
            transform: translateX(110%);
            transition: transform 0.3s ease-in-out;
            z-index: 9999;
            max-width: 350px;
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.success {
            background: var(--green);
            color: white;
        }

        .notification.info {
            background: var(--blue);
            color: white;
        }

        .notification.error {
            background: var(--pink);
            color: white;
        }

        /* Responsive Adjustments */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Add row animation */
        .data-table tbody tr {
            animation: fadeIn 0.5s ease-out forwards;
            opacity: 0;
        }

        .data-table tbody tr:nth-child(1) {
            animation-delay: 0.1s;
        }

        .data-table tbody tr:nth-child(2) {
            animation-delay: 0.2s;
        }

        .data-table tbody tr:nth-child(3) {
            animation-delay: 0.3s;
        }

        .data-table tbody tr:nth-child(4) {
            animation-delay: 0.4s;
        }

        .data-table tbody tr:nth-child(5) {
            animation-delay: 0.5s;
        }

        .data-table tbody tr:nth-child(6) {
            animation-delay: 0.6s;
        }

        .data-table tbody tr:nth-child(7) {
            animation-delay: 0.7s;
        }

        .data-table tbody tr:nth-child(8) {
            animation-delay: 0.8s;
        }

        .data-table tbody tr:nth-child(9) {
            animation-delay: 0.9s;
        }

        .data-table tbody tr:nth-child(10) {
            animation-delay: 1.0s;
        }

        /* Data usage styles */
        .download-value,
        .upload-value {
            display: inline-flex;
            align-items: center;
            font-family: monospace;
            font-size: 0.85rem;
            color: var(--gray-700);
        }

        .download-value i {
            color: var(--blue);
            font-size: 1rem;
            margin-right: 0.25rem;
        }

        .upload-value i {
            color: var(--green);
            font-size: 1rem;
            margin-right: 0.25rem;
        }

        .text-right {
            text-align: right;
        }

        .email {
            color: var(--gray-600);
            font-size: 0.85rem;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .token-user {
            color: var(--gray-500);
            font-style: italic;
            background-color: var(--gray-100);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
    </style>
@endsection