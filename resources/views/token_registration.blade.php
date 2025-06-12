@extends('layouts.app')

@section('title', 'Enter Your Code')

@section('css')
<style>
    .registration-container {
        max-width: 550px;
        margin: 0 auto;
        padding: 2rem;
        background-color: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .registration-title {
        text-align: center;
        color: var(--primary-color);
        margin-bottom: 1.5rem;
        font-weight: 600;
    }

    .registration-subtitle {
        text-align: center;
        margin-bottom: 2rem;
        color: #666;
    }

    .form-group {
        position: relative;
        margin-bottom: 2rem;
    }

    .form-group input[type="text"] {
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

    .error-message {
        display: none;
        color: #c62828;
        font-size: 0.8rem;
        margin-top: 5px;
    }

    .error-message.show {
        display: block;
    }
</style>
@endsection

@section('content')
<div class="registration-container">
    <h2 class="registration-title">{{ __('Enter Your WiFi Access Code') }}</h2>
    
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p class="registration-subtitle">{{ __('To use your verification code, please provide the following information:') }}</p>

    <form method="POST" action="{{ route('process.token.registration') }}" id="registrationForm">
        @csrf
        
        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" required>
            <span class="error-message" id="fullNameError">Please enter your full name</span>
        </div>
        
        <div class="gender-section">
            <label class="gender-label">Gender</label>
            <div class="radio-group">
                <div class="radio-option">
                    <input type="radio" id="male" name="gender" value="male" {{ old('gender') == 'male' ? 'checked' : '' }} required>
                    <label for="male">Male</label>
                </div>
                <div class="radio-option">
                    <input type="radio" id="female" name="gender" value="female" {{ old('gender') == 'female' ? 'checked' : '' }}>
                    <label for="female">Female</label>
                </div>
            </div>
            <span class="error-message" id="genderError">Please select a gender</span>
        </div>
        
        <!-- Show current MAC address if available for debugging -->
        @if(request('mac') || request('macaddr') || request('client_mac') || isset($mac_address))
        <div style="margin-bottom: 1rem; font-size: 0.8rem; color: #666;">
            {{ __('Device ID') }}: {{ request('mac') ?? request('macaddr') ?? request('client_mac') ?? $mac_address ?? '' }}
        </div>
        @endif
        
        <!-- Hidden MAC field - pass any available MAC parameters -->
        <input type="hidden" name="mac" value="{{ request('mac') ?? request('macaddr') ?? request('client_mac') ?? $mac_address ?? '' }}">
        
        <button type="submit" class="submit-btn">
            {{ __('Continue to Enter Code') }}
        </button>
        
        <a href="{{ url('/wifi') }}" class="btn-link">
            {{ __('Need to register with email instead?') }}
        </a>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('registrationForm');
        const fullNameInput = document.getElementById('full_name');
        const maleRadio = document.getElementById('male');
        const femaleRadio = document.getElementById('female');
        const fullNameError = document.getElementById('fullNameError');
        const genderError = document.getElementById('genderError');
        
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
            
            // Validate gender
            if (!maleRadio.checked && !femaleRadio.checked) {
                genderError.classList.add('show');
                isValid = false;
            } else {
                genderError.classList.remove('show');
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        // Clear error messages on input
        fullNameInput.addEventListener('input', function() {
            fullNameError.classList.remove('show');
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