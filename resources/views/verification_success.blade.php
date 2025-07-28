<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Verification Successful - Aqua Mirage Marrakech</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                margin: 0;
                padding: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .container {
                background: white;
                border-radius: 15px;
                padding: 40px;
                max-width: 500px;
                text-align: center;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            }
            .success-icon {
                font-size: 4rem;
                color: #10B981;
                margin-bottom: 20px;
            }
            h1 {
                color: #1F2937;
                margin-bottom: 15px;
                font-size: 1.8rem;
            }
            .message {
                color: #10B981;
                font-size: 1.1rem;
                margin-bottom: 20px;
                font-weight: 600;
            }
            .instructions {
                color: #6B7280;
                font-size: 1rem;
                line-height: 1.6;
                margin-bottom: 30px;
                padding: 20px;
                background: #F9FAFB;
                border-radius: 10px;
                border-left: 4px solid #10B981;
            }
            .profile-info {
                background: #EFF6FF;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 20px;
                border-left: 4px solid #3B82F6;
            }
            .refresh-btn {
                background: #10B981;
                color: white;
                border: none;
                padding: 12px 30px;
                border-radius: 8px;
                font-size: 1rem;
                cursor: pointer;
                transition: background 0.3s;
            }
            .refresh-btn:hover {
                background: #059669;
            }
            .auto-refresh {
                margin-top: 20px;
                color: #6B7280;
                font-size: 0.9rem;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="success-icon">✅</div>
            
            <h1>Verification Successful!</h1>
            
            @if (session('message'))
                <div class="message">
                    {{ session('message') }}
                </div>
            @endif

            @if (session('instructions'))
                <div class="instructions">
                    <strong>Next Steps:</strong><br>
                    {{ session('instructions') }}
                </div>
            @endif

            @if (session('profile'))
                <div class="profile-info">
                    <strong>Your Profile:</strong> {{ ucfirst(str_replace('_', ' ', session('profile'))) }}<br>
                    @if (session('mac_address'))
                        <strong>Device:</strong> {{ session('mac_address') }}
                    @endif
                </div>
            @endif

            <button class="refresh-btn" onclick="window.location.reload()">
                🔄 Refresh Page
            </button>

            <div class="auto-refresh">
                <small>This page will automatically refresh in <span id="countdown">10</span> seconds</small>
            </div>
        </div>

        <script>
            // Auto refresh after 10 seconds
            let countdown = 10;
            const countdownElement = document.getElementById('countdown');
            
            const timer = setInterval(() => {
                countdown--;
                countdownElement.textContent = countdown;
                
                if (countdown <= 0) {
                    clearInterval(timer);
                    window.location.reload();
                }
            }, 1000);
        </script>
    </body>
</html>
