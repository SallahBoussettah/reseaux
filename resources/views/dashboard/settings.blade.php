@extends('layouts.dashboard')

@section('title', 'Settings')

@section('content')
<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="header-content">
            <h2 class="header-title">Settings</h2>
            <p class="header-subtitle">Manage your application settings</p>
        </div>
    </div>
    
    <ul class="nav-tabs">
        @foreach($settings as $group => $groupSettings)
            <li class="nav-item">
                <button class="nav-link {{ $loop->first ? 'active' : '' }}" 
                        onclick="openTab('{{ $group }}')">
                    {{ ucfirst($group) }}
                </button>
            </li>
        @endforeach
    </ul>

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        @method('PUT')
        
        @foreach($settings as $group => $groupSettings)
            <div id="{{ $group }}" class="tab-content" style="{{ $loop->first ? '' : 'display: none;' }}">
                @if($group == 'emails')
                    <div class="settings-section">
                        <h3>Email Settings</h3>
                        <p>Customize the content of email verification messages sent to users.</p>
                        
                        <div class="preview-actions">
                            <div class="preview-buttons">
                                <div class="dropdown preview-dropdown">
                                    <button type="button" class="preview-button" onclick="togglePreviewOptions()">
                                        <i class="uil uil-eye"></i> Preview Email
                                    </button>
                                    <div class="preview-options" id="previewOptions">
                                        <a href="#" onclick="showEmailPreview('en'); return false;">
                                            <i class="uil uil-language"></i> English
                                        </a>
                                        <a href="#" onclick="showEmailPreview('fr'); return false;">
                                            <i class="uil uil-language"></i> French
                                        </a>
                                    </div>
                                </div>
                                <div class="dropdown test-email-dropdown">
                                    <button type="button" class="test-email-button" onclick="toggleTestEmailOptions()">
                                        <i class="uil uil-envelope-send"></i> Send Test Email
                                    </button>
                                    <div class="test-email-options" id="testEmailOptions">
                                        <a href="{{ route('admin.settings.send-test-email', ['email' => 'boussettahsallah@gmail.com', 'lang' => 'en']) }}" 
                                           onclick="return confirm('Send an English test email to boussettahsallah@gmail.com?')">
                                            <i class="uil uil-language"></i> English
                                        </a>
                                        <a href="{{ route('admin.settings.send-test-email', ['email' => 'boussettahsallah@gmail.com', 'lang' => 'fr']) }}" 
                                           onclick="return confirm('Send a French test email to boussettahsallah@gmail.com?')">
                                            <i class="uil uil-language"></i> French
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="premium-badge">
                                <i class="uil uil-star"></i> Premium Access: <span id="premiumDurationDisplay">7</span> days
                            </div>
                        </div>
                        
                        <div class="dropdown language-dropdown">
                            <button type="button" id="languageButton" class="language-main-button">
                                <i class="uil uil-language"></i> <span id="currentLanguage">English</span>
                            </button>
                            <div class="language-options" id="languageOptions">
                                <a href="#" class="language-option en active" data-language="en" onclick="toggleEmailLanguage('en'); return false;">
                                    <i class="uil uil-language"></i> English
                                </a>
                                <a href="#" class="language-option fr" data-language="fr" onclick="toggleEmailLanguage('fr'); return false;">
                                    <i class="uil uil-language"></i> French
                                </a>
                                <a href="#" class="language-option general" data-language="general" onclick="toggleEmailLanguage('general'); return false;">
                                    <i class="uil uil-setting"></i> General
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="settings-grid">
                    @foreach($groupSettings as $setting)
                        <div class="setting-item 
                            @if($group == 'emails')
                                email-setting
                                @if(str_contains($setting->key, '_en'))
                                    lang-en
                                @elseif(str_contains($setting->key, '_fr'))
                                    lang-fr
                                @else
                                    lang-general
                                @endif
                            @endif">
                            
                            <label for="{{ $setting->key }}">
                                {{ $setting->label }}
                                @if($setting->description)
                                    <i class="uil uil-info-circle tooltip-icon" 
                                       data-bs-toggle="tooltip" 
                                       title="{{ $setting->description }}"></i>
                                @endif
                            </label>
                            
                            <input type="hidden" name="settings[{{ $setting->id }}][key]" value="{{ $setting->key }}">
                            
                            @if($setting->type == 'text')
                                <input type="text" 
                                       class="@if($setting->key == 'email_premium_duration_days') premium-duration-input @endif" 
                                       id="{{ $setting->key }}" 
                                       name="settings[{{ $setting->id }}][value]"
                                       value="{{ $setting->value }}"
                                       @if($setting->key == 'email_premium_duration_days')
                                       oninput="updatePremiumDuration(this.value)"
                                       @endif>
                            @elseif($setting->type == 'color')
                                <div class="color-input">
                                    <input type="color" 
                                           id="{{ $setting->key }}_picker" 
                                           value="{{ $setting->value }}"
                                           onchange="document.getElementById('{{ $setting->key }}').value = this.value">
                                    <input type="text" 
                                           id="{{ $setting->key }}"
                                           name="settings[{{ $setting->id }}][value]"
                                           value="{{ $setting->value }}"
                                           onchange="document.getElementById('{{ $setting->key }}_picker').value = this.value">
                                </div>
                            @elseif($setting->type == 'textarea')
                                <textarea 
                                      id="{{ $setting->key }}" 
                                      name="settings[{{ $setting->id }}][value]"
                                      rows="3">{{ $setting->value }}</textarea>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="form-actions">
            <button type="submit" class="save-button">
                <i class="uil uil-save"></i> Save Changes
            </button>
        </div>
    </form>
