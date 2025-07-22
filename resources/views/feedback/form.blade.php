<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Share Your Feedback') }}</title>
    <style>
        /* Reset CSS to prevent any inheritance issues */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Custom variables */
        :root {
            --primary-color: {{ \App\Models\Setting::get('primary_color', '#4CAF50') }};
            --secondary-color: {{ \App\Models\Setting::get('secondary_color', '#388E3C') }};
            --background-color: {{ \App\Models\Setting::get('background_color', '#f4f7fe') }};
            --card-bg: #ffffff;
            --text-color: #333333;
            --text-light: #6c757d;
            --border-color: #e0e0e0;
            --star-color: #ffc107;
            --star-color-inactive: #e0e0e0;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
        }

        /* Base styles */
        html, body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--background-color);
            width: 100%;
            height: 100%;
            position: relative;
        }

        /* Main container */
        .page-container {
            width: 100%;
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .feedback-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        .feedback-card {
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        /* Header section */
        .feedback-header {
            padding: 2.5rem 3rem;
            border-bottom: 1px solid var(--border-color);
            text-align: center;
        }

        .feedback-title {
            font-size: 2rem;
            font-weight: 700;
            margin: 0 0 0.5rem;
            color: var(--text-color);
        }

        .feedback-subtitle {
            font-size: 1.1rem;
            color: var(--text-light);
            margin: 0;
        }

        /* Content section */
        .feedback-body {
            padding: 3rem;
        }

        .feedback-section {
            margin-bottom: 3rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 2rem;
            color: var(--text-color);
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--primary-color);
            display: inline-block;
        }

        /* Client info section */
        .client-info-section {
            background-color: rgba(var(--primary-color-rgb, 76, 175, 80), 0.05);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2.5rem;
            border: 1px solid rgba(var(--primary-color-rgb, 76, 175, 80), 0.2);
        }

        .client-info-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--primary-color);
            display: flex;
            align-items: center;
        }

        .client-info-title i {
            margin-right: 0.5rem;
        }

        .client-info-row {
            display: flex;
            margin-bottom: 0.75rem;
        }

        .client-info-label {
            font-weight: 500;
            width: 120px;
            color: var(--text-color);
        }

        .client-info-value {
            color: var(--text-color);
            flex: 1;
        }

        /* Rating groups */
        .rating-group {
            margin-bottom: 2.5rem;
        }

        .rating-question {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
        }

        /* Star rating system - completely rebuilt */
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-start;
            margin-bottom: 1rem;
        }

        /* Hide the radio inputs */
        .star-rating input[type="radio"] {
            position: absolute;
            left: -9999px;
        }

        /* Style the star labels */
        .star-rating label {
            cursor: pointer;
            font-size: 2.5rem;
            color: var(--star-color-inactive);
            margin-right: 0.5rem;
            transition: color 0.2s ease-in-out;
        }

        /* When checked or hovered */
        .star-rating input[type="radio"]:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: var(--star-color);
        }

        /* Binary options (Yes/No) */
        .binary-group {
            margin-bottom: 2.5rem;
        }

        .binary-options {
            display: flex;
            gap: 1rem;
        }

        .binary-option {
            flex: 1;
            max-width: 300px;
        }

        /* Hide original radio buttons */
        .binary-option input[type="radio"] {
            position: absolute;
            left: -9999px;
        }

        /* Style the custom buttons */
        .binary-option label {
            display: block;
            width: 100%;
            padding: 1rem;
            text-align: center;
            background-color: #fff;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        /* Selected state */
        .binary-option input[type="radio"]:checked + label {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        /* Hover state */
        .binary-option label:hover {
            border-color: var(--primary-color);
        }

        /* Form controls */
        .form-group {
            margin-bottom: 2rem;
        }

        .form-label {
            display: block;
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.2s ease;
            resize: vertical;
            min-height: 120px;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
        }

        /* Submit button */
        .submit-container {
            text-align: right;
            margin-top: 3rem;
        }

        .btn-submit {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 1rem 2.5rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .btn-submit:hover {
            background-color: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        /* Alert messages */
        .alert {
            padding: 1rem;
            margin-bottom: 2rem;
            border-radius: 8px;
        }

        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .alert ul {
            margin: 0.5rem 0 0 1.5rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .feedback-header, .feedback-body {
                padding: 2rem;
            }
        }

        @media (max-width: 576px) {
            .binary-options {
                flex-direction: column;
            }

            .binary-option {
                max-width: 100%;
            }

            .feedback-header, .feedback-body {
                padding: 1.5rem;
            }

            .submit-container {
                text-align: center;
            }

            .btn-submit {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="feedback-container">
            <div class="feedback-card">
                <div class="feedback-header">
                    <h1 class="feedback-title">{{ __('We Value Your Feedback') }}</h1>
                    <p class="feedback-subtitle">{{ __('Please share your experience to help us improve our services.') }}</p>
                </div>
                
                <div class="feedback-body">
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>{{ __('Please check the following errors:') }}</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    <!-- Client Information Section -->
                    @if($client)
                    <div class="client-info-section">
                        <div class="client-info-title">
                            <i class="uil uil-user"></i> {{ __('Your Information') }}
                        </div>
                        <div class="client-info-row">
                            <div class="client-info-label">{{ __('Name') }}:</div>
                            <div class="client-info-value">{{ $client->full_name }}</div>
                        </div>
                        <div class="client-info-row">
                            <div class="client-info-label">{{ __('Email') }}:</div>
                            <div class="client-info-value">{{ $client->email }}</div>
                        </div>
                    </div>
                    @endif
                    
                    <form action="{{ route('feedback.store') }}" method="POST">
                        @csrf
                        
                        @if(isset($token) && isset($email))
                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="email" value="{{ $email }}">
                        @endif
                        
                        <div class="feedback-section">
                            <h2 class="section-title">{{ __('Rate Your Experience') }}</h2>
                            
                            @php
                            $ratings = [
                                'wifi_rating' => 'How would you rate the WiFi service?',
                                'hotel_rating' => 'How would you rate your overall hotel experience?',
                                'room_rating' => 'How would you rate the comfort and cleanliness of your room?',
                                'service_rating' => 'How would you rate our staff and service?',
                                'food_rating' => 'How would you rate the food and dining experience?',
                            ];
                            @endphp

                            @foreach($ratings as $name => $question)
                            <div class="rating-group">
                                <p class="rating-question">{{ __($question) }}</p>
                                <div class="star-rating">
                                    @for ($i = 5; $i >= 1; $i--)
                                    <input type="radio" id="{{ $name }}-{{ $i }}" name="{{ $name }}" value="{{ $i }}" {{ old($name) == $i ? 'checked' : '' }} required>
                                    <label for="{{ $name }}-{{ $i }}" title="{{ $i }} stars">★</label>
                                    @endfor
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <div class="feedback-section">
                            <h2 class="section-title">{{ __('Your Satisfaction') }}</h2>
                            
                            <div class="binary-group">
                                <p class="rating-question">{{ __('Are you satisfied with your overall stay?') }}</p>
                                <div class="binary-options">
                                    <div class="binary-option">
                                        <input type="radio" id="satisfied-yes" name="is_satisfied" value="1" {{ old('is_satisfied', '1') == '1' ? 'checked' : '' }} required>
                                        <label for="satisfied-yes">{{ __('Yes, Satisfied') }}</label>
                                    </div>
                                    <div class="binary-option">
                                        <input type="radio" id="satisfied-no" name="is_satisfied" value="0" {{ old('is_satisfied') == '0' ? 'checked' : '' }}>
                                        <label for="satisfied-no">{{ __('No, Unsatisfied') }}</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="binary-group">
                                <p class="rating-question">{{ __('Would you visit our hotel again?') }}</p>
                                <div class="binary-options">
                                    <div class="binary-option">
                                        <input type="radio" id="visit-yes" name="visit_again" value="1" {{ old('visit_again', '1') == '1' ? 'checked' : '' }} required>
                                        <label for="visit-yes">{{ __('Yes') }}</label>
                                    </div>
                                    <div class="binary-option">
                                        <input type="radio" id="visit-no" name="visit_again" value="0" {{ old('visit_again') == '0' ? 'checked' : '' }}>
                                        <label for="visit-no">{{ __('No') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="feedback-section">
                            <h2 class="section-title">{{ __('Additional Comments') }}</h2>
                            
                            <div class="form-group">
                                <label class="form-label" for="comments">{{ __('Share your thoughts about your stay') }}</label>
                                <textarea class="form-control" id="comments" name="comments" rows="5" placeholder="{{ __('Please share any additional thoughts about your stay...') }}">{{ old('comments') }}</textarea>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="suggestion">{{ __('How can we improve your experience?') }}</label>
                                <textarea class="form-control" id="suggestion" name="suggestion" rows="5" placeholder="{{ __('Your suggestions help us serve you better...') }}">{{ old('suggestion') }}</textarea>
                            </div>
                        </div>
                        
                        <div class="submit-container">
                            <button type="submit" class="btn-submit">{{ __('Submit Feedback') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 