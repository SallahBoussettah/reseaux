<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Restaurant Client Registration</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style type="text/css">
        /* Dashboard styles */
.dashboard-container {
    display: flex;
    min-height: 100vh;
}

.sidebar {
    width: 250px;
    background-color: #2c3e50;
    color: white;
    padding: 20px;
}

.sidebar nav ul {
    list-style-type: none;
    padding: 0;
}

.sidebar nav ul li {
    margin-bottom: 10px;
}

.sidebar nav ul li a {
    color: white;
    text-decoration: none;
    font-size: 18px;
}

.content {
    flex-grow: 1;
    padding: 20px;
}

.card-container {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
}

.card {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 20px;
    width: calc(33.33% - 20px);
    text-align: center;
}

.card h2 {
    margin-top: 0;
}

.card p {
    font-size: 24px;
    font-weight: bold;
    color: var(--primary-color);
}

.stats-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.stats-card {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 20px;
    width: calc(46% - 10px);
}

.stats-card h2 {
    margin-top: 0;
}

.stats-card ul {
    list-style-type: none;
    padding: 0;
}

.stats-card li {
    margin-bottom: 10px;
}

@media (max-width: 768px) {
    .dashboard-container {
        flex-direction: column;
    }

    .sidebar {
        width: 100%;
    }

    .card-container {
        flex-direction: column;
    }

    .card {
        width: 100%;
        margin-bottom: 20px;
    }

    .stats-card {
        width: 100%;
    }
}
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <nav>
                <ul>
                    <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li><a href="#">Clients</a></li>
                    <li><a href="#">Statistics</a></li>
                </ul>
            </nav>
        </aside>
        <main class="content">
            @yield('content')
        </main>
    </div>
    <script src="{{ asset('js/script.js') }}"></script>
    @yield('scripts')
</body>
</html>