</div>

<!-- Email Preview Modal -->
<div class="modal fade" id="emailPreviewModal" tabindex="-1" aria-labelledby="emailPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emailPreviewModalLabel">Email Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="emailPreviewContent" class="border p-3 rounded">
                    <!-- Email preview content will be inserted here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Dashboard-style settings page */
    .dashboard-container {
        padding: 0;
        width: 100%;
    }
    
    .dashboard-header {
        margin-bottom: 1.5rem;
    }
    
    .header-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1F2937;
        margin: 0;
    }
    
    .header-subtitle {
        font-size: 0.95rem;
        color: #6B7280;
        margin-top: 0.25rem;
    }
    
    .nav-tabs {
        display: flex;
        background-color: transparent;
        border: none;
        margin-bottom: 1.5rem;
        padding: 0;
        list-style: none;
        gap: 0.5rem;
    }
    
    .nav-tabs .nav-item {
        margin: 0;
    }
    
    .nav-tabs .nav-link {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        color: #6B7280;
        background-color: white;
        border: none;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .nav-tabs .nav-link:hover {
        background-color: #F3F4F6;
    }
    
    .nav-tabs .nav-link.active {
        background-color: #3B82F6;
        color: white;
    }
    
    .tab-content {
        background-color: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .settings-section {
        margin-bottom: 2rem;
    }
    
    .settings-section h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1F2937;
        margin-bottom: 0.5rem;
    }
    
    .settings-section p {
        font-size: 0.95rem;
        color: #6B7280;
        margin-bottom: 1.5rem;
    }
    
    .preview-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    
    .preview-buttons {
        display: flex;
        gap: 0.5rem;
    }
    
    .preview-button {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.2rem;
        background: linear-gradient(135deg, #3B82F6, #2563EB);
        color: white;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
    }
    
    .preview-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .preview-dropdown {
        position: relative;
        display: inline-block;
    }
    
    .preview-options {
        display: none;
        position: absolute;
        background-color: white;
        min-width: 160px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 10;
        border-radius: 8px;
        top: 100%;
        right: 0;
        margin-top: 0.5rem;
    }
    
    .preview-options a {
        color: #333;
        padding: 12px 16px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }
    
    .preview-options a:hover {
        background-color: #f1f1f1;
        border-radius: 8px;
    }
    
    .preview-options.show {
        display: block;
    }
    
    .test-email-button {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.2rem;
        background: linear-gradient(135deg, #10B981, #059669);
        color: white;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
    }
    
    .test-email-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        color: white;
        text-decoration: none;
    }
    
    .test-email-dropdown {
        position: relative;
        display: inline-block;
    }
    
    .test-email-options {
        display: none;
        position: absolute;
        background-color: white;
        min-width: 160px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 10;
        border-radius: 8px;
        top: 100%;
        right: 0;
        margin-top: 0.5rem;
    }
    
    .test-email-options a {
        color: #333;
        padding: 12px 16px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }
    
    .test-email-options a:hover {
        background-color: #f1f1f1;
        border-radius: 8px;
    }
    
    .test-email-options.show {
        display: block;
    }
    
    .premium-badge {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, #8B5CF6, #6366F1);
        color: white;
        border-radius: 8px;
        font-weight: 500;
    }
    
    .language-buttons {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }
    
    .language-buttons button {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        transition: all 0.2s;
        opacity: 0.7;
    }
    
    .language-buttons button:hover {
        transform: translateY(-2px);
    }
    
    .language-buttons button.active {
        opacity: 1;
    }
    
    .language-buttons button.en {
        background-color: #10B981;
        color: white;
    }
    
    .language-buttons button.fr {
        background-color: #3B82F6;
        color: white;
    }
    
    .language-buttons button.general {
        background-color: #F59E0B;
        color: white;
    }
    
    .settings-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }
    
    .setting-item {
        margin-bottom: 1.5rem;
    }
    
    .setting-item label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #374151;
    }
    
    .setting-item input[type="text"],
    .setting-item input[type="color"],
    .setting-item textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: all 0.2s;
        background-color: white;
    }
    
    .setting-item input[type="text"]:focus,
    .setting-item textarea:focus {
        border-color: #3B82F6;
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
    }
    
    .color-input {
        display: flex;
        gap: 0.5rem;
    }
    
    .color-input input[type="color"] {
        width: 50px;
        padding: 0;
        height: 42px;
    }
    
    .tooltip-icon {
        margin-left: 0.5rem;
        color: #9CA3AF;
        cursor: help;
    }
    
    .form-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 1.5rem;
    }
    
    .save-button {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #3B82F6, #2563EB);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .save-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    /* Email settings language indicators */
    .lang-en, .lang-fr, .lang-general {
        position: relative;
    }
    
    .lang-en::before, 
    .lang-fr::before, 
    .lang-general::before {
        content: '';
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        position: absolute;
        left: -15px;
        top: 10px;
    }
    
    .lang-en::before {
        background-color: #10B981;
    }
    
    .lang-fr::before {
        background-color: #3B82F6;
    }
    
    .lang-general::before {
        background-color: #F59E0B;
    }
    
    /* Textarea styling */
    textarea {
        min-height: 100px;
        resize: vertical;
    }
    
    .language-dropdown {
        position: relative;
        display: inline-block;
        margin-bottom: 1.5rem;
    }
    
    .language-main-button {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #10B981, #059669);
        color: white;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        transition: all 0.2s;
    }
    
    .language-main-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .language-options {
        display: none;
        position: absolute;
        background-color: white;
        min-width: 200px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 10;
        border-radius: 8px;
        top: 100%;
        left: 0;
        margin-top: 0.5rem;
    }
    
    .language-options.show {
        display: block;
    }
    
    .language-option {
        color: #333;
        padding: 12px 16px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
        opacity: 0.7;
    }
    
    .language-option:hover {
        background-color: #f1f1f1;
        border-radius: 8px;
    }
    
    .language-option.active {
        opacity: 1;
        font-weight: 500;
    }
    
    .language-option.en i {
        color: #10B981;
    }
    
    .language-option.fr i {
        color: #3B82F6;
    }
    
    .language-option.general i {
        color: #F59E0B;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Update text input when color picker changes
        document.querySelectorAll('input[type="color"]').forEach(function(colorPicker) {
            colorPicker.addEventListener('input', function() {
                let textInputId = this.id.replace('_picker', '');
                let textInput = document.getElementById(textInputId);
                if (textInput) {
                    textInput.value = this.value;
                }
            });
        });

        // Update color picker when text input changes
        document.querySelectorAll('input[id$="_picker"]').forEach(function(colorPicker) {
            const textInputId = colorPicker.id.replace('_picker', '');
            const textInput = document.getElementById(textInputId);
            
            if (textInput) {
                textInput.addEventListener('input', function() {
                    colorPicker.value = this.value;
                });
            }
        });
        
        // Initialize premium duration display
        const durationInput = document.querySelector('.premium-duration-input');
        if (durationInput) {
            updatePremiumDuration(durationInput.value);
        }

        // Setup listeners for email settings fields to enable live preview
        document.querySelectorAll('.email-setting input, .email-setting textarea').forEach(function(input) {
            input.addEventListener('input', function() {
                // If this is the premium duration field, update the display
                if (this.id === 'email_premium_duration_days') {
                    updatePremiumDuration(this.value);
                }
            });
        });
        
        // Add direct event listeners for the dropdown buttons
        document.getElementById('languageButton').addEventListener('click', function(e) {
            e.preventDefault();
            toggleLanguageOptions();
        });
        
        // Initialize with English language selected by default
        toggleEmailLanguage('en');
    });
    
    // Tab navigation
    function openTab(tabName) {
        // Hide all tab contents
        var tabContents = document.getElementsByClassName('tab-content');
        for (var i = 0; i < tabContents.length; i++) {
            tabContents[i].style.display = 'none';
        }
        
        // Show the selected tab content
        document.getElementById(tabName).style.display = 'block';
        
        // Update active tab
        var tabs = document.getElementsByClassName('nav-link');
        for (var i = 0; i < tabs.length; i++) {
            tabs[i].classList.remove('active');
        }
        
        // Find and activate the clicked tab
        var activeTabs = document.querySelectorAll('.nav-link');
        for (var i = 0; i < activeTabs.length; i++) {
            if (activeTabs[i].getAttribute('onclick') === `openTab('${tabName}')`) {
                activeTabs[i].classList.add('active');
            }
        }
    }
    
    function toggleEmailLanguage(language) {
        // Update the active button
        document.querySelectorAll('.language-option').forEach(function(btn) {
            if (btn.getAttribute('data-language') === language) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        // Update current language display
        let languageText = 'English';
        if (language === 'fr') {
            languageText = 'French';
        } else if (language === 'general') {
            languageText = 'General';
        }
        document.getElementById('currentLanguage').textContent = languageText;
        
        // Hide all email settings first
        document.querySelectorAll('.email-setting').forEach(function(el) {
            el.style.display = 'none';
        });
        
        // Show settings based on selected language
        document.querySelectorAll('.lang-' + language).forEach(function(el) {
            el.style.display = 'block';
        });
        
        // Hide the dropdown after selection
        document.getElementById('languageOptions').classList.remove('show');
    }
    
    function updatePremiumDuration(value) {
        document.getElementById('premiumDurationDisplay').textContent = value;
    }
    
    function showEmailPreview(language) {
        let emailContent = '';
        const buttonColor = document.getElementById('secondary_color')?.value || '#4CAF50';
        const sampleToken = '123456';
        
        if (language === 'en') {
            const heading = document.getElementById('email_verification_heading_en')?.value || 'Your WiFi Verification Code';
            const greeting = document.getElementById('email_verification_greeting_en')?.value || 'Hello,';
            const intro = document.getElementById('email_verification_intro_en')?.value || 'Thank you for using our WiFi service.';
            const expiry = document.getElementById('email_verification_expiry_text_en')?.value || 'This code is valid for 15 minutes.';
            const buttonIntro = document.getElementById('email_verification_button_intro_en')?.value || 'Click the button below:';
            const buttonText = document.getElementById('email_verification_button_text_en')?.value || 'Verify My Code';
            const footer = document.getElementById('email_verification_footer_en')?.value || 'If you didn\'t request this code, please ignore this email.';
            
            emailContent = generateEmailHTML(heading, greeting, intro, sampleToken, expiry, buttonIntro, buttonText, footer, buttonColor);
        } else {
            const heading = document.getElementById('email_verification_heading_fr')?.value || 'Votre code de vérification WiFi';
            const greeting = document.getElementById('email_verification_greeting_fr')?.value || 'Bonjour,';
            const intro = document.getElementById('email_verification_intro_fr')?.value || 'Merci d\'avoir utilisé notre service WiFi.';
            const expiry = document.getElementById('email_verification_expiry_text_fr')?.value || 'Ce code est valable pendant 15 minutes.';
            const buttonIntro = document.getElementById('email_verification_button_intro_fr')?.value || 'Cliquez sur le bouton ci-dessous:';
            const buttonText = document.getElementById('email_verification_button_text_fr')?.value || 'Vérifier mon code';
            const footer = document.getElementById('email_verification_footer_fr')?.value || 'Si vous n\'avez pas demandé ce code, veuillez ignorer cet email.';
            
            emailContent = generateEmailHTML(heading, greeting, intro, sampleToken, expiry, buttonIntro, buttonText, footer, buttonColor);
        }
        
        document.getElementById('emailPreviewContent').innerHTML = emailContent;
        
        // Show the modal
        const emailPreviewModal = new bootstrap.Modal(document.getElementById('emailPreviewModal'));
        emailPreviewModal.show();
    }
    
    function generateEmailHTML(heading, greeting, intro, token, expiry, buttonIntro, buttonText, footer, buttonColor) {
        return `
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <div style="text-align: center; padding: 10px; background-color: #f8f9fa; border-radius: 5px; margin-bottom: 20px;">
                    <h2>${heading}</h2>
                </div>
                
                <p>${greeting}</p>
                
                <p>${intro}</p>
                
                <div style="font-size: 24px; font-weight: bold; text-align: center; padding: 15px; background-color: #f0f0f0; border-radius: 5px; margin: 20px 0; letter-spacing: 5px;">
                    ${token}
                </div>
                
                <p>${expiry}</p>
                
                <div style="text-align: center; margin: 20px 0;">
                    <p>${buttonIntro}</p>
                    <a href="#" style="display: inline-block; padding: 10px 20px; background-color: ${buttonColor}; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
                        ${buttonText}
                    </a>
                </div>
                
                <p>${footer}</p>
                
                <div style="margin-top: 30px; font-size: 12px; color: #777; text-align: center;">
                    <p>&copy; ${new Date().getFullYear()} Eureka Digital. All rights reserved.</p>
                </div>
            </div>
        `;
    }
    
    function toggleTestEmailOptions() {
        document.getElementById('testEmailOptions').classList.toggle('show');
    }
    
    function togglePreviewOptions() {
        document.getElementById('previewOptions').classList.toggle('show');
    }
    
    function toggleLanguageOptions() {
        // Close any other open dropdowns first
        document.getElementById('previewOptions')?.classList.remove('show');
        document.getElementById('testEmailOptions')?.classList.remove('show');
        
        // Toggle the language options dropdown
        const languageOptions = document.getElementById('languageOptions');
        if (languageOptions) {
            languageOptions.classList.toggle('show');
        }
    }
    
    // Close the dropdowns if the user clicks outside of them
    window.addEventListener('click', function(event) {
        // Language dropdown - close if click is outside the dropdown or button
        if (!event.target.matches('#languageButton') && 
            !event.target.closest('#languageButton') && 
            !event.target.closest('#languageOptions')) {
            var languageDropdown = document.getElementById('languageOptions');
            if (languageDropdown && languageDropdown.classList.contains('show')) {
                languageDropdown.classList.remove('show');
            }
        }
        
        // Test Email dropdown
        if (!event.target.matches('.test-email-button') && 
            !event.target.closest('.test-email-button')) {
            var testEmailDropdown = document.getElementById('testEmailOptions');
            if (testEmailDropdown && testEmailDropdown.classList.contains('show')) {
                testEmailDropdown.classList.remove('show');
            }
        }
        
        // Preview dropdown
        if (!event.target.matches('.preview-button') && 
            !event.target.closest('.preview-button')) {
            var previewDropdown = document.getElementById('previewOptions');
            if (previewDropdown && previewDropdown.classList.contains('show')) {
                previewDropdown.classList.remove('show');
            }
        }
    });
</script>
@endsection 