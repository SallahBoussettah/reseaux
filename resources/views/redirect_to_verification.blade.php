@extends('layouts.app')

@section('title', 'Redirecting to Verification')

@section('css')
<style>
    .redirect-container {
        max-width: 550px;
        margin: 0 auto;
        padding: 2rem;
        background-color: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .redirect-title {
        color: var(--primary-color);
        margin-bottom: 1.5rem;
        font-weight: 600;
    }

    .redirect-message {
        color: #666;
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }

    .redirect-button {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s;
        text-decoration: none;
        display: inline-block;
        margin-top: 1rem;
    }

    .redirect-button:hover {
        background-color: #5753D9;
    }

    .countdown {
        font-size: 1.5rem;
        font-weight: bold;
        color: var(--primary-color);
        margin: 1rem 0;
    }

    .checkmark {
        width: 80px;
        height: 80px;
        margin: 0 auto 1.5rem;
    }

    .checkmark__circle {
        stroke-dasharray: 166;
        stroke-dashoffset: 166;
        stroke-width: 2;
        stroke-miterlimit: 10;
        stroke: var(--primary-color);
        fill: none;
        animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
    }

    .checkmark__check {
        transform-origin: 50% 50%;
        stroke-dasharray: 48;
        stroke-dashoffset: 48;
        animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
    }

    @keyframes stroke {
        100% {
            stroke-dashoffset: 0;
        }
    }
</style>
@endsection

@section('content')
<div class="redirect-container">
    <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
        <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
        <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" stroke="var(--primary-color)" stroke-width="2"/>
    </svg>

    <h2 class="redirect-title">You're Connected!</h2>
    
    <p class="redirect-message">
        You now have 5-minute free WiFi access. Check your email for the verification code to get full access.
    </p>
    
    <p class="redirect-message">
        You will be redirected to the verification page in <span id="countdown" class="countdown">5</span> seconds.
    </p>
    
    <a href="{{ $verificationUrl }}" class="redirect-button">
        Go to Verification Page Now
    </a>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let count = 5;
        const countdownElement = document.getElementById('countdown');
        
        const interval = setInterval(function() {
            count--;
            countdownElement.textContent = count;
            
            if (count <= 0) {
                clearInterval(interval);
                window.location.href = "{{ $verificationUrl }}";
            }
        }, 1000);
    });
</script>
@endsection 