@extends('layouts.app')

@section('title', 'Register')

@section('css')
<link rel="stylesheet" href="{{ secure_asset('css/load.css') }}">
@endsection


@section('content')

    <div class="container">

        <form action="" method="POST" class="register-form" id="creativeForm">
        @csrf
            <h1>Free Wifi</h1>
            <div class="form-group">
                <input type="text" id="full_name" name="full_name" required>
                <label for="fullName">Full Name</label>
                <span class="error-message" id="fullNameError">Please enter your full name</span>
            </div>
            <div class="form-group">
                <input type="email" id="email" name="email" required>
                <label for="email">Email</label>
                <span class="error-message" id="emailError">Please enter a valid email address</span>
            </div>
            <div class="form-group">
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
            <button type="submit" class="submit-btn">Submit</button>
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
            <h2>Succès!</h2>
            <p>Vérifiez votre email pour un accès complet au WiFi.</p>
            <p>Pour 5 minutes d'accès limité, cliquez sur 'Connecter'</p>
            <a href="http://10.5.50.1/login?username=trial&password=trial">
                <button class="submit-btn">
                    Connecter
                </button>
            </a>
        </div>
    </div>

@endsection

@section('scripts')

@endsection
