@extends('layouts.app')

@section('title', 'Verify Your Email')

@section('css')
<style>
    .verification-container {
        max-width: 550px;
        margin: 0 auto;
        padding: 2rem;
        background-color: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .verification-title {
        text-align: center;
        color: var(--primary-color);
        margin-bottom: 1.5rem;
        font-weight: 600;
    }

    .verification-subtitle {
        text-align: center;
        margin-bottom: 2rem;
        color: #666;
    }

    .code-input-group {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 2rem;
    }

    .code-input {
        width: 50px;
        height: 60px;
        text-align: center;
        font-size: 1.5rem;
        font-weight: bold;
        border: 2px solid #ddd;
        border-radius: 8px;
        transition: all 0.3s;
    }

    .code-input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 2px rgba(146, 227, 169, 0.3);
        outline: none;
    }

    .btn-verify {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s;
        display: block;
        margin: 0 auto;
    }

    .btn-verify:hover {
        background-color: #5753D9;
    }

    .btn-link {
        display: block;
        text-align: center;
        margin-top: 1rem;
        color: #666;
        text-decoration: none;
    }

    .btn-link:hover {
        color: var(--primary-color);
    }

    .free-access-section {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #eee;
    }

    .free-access-title {
        color: var(--secondary-color);
        margin-bottom: 1rem;
    }

    .mac-address {
        background-color: #f5f5f5;
        padding: 8px 15px;
        border-radius: 6px;
        display: inline-block;
        font-family: monospace;
        margin: 5px 0;
    }

    .connect-btn {
        background-color: var(--secondary-color);
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s;
        display: inline-block;
        margin-top: 1rem;
        text-decoration: none;
    }

    .connect-btn:hover {
        background-color: #3d8b40;
    }

    .alert {
        padding: 12px 20px;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }

    .alert-danger {
        background-color: #ffebee;
        color: #c62828;
        border-left: 4px solid #c62828;
    }
</style>
@endsection

@section('content')
<div class="verification-container">
    <h2 class="verification-title">
        @if(isset($token_registration))
            {{ __('Enter Your WiFi Access Code') }}
        @else
            {{ __('Verify Your Email') }}
        @endif
    </h2>
    
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->has('token'))
        <div class="alert alert-danger">
            {{ $errors->first('token') }}
        </div>
    @endif

    <p class="verification-subtitle">
        @if(isset($token_registration))
            @if(isset($full_name))
                {{ __('Hi') }} {{ $full_name }}! {{ __('Please enter the 6-digit access code you received to get WiFi access.') }}
            @else
                {{ __('Please enter the 6-digit access code you received to get WiFi access.') }}
            @endif
        @else
            {{ __('Please enter the 6-digit verification code sent to your email. This code is valid for 15 minutes.') }}
        @endif
    </p>

    @if(isset($attempts_remaining))
    <div class="attempts-info" style="text-align: center; margin-bottom: 1rem; color: #666;">
        <p>{{ __('You have') }} <strong>{{ $attempts_remaining }}</strong> {{ __('attempts remaining') }}</p>
    </div>
    @endif

    @if(isset($client) && isset($client->successful_verifications))
    <div class="successful-verifications" style="text-align: center; margin-bottom: 1rem; background-color: #e8f5e9; padding: 10px; border-radius: 8px; color: #2e7d32;">
        <p>{{ __('This token has been used on') }} <strong>{{ $client->successful_verifications }}</strong> {{ __('device(s)') }}</p>
        <p>{{ __('You can still use it on') }} <strong>{{ 5 - $client->successful_verifications }}</strong> {{ __('more device(s)') }}</p>
    </div>
    @endif

    <form method="POST" action="{{ route('verify.token') }}" id="verificationForm">
        @csrf
        
        <div class="code-input-group">
            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="one-time-code" required>
            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
            <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
        </div>
        
        <input type="hidden" id="token" name="token" value="{{ old('token') }}">
        
        <button type="submit" class="btn-verify">
            {{ __('Verify Code') }}
        </button>
        
        <a href="{{ url('/wifi') }}" class="btn-link">
            @if(isset($token_registration))
                {{ __('Need to register with email instead?') }}
            @else
                {{ __('Need a new code?') }}
            @endif
        </a>
    </form>

    @if(session('mac_address'))
    <div class="free-access-section">
        <h4 class="free-access-title">{{ __('Get 5-minute free access while waiting for your code') }}</h4>
        
        <p>{{ __('Your device MAC address:') }} <span class="mac-address">{{ session('mac_address') }}</span></p>
        
        <p>{{ __('To connect to WiFi:') }}</p>
        <ol>
            <li>{{ __('Connect to the WiFi network') }}</li>
            <li>{{ __('When the login page appears, enter:') }}
                <ul>
                    <li>{{ __('Username:') }} <strong>{{ session('mac_address') }}</strong></li>
                    <li>{{ __('Password:') }} <strong>123456789</strong></li>
                </ul>
            </li>
        </ol>
        
        @if(session('login_url'))
            <a href="{{ session('login_url') }}" class="connect-btn">
                {{ __('Connect to WiFi (5 minutes)') }}
            </a>
        @endif
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const codeInputs = document.querySelectorAll('.code-input');
        const tokenInput = document.getElementById('token');
        const form = document.getElementById('verificationForm');
        
        // Auto-focus the first input
        codeInputs[0].focus();
        
        // Handle input in code fields
        codeInputs.forEach((input, index) => {
            // Auto-advance to next field
            input.addEventListener('input', function(e) {
                if (this.value.length === 1) {
                    if (index < codeInputs.length - 1) {
                        codeInputs[index + 1].focus();
                    }
                }
                updateTokenValue();
            });
            
            // Handle backspace
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value.length === 0 && index > 0) {
                    codeInputs[index - 1].focus();
                }
            });
            
            // Ensure only numbers
            input.addEventListener('keypress', function(e) {
                if (e.key < '0' || e.key > '9') {
                    e.preventDefault();
                }
            });
            
            // Handle paste event
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasteData = e.clipboardData.getData('text').trim();
                
                // If pasted data is a 6-digit number, distribute it across inputs
                if (/^\d{6}$/.test(pasteData)) {
                    codeInputs.forEach((input, i) => {
                        input.value = pasteData.charAt(i);
                    });
                    updateTokenValue();
                    codeInputs[5].focus();
                }
            });
        });
        
        // Update the hidden token input with combined values
        function updateTokenValue() {
            let code = '';
            codeInputs.forEach(input => {
                code += input.value;
            });
            tokenInput.value = code;
        }
        
        // Handle form submission
        form.addEventListener('submit', function(e) {
            updateTokenValue();
            
            // Validate that all inputs are filled
            let isValid = true;
            codeInputs.forEach(input => {
                if (input.value.length === 0) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please enter the complete 6-digit verification code.');
            }
        });
        
        // If there's an old token value, distribute it to the inputs
        const oldToken = "{{ old('token') }}";
        if (oldToken && oldToken.length === 6) {
            for (let i = 0; i < 6; i++) {
                codeInputs[i].value = oldToken.charAt(i);
            }
        }
    });
</script>
@endsection