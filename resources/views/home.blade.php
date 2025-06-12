@extends('layouts.app')

@section('title', 'WiFi Access Registration')

@section('css')
<style>
    .wifi-container {
        max-width: 550px;
        margin: 0 auto;
        padding: 2rem;
        background-color: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .wifi-title {
        text-align: center;
        color: var(--primary-color);
        margin-bottom: 1.5rem;
        font-weight: 600;
    }

    .wifi-subtitle {
        color: #666;
        margin-bottom: 1.5rem;
    }

    .existing-code {
        background-color: #f9f9f9;
        padding: 1.5rem;
        border-radius: 10px;
        margin-bottom: 2rem;
        text-align: center;
    }

    .existing-code h5 {
        margin-bottom: 1rem;
        color: #555;
    }

    .divider {
        height: 1px;
        background-color: #eee;
        margin: 2rem 0;
        position: relative;
    }

    .divider::before {
        content: 'OR';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: white;
        padding: 0 15px;
        color: #999;
        font-size: 0.9rem;
    }

    .form-group {
        position: relative;
        margin-bottom: 2rem;
    }

    .form-group input[type="text"],
    .form-group input[type="email"] {
        width: 100%;
        padding: 12px 15px;
        font-size: 1rem;
        border: 2px solid #ddd;
        border-radius: 8px;
        transition: all 0.3s;
        background-color: #f9f9f9;
    }

    .form-group input:focus {
        border-color: var(--primary-color);
        background-color: #fff;
        box-shadow: 0 0 0 2px rgba(146, 227, 169, 0.3);
        outline: none;
    }

    .form-group label {
        position: absolute;
        top: -10px;
        left: 10px;
        background-color: white;
        padding: 0 5px;
        font-size: 0.9rem;
        color: #666;
        transition: all 0.3s;
    }

    .gender-section {
        margin-bottom: 2rem;
    }

    .gender-label {
        display: block;
        margin-bottom: 0.8rem;
        color: #666;
        font-size: 0.9rem;
    }

    .radio-group {
        display: flex;
        gap: 20px;
    }

    .radio-option {
        display: flex;
        align-items: center;
        cursor: pointer;
    }

    .radio-option input[type="radio"] {
        margin-right: 8px;
        cursor: pointer;
        accent-color: var(--primary-color);
        width: 18px;
        height: 18px;
    }

    .radio-option label {
        cursor: pointer;
        color: #555;
    }

    .submit-btn {
        width: 100%;
        padding: 12px;
        background-color: var(--primary-color);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s;
        margin-top: 1rem;
    }

    .submit-btn:hover {
        background-color: #5753D9;
    }

    .code-btn {
        background-color: var(--secondary-color);
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.3s;
        text-decoration: none;
        display: inline-block;
    }

    .code-btn:hover {
        background-color: #3d8b40;
    }

    .alert {
        padding: 12px 20px;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }

    .alert-success {
        background-color: #e8f5e9;
        color: #2e7d32;
        border-left: 4px solid #2e7d32;
    }

    .error-message {
        display: none;
        color: #c62828;
        font-size: 0.8rem;
        margin-top: 5px;
    }

    .error-message.show {
        display: block;
    }

    /* Loader styles */
    .loader-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .success-card {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        text-align: center;
        z-index: 1001;
        max-width: 400px;
        width: 90%;
    }

    .success-card h2 {
        color: var(--secondary-color);
        margin-bottom: 1rem;
    }

    .verification-link {
        color: var(--primary-color);
        font-weight: 500;
        text-decoration: underline;
        word-break: break-all;
    }

    .verification-link:hover {
        color: #5753D9;
    }

    .hidden {
        display: none !important;
    }
</style>
<link rel="stylesheet" href="{{ secure_asset('css/load.css') }}">
@endsection

@section('content')
<div class="wifi-container">
    <h2 class="wifi-title">{{ __('WiFi Access') }}</h2>

    <div class="existing-code">
        <h5>{{ __('Already have a verification code?') }}</h5>
        <a href="{{ route('token.registration') }}" class="code-btn">
            {{ __('Enter Your Code') }}
        </a>
    </div>

    <div class="divider"></div>

    <h5 class="wifi-subtitle">{{ __('Need access? Register below:') }}</h5>
    
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('wifiaccess') }}" id="wifiForm">
        @csrf

        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" required>
            <span class="error-message" id="fullNameError">Please enter your full name</span>
        </div>
        
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>
            <span class="error-message" id="emailError">Please enter a valid email address</span>
        </div>
        
        <div class="gender-section">
            <label class="gender-label">Gender</label>
            <div class="radio-group">
                <div class="radio-option">
                    <input type="radio" id="male" name="gender" value="male" required>
                    <label for="male">Male</label>
                </div>
                <div class="radio-option">
                    <input type="radio" id="female" name="gender" value="female">
                    <label for="female">Female</label>
                </div>
            </div>
            <span class="error-message" id="genderError">Please select a gender</span>
        </div>
        
        <input type="hidden" name="mac" value="{{ request('mac') }}">
        <button type="submit" class="submit-btn">Register for WiFi Access</button>
    </form>
    
    <div class="loader-container hidden">
        <div class="loader">
            <div class="loader__bar"></div>
            <div class="loader__bar"></div>
            <div class="loader__bar"></div>
            <div class="loader__bar"></div>
            <div class="loader__bar"></div>
            <div class="loader__ball"></div>
        </div>
    </div>
    
    <div id="successCard" class="success-card hidden">
        <h2>Success!</h2>
        <p>Check your email for the verification code to get full WiFi access.</p>
        <p>Enter your code at: <a href="{{ route('token.verification') }}" class="verification-link">{{ route('token.verification') }}</a></p>
        <p>For 5 minutes of limited access, click 'Connect'</p>
        <a href="{{ route('redirect2') }}">
            <button class="submit-btn">Connect</button>
        </a>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('wifiForm');
        const fullNameInput = document.getElementById('full_name');
        const emailInput = document.getElementById('email');
        const maleRadio = document.getElementById('male');
        const femaleRadio = document.getElementById('female');
        const fullNameError = document.getElementById('fullNameError');
        const emailError = document.getElementById('emailError');
        const genderError = document.getElementById('genderError');
        const loaderContainer = document.querySelector('.loader-container');
        const successCard = document.getElementById('successCard');
        
        // Form validation
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate full name
            if (!fullNameInput.value.trim()) {
                fullNameError.classList.add('show');
                isValid = false;
            } else {
                fullNameError.classList.remove('show');
            }
            
            // Validate email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailInput.value.trim())) {
                emailError.classList.add('show');
                isValid = false;
            } else {
                emailError.classList.remove('show');
            }
            
            // Validate gender
            if (!maleRadio.checked && !femaleRadio.checked) {
                genderError.classList.add('show');
                isValid = false;
            } else {
                genderError.classList.remove('show');
            }
            
            if (!isValid) {
                e.preventDefault();
                return;
            }
            
            // Show loader while form is submitting
            loaderContainer.classList.remove('hidden');
            
            // For demo purposes only - in production this would be handled by the server response
            // setTimeout(function() {
            //     loaderContainer.classList.add('hidden');
            //     form.classList.add('hidden');
            //     successCard.classList.remove('hidden');
            // }, 2000);
        });
        
        // Clear error messages on input
        fullNameInput.addEventListener('input', function() {
            fullNameError.classList.remove('show');
        });
        
        emailInput.addEventListener('input', function() {
            emailError.classList.remove('show');
        });
        
        maleRadio.addEventListener('change', function() {
            genderError.classList.remove('show');
        });
        
        femaleRadio.addEventListener('change', function() {
            genderError.classList.remove('show');
        });
    });
</script>
@endsection
