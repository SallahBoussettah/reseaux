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
                        
                        <!-- Logo Upload Section -->
                        <div class="setting-item email-setting lang-visible" style="grid-column: span 2; margin-bottom: 20px;">
                            <label for="email_logo">Email Logo</label>
                            <div class="logo-upload-container">
                                <div class="current-logo">
                                    @if(isset($logo_url) && !empty($logo_url))
                                        <img src="{{ $logo_url }}" alt="Email Logo" id="currentLogoImage" class="logo-preview">
                                    @else
                                        <div class="logo-placeholder" id="logoPlaceholder">
                                            <i class="uil uil-image"></i>
                                            <span>No logo uploaded</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="logo-actions">
                                    <label for="logo_upload" class="logo-upload-btn">
                                        <i class="uil uil-upload"></i> Upload Logo
                                        <input type="file" id="logo_upload" class="hidden-upload" accept="image/*" onchange="handleLogoUpload(this)">
                                    </label>
                                    <button type="button" class="logo-remove-btn" onclick="removeLogo()" @if(!isset($logo_url) || empty($logo_url)) disabled @endif>
                                        <i class="uil uil-trash-alt"></i> Remove
                                    </button>
                                </div>
                                <p class="logo-hint">Recommended size: 200x200px, PNG or JPG format</p>
                                <input type="hidden" id="email_logo_url" name="email_logo_url" value="{{ $logo_url ?? '' }}">
                            </div>
                        </div>
                        
                        <!-- Quick Email Test Section -->
                        <div class="setting-item email-setting lang-visible" style="grid-column: span 2; margin-bottom: 20px;">
                            <label>Quick Email Test</label>
                            <div class="direct-test-buttons" style="background-color: #f9fafb; border-radius: 8px; padding: 15px; border: 1px solid #e5e7eb;">
                                <p style="margin-bottom: 15px; color: #6B7280;">Test email functionality without page refresh. Results will appear below.</p>
                                
                                <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                                    <div class="input-group" style="flex: 1; min-width: 250px;">
                                        <label for="testEmailAddress" style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Test Email Address</label>
                                        <input type="email" id="testEmailAddress" value="boussettahsallah@gmail.com" placeholder="Enter email address" style="width: 100%; padding: 8px 12px; border: 1px solid #E5E7EB; border-radius: 6px;">
                                    </div>
                                    <div class="input-group" style="flex: 0 0 150px;">
                                        <label for="testEmailLanguage" style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Language</label>
                                        <select id="testEmailLanguage" style="width: 100%; padding: 8px 12px; border: 1px solid #E5E7EB; border-radius: 6px; background-color: white;">
                                            <option value="en">English</option>
                                            <option value="fr">French</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div style="display: flex; gap: 10px; margin-top: 15px;">
                                    <button type="button" onclick="sendDirectVerificationEmail()" class="direct-test-button verification" style="padding: 8px 16px; background: linear-gradient(135deg, #3B82F6, #2563EB); color: white; border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                                        <i class="uil uil-envelope-check"></i> Send Verification Email
                                    </button>
                                    <button type="button" onclick="sendDirectFeedbackEmail()" class="direct-test-button feedback" style="padding: 8px 16px; background: linear-gradient(135deg, #10B981, #059669); color: white; border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                                        <i class="uil uil-comment-alt-message"></i> Send Feedback Email
                                    </button>
                                </div>
                                
                                <div id="emailTestResult" style="margin-top: 15px; padding: 10px; border-radius: 6px; display: none;"></div>
                            </div>
                        </div>
                        
                        <!-- Email Type Toggle -->
                        <div class="email-type-toggle">
                            <span class="email-type-label">Email Type:</span>
                            <div class="toggle-buttons">
                                <button type="button" class="email-type-btn active" data-type="verification" onclick="toggleEmailType('verification')">
                                    <i class="uil uil-envelope-check"></i> Verification Email
                                </button>
                                <button type="button" class="email-type-btn" data-type="feedback" onclick="toggleEmailType('feedback')">
                                    <i class="uil uil-comment-alt-message"></i> Feedback Email
                                </button>
                            </div>
                        </div>
                        
                        <div class="preview-actions">
                            <div class="preview-buttons">
                                <div class="dropdown preview-dropdown">
                                    <button type="button" class="preview-button" onclick="togglePreviewOptions()">
                                        <i class="uil uil-eye"></i> Preview Email
                                    </button>
                                    <div class="preview-options" id="previewOptions">
                                        <a href="#" onclick="previewEmail('en'); return false;">
                                            <i class="uil uil-language"></i> English
                                        </a>
                                        <a href="#" onclick="previewEmail('fr'); return false;">
                                            <i class="uil uil-language"></i> French
                                        </a>
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
                            <div class="premium-badge">
                                <i class="uil uil-star"></i> Premium Access: <span id="premiumDurationDisplay">7</span> days
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
                                @if(str_contains($setting->key, '_verification_'))
                                    email-type-verification
                                @elseif(str_contains($setting->key, '_feedback_'))
                                    email-type-feedback
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
    
    /* Adjust language dropdown inside preview-buttons */
    .preview-buttons .language-dropdown {
        margin-left: 10px;
    }
    
    .preview-buttons .language-main-button {
        height: 100%;
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
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
    
    /* Logo upload styling */
    .logo-upload-container {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-top: 10px; 
        max-width: 600px;
    }
    
    .current-logo {
        width: 200px;
        height: 200px;
        border: 2px dashed #E5E7EB;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background-color: #F9FAFB;
        margin-bottom: 10px;
    }
    
    .logo-preview {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    
    .logo-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #9CA3AF;
        gap: 0.5rem;
        width: 100%;
        height: 100%;
    }
    
    .logo-placeholder i {
        font-size: 2.5rem;
    }
    
    .logo-actions {
        display: flex;
        gap: 1rem;
    }
    
    .logo-upload-btn {
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
    
    .logo-remove-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.2rem;
        background: linear-gradient(135deg, #EF4444, #B91C1C);
        color: white;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
    }
    
    .logo-remove-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .logo-upload-btn:hover, .logo-remove-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .hidden-upload {
        display: none;
    }
    
    .logo-hint {
        font-size: 0.85rem;
        color: #6B7280;
        margin-top: 0.5rem;
    }
    
    /* Always show elements with lang-visible class */
    .lang-visible {
        display: block !important;
    }
    
    /* Logo loading styling */
    .logo-loading {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #6B7280;
        gap: 0.5rem;
        width: 100%;
        height: 100%;
    }
    
    .logo-loading i {
        font-size: 2rem;
        color: #3B82F6;
    }
    
    .logo-loading span {
        font-size: 0.9rem;
    }

    /* Email Type Toggle Styles */
    .email-type-toggle {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        padding: 0 15px;
    }
    
    .email-type-label {
        font-weight: 600;
        margin-right: 15px;
        color: #344767;
    }
    
    .toggle-buttons {
        display: flex;
        gap: 10px;
    }
    
    .email-type-btn {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 8px 15px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .email-type-btn i {
        font-size: 16px;
    }
    
    .email-type-btn.active {
        background-color: #4E73F8;
        color: white;
        border-color: #4E73F8;
    }
    
    /* Email Preview Container Styles */
    .email-preview-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1050;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }
    
    .email-preview-container.visible {
        opacity: 1;
        pointer-events: auto;
    }
    
    .preview-header {
        background-color: #fff;
        width: 80%;
        max-width: 800px;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e5e5e5;
    }
    
    .preview-title {
        font-size: 18px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .preview-language-indicator {
        font-size: 12px;
        padding: 5px 10px;
        border-radius: 4px;
        background-color: #f0f0f0;
        color: #666;
        cursor: pointer;
        transition: all 0.2s ease;
        font-weight: 600;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }
    
    .preview-language-indicator:hover {
        background-color: #e0e0e0;
        transform: translateY(-1px);
    }
    
    .preview-language-indicator.active {
        background-color: #4E73F8;
        color: white;
        box-shadow: 0 2px 4px rgba(78, 115, 248, 0.3);
    }
    
    .close-preview {
        background: none;
        border: none;
        font-size: 22px;
        cursor: pointer;
        color: #777;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 4px;
        transition: background-color 0.2s;
    }
    
    .close-preview:hover {
        background-color: #f0f0f0;
    }
    
    .preview-content {
        background-color: #fff;
        width: 80%;
        max-width: 800px;
        height: 70%;
        border-bottom-left-radius: 8px;
        border-bottom-right-radius: 8px;
        overflow: hidden;
    }
    
    .loader {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #4E73F8;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        margin: 0 auto 15px;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Email Type Settings Display */
    .email-type-verification, .email-type-feedback {
        display: none; /* Hidden by default, shown via JS */
    }
    
    /* Always show elements with lang-visible class */
    .lang-visible {
        display: block !important;
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
        
        // Initialize with verification email type selected by default
        toggleEmailType('verification');
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
        
        // Hide all email settings first, except those with lang-visible class
        document.querySelectorAll('.email-setting:not(.lang-visible)').forEach(function(el) {
            el.style.display = 'none';
        });
        
        // Show settings based on selected language
        document.querySelectorAll('.lang-' + language).forEach(function(el) {
            if ((currentEmailType === 'verification' && el.classList.contains('email-type-verification')) || 
                (currentEmailType === 'feedback' && el.classList.contains('email-type-feedback')) || 
                (!el.classList.contains('email-type-verification') && !el.classList.contains('email-type-feedback'))) {
                el.style.display = 'block';
            }
        });
        
        // Hide the dropdown after selection
        document.getElementById('languageOptions').classList.remove('show');
    }
    
    function updatePremiumDuration(value) {
        document.getElementById('premiumDurationDisplay').textContent = value;
    }
    
    // Global variable to track current email type
    let currentEmailType = 'verification';
    
    // Function to toggle between email types
    function toggleEmailType(type) {
        currentEmailType = type;
        
        // Update button styles
        const buttons = document.querySelectorAll('.email-type-btn');
        buttons.forEach(btn => {
            if (btn.dataset.type === type) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        // Show verification email settings and hide feedback email settings or vice versa
        if (type === 'verification') {
            // Show verification email settings and hide feedback email settings
            document.querySelectorAll('.email-type-verification').forEach(el => {
                el.style.display = 'block';
            });
            document.querySelectorAll('.email-type-feedback').forEach(el => {
                el.style.display = 'none';
            });
        } else {
            // Show feedback email settings and hide verification email settings
            document.querySelectorAll('.email-type-verification').forEach(el => {
                el.style.display = 'none';
            });
            document.querySelectorAll('.email-type-feedback').forEach(el => {
                el.style.display = 'block';
            });
        }
        
        // If email preview is open, refresh it with the new type
        const previewContainer = document.getElementById('emailPreview');
        if (previewContainer && previewContainer.style.display === 'block') {
            const selectedLang = document.querySelector('.preview-language.active').dataset.lang;
            previewEmail(selectedLang);
        }
    }
    
    // Update the previewEmail function to include email type with the correct parameter name
    function previewEmail(lang) {
        const previewContainer = document.getElementById('emailPreview');
        const logoUrl = document.getElementById('email_logo_url').value;
        
        if (!previewContainer) {
            // Create preview container if it doesn't exist
            const container = document.createElement('div');
            container.id = 'emailPreview';
            container.className = 'email-preview-container';
            container.innerHTML = `
                <div class="preview-header">
                    <div class="preview-title">
                        Email Preview
                        <span class="preview-language-indicator ${lang === 'en' ? 'active' : ''}" data-lang="en" onclick="switchPreviewLanguage('en')">EN</span>
                        <span class="preview-language-indicator ${lang === 'fr' ? 'active' : ''}" data-lang="fr" onclick="switchPreviewLanguage('fr')">FR</span>
                    </div>
                    <button type="button" class="close-preview" onclick="closePreview()">
                        <i class="uil uil-times"></i>
                    </button>
                </div>
                <div class="preview-content">
                    <iframe id="previewFrame" style="width:100%; height:100%; border:none;"></iframe>
                </div>
            `;
            document.body.appendChild(container);
            setTimeout(() => container.classList.add('visible'), 10);
        } else {
            // Update existing preview container - ensure correct positioning
            previewContainer.style.display = 'flex'; // Use flex instead of block
            previewContainer.style.alignItems = 'center';
            previewContainer.style.justifyContent = 'center';
            setTimeout(() => previewContainer.classList.add('visible'), 10);
            
            const languageIndicators = previewContainer.querySelectorAll('.preview-language-indicator');
            languageIndicators.forEach(indicator => {
                if (indicator.dataset.lang === lang) {
                    indicator.classList.add('active');
                } else {
                    indicator.classList.remove('active');
                }
            });
        }
        
        // Show loading spinner
        const frame = document.getElementById('previewFrame');
        frame.srcdoc = '<div style="display:flex; justify-content:center; align-items:center; height:100%; font-family:Arial, sans-serif;"><div style="text-align:center;"><div class="loader"></div><p>Loading preview...</p></div></div>';
        
        // Make AJAX request to get email preview
        fetch(`/settings/preview-email?language=${lang}&email_type=${currentEmailType}&logo_url=${encodeURIComponent(logoUrl)}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.html) {
                frame.srcdoc = data.html;
            } else {
                const errorMessage = data.message || 'Error loading preview';
                console.error('Preview error:', errorMessage);
                frame.srcdoc = `
                    <div style="padding:20px; color:#e53e3e; font-family:Arial, sans-serif;">
                        <h3 style="margin-bottom:15px;">Error loading preview</h3>
                        <p style="margin-bottom:10px;">There was a problem loading the email preview:</p>
                        <div style="background-color:#fef2f2; border-left:4px solid #e53e3e; padding:10px; margin-bottom:15px;">
                            ${errorMessage}
                        </div>
                        <p><strong>Email Type:</strong> ${currentEmailType}</p>
                        <p><strong>Language:</strong> ${lang}</p>
                        <p style="margin-top:15px;">Check browser console for more details.</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error fetching preview:', error);
            frame.srcdoc = `
                <div style="padding:20px; color:#e53e3e; font-family:Arial, sans-serif;">
                    <h3 style="margin-bottom:15px;">Error loading preview</h3>
                    <p style="margin-bottom:10px;">There was a network error when trying to load the email preview:</p>
                    <div style="background-color:#fef2f2; border-left:4px solid #e53e3e; padding:10px; margin-bottom:15px;">
                        ${error.message || 'Network error'}
                    </div>
                    <p><strong>Email Type:</strong> ${currentEmailType}</p>
                    <p><strong>Language:</strong> ${lang}</p>
                    <p style="margin-top:15px;">Please check that the server is running and try again.</p>
                </div>
            `;
        });
    }
    
    // Function to switch languages in the preview modal
    function switchPreviewLanguage(lang) {
        const languageIndicators = document.querySelectorAll('.preview-language-indicator');
        
        // Update active state on language indicators
        languageIndicators.forEach(indicator => {
            if (indicator.dataset.lang === lang) {
                indicator.classList.add('active');
            } else {
                indicator.classList.remove('active');
            }
        });
        
        // Load the preview in the selected language
        previewEmail(lang);
    }
    
    function togglePreviewOptions() {
        document.getElementById('previewOptions').classList.toggle('show');
    }
    
    function toggleLanguageOptions() {
        // Close any other open dropdowns first
        document.getElementById('previewOptions')?.classList.remove('show');
        
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
        
        // Preview dropdown
        if (!event.target.matches('.preview-button') && 
            !event.target.closest('.preview-button')) {
            var previewDropdown = document.getElementById('previewOptions');
            if (previewDropdown && previewDropdown.classList.contains('show')) {
                previewDropdown.classList.remove('show');
            }
        }
    });
    
    // Logo upload handling
    function handleLogoUpload(input) {
        if (input.files && input.files[0]) {
            // Create a FormData object to send the file
            const formData = new FormData();
            formData.append('logo', input.files[0]);
            formData.append('_token', '{{ csrf_token() }}');
            
            // Show loading indicator
            const logoContainer = document.querySelector('.current-logo');
            logoContainer.innerHTML = `
                <div class="logo-loading">
                    <i class="uil uil-spinner fa-spin"></i>
                    <span>Uploading...</span>
                </div>
            `;
            
            // Upload the file using AJAX
            fetch('{{ route('admin.settings.upload-logo') }}', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the logo preview with the new URL
                    logoContainer.innerHTML = `
                        <img src="${data.url}" alt="Email Logo" id="currentLogoImage" class="logo-preview">
                    `;
                    
                    // Update hidden input with logo URL
                    document.getElementById('email_logo_url').value = data.url;
                    
                    // Enable remove button
                    const removeBtn = document.querySelector('.logo-remove-btn');
                    if (removeBtn) {
                        removeBtn.disabled = false;
                    }
                    
                    // If email preview is already open, refresh it with the new logo
                    refreshEmailPreviewIfOpen();
                } else {
                    // Show error message
                    alert('Error uploading logo: ' + data.message);
                    
                    // Reset to placeholder
                    logoContainer.innerHTML = `
                        <div class="logo-placeholder" id="logoPlaceholder">
                            <i class="uil uil-image"></i>
                            <span>No logo uploaded</span>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error uploading logo:', error);
                alert('Error uploading logo. Please try again.');
                
                // Reset to placeholder on error
                logoContainer.innerHTML = `
                    <div class="logo-placeholder" id="logoPlaceholder">
                        <i class="uil uil-image"></i>
                        <span>No logo uploaded</span>
                    </div>
                `;
            });
        }
    }
    
    function removeLogo() {
        // Show loading indicator
        const logoContainer = document.querySelector('.current-logo');
        logoContainer.innerHTML = `
            <div class="logo-loading">
                <i class="uil uil-spinner fa-spin"></i>
                <span>Removing...</span>
            </div>
        `;
        
        // Send delete request to remove the logo
        fetch('{{ route('admin.settings.remove-logo') }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear logo display
                logoContainer.innerHTML = `
                    <div class="logo-placeholder" id="logoPlaceholder">
                        <i class="uil uil-image"></i>
                        <span>No logo uploaded</span>
                    </div>
                `;
                
                // Clear hidden input
                document.getElementById('email_logo_url').value = '';
                
                // Disable remove button
                const removeBtn = document.querySelector('.logo-remove-btn');
                if (removeBtn) {
                    removeBtn.disabled = true;
                }
                
                // If email preview is already open, refresh it without the logo
                refreshEmailPreviewIfOpen();
            } else {
                alert('Error removing logo: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error removing logo:', error);
            alert('Error removing logo. Please try again.');
            
            // Reset the container to show the previous logo if there was one
            const previousLogo = document.getElementById('email_logo_url').value;
            if (previousLogo) {
                logoContainer.innerHTML = `
                    <img src="${previousLogo}" alt="Email Logo" id="currentLogoImage" class="logo-preview">
                `;
            } else {
                logoContainer.innerHTML = `
                    <div class="logo-placeholder" id="logoPlaceholder">
                        <i class="uil uil-image"></i>
                        <span>No logo uploaded</span>
                    </div>
                `;
            }
        });
    }
    
    function refreshEmailPreviewIfOpen() {
        // Check if email preview is open
        const emailPreviewContent = document.getElementById('emailPreviewContent');
        if (emailPreviewContent && emailPreviewContent.innerHTML.trim() !== '') {
            // Get the current language tab
            const activeLanguageOption = document.querySelector('.language-option.active');
            const language = activeLanguageOption ? activeLanguageOption.getAttribute('data-language') : 'en';
            
            // Refresh preview with updated logo
            if (language === 'en' || language === 'fr') {
                previewEmail(language);
            }
        }
    }

    // Function to close the email preview
    function closePreview() {
        const previewContainer = document.getElementById('emailPreview');
        if (previewContainer) {
            previewContainer.classList.remove('visible');
            setTimeout(() => {
                // Don't remove the container - this allows us to keep the state
                // when reopening the preview
                previewContainer.style.display = 'none';
                
                // Make sure to preserve the flex layout for next time
                previewContainer.style.alignItems = 'center';
                previewContainer.style.justifyContent = 'center';
            }, 300);
        }
    }

    // Close preview when clicking outside of it
    document.addEventListener('click', function(event) {
        const previewContainer = document.getElementById('emailPreview');
        if (previewContainer && event.target === previewContainer) {
            closePreview();
        }
    });

    function sendDirectVerificationEmail() {
        const email = document.getElementById('testEmailAddress').value;
        const language = document.getElementById('testEmailLanguage').value;
        const resultDiv = document.getElementById('emailTestResult');
        
        if (!email) {
            showTestResult('error', 'Please enter an email address');
            return;
        }
        
        // Show loading state
        document.querySelector('.direct-test-button.verification').disabled = true;
        document.querySelector('.direct-test-button.verification').innerHTML = '<i class="uil uil-spinner fa-spin"></i> Sending...';
        
        showTestResult('loading', 'Sending verification email...');
        
        // Make the API call
        fetch(`/test-email-direct/${encodeURIComponent(email)}/${language}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showTestResult('success', data.message);
                } else {
                    showTestResult('error', data.message);
                }
            })
            .catch(error => {
                showTestResult('error', 'Network error: ' + error.message);
            })
            .finally(() => {
                // Reset button state
                document.querySelector('.direct-test-button.verification').disabled = false;
                document.querySelector('.direct-test-button.verification').innerHTML = '<i class="uil uil-envelope-check"></i> Send Verification Email';
            });
    }
    
    function sendDirectFeedbackEmail() {
        const email = document.getElementById('testEmailAddress').value;
        const language = document.getElementById('testEmailLanguage').value;
        const resultDiv = document.getElementById('emailTestResult');
        
        if (!email) {
            showTestResult('error', 'Please enter an email address');
            return;
        }
        
        // Show loading state
        document.querySelector('.direct-test-button.feedback').disabled = true;
        document.querySelector('.direct-test-button.feedback').innerHTML = '<i class="uil uil-spinner fa-spin"></i> Sending...';
        
        showTestResult('loading', 'Sending feedback email...');
        
        // Make the API call
        fetch(`/test-feedback-email-direct/${encodeURIComponent(email)}/${language}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showTestResult('success', data.message);
                } else {
                    showTestResult('error', data.message);
                    console.error('Error details:', data);
                }
            })
            .catch(error => {
                showTestResult('error', 'Network error: ' + error.message);
            })
            .finally(() => {
                // Reset button state
                document.querySelector('.direct-test-button.feedback').disabled = false;
                document.querySelector('.direct-test-button.feedback').innerHTML = '<i class="uil uil-comment-alt-message"></i> Send Feedback Email';
            });
    }
    
    function showTestResult(type, message) {
        const resultDiv = document.getElementById('emailTestResult');
        resultDiv.style.display = 'block';
        
        if (type === 'loading') {
            resultDiv.style.backgroundColor = '#f0f9ff';
            resultDiv.style.color = '#0369a1';
            resultDiv.style.border = '1px solid #bae6fd';
            resultDiv.innerHTML = `<div style="display: flex; align-items: center; gap: 10px;">
                <i class="uil uil-spinner fa-spin" style="font-size: 1.2rem;"></i>
                ${message}
            </div>`;
        } else if (type === 'success') {
            resultDiv.style.backgroundColor = '#f0fdf4';
            resultDiv.style.color = '#166534';
            resultDiv.style.border = '1px solid #bbf7d0';
            resultDiv.innerHTML = `<div style="display: flex; align-items: center; gap: 10px;">
                <i class="uil uil-check-circle" style="font-size: 1.2rem;"></i>
                ${message}
            </div>`;
        } else if (type === 'error') {
            resultDiv.style.backgroundColor = '#fef2f2';
            resultDiv.style.color = '#b91c1c';
            resultDiv.style.border = '1px solid #fecaca';
            resultDiv.innerHTML = `<div style="display: flex; align-items: center; gap: 10px;">
                <i class="uil uil-exclamation-triangle" style="font-size: 1.2rem;"></i>
                ${message}
            </div>
            <div style="margin-top: 8px; font-size: 0.9rem;">Check the developer console for more details.</div>`;
        }
    }
</script>
@endsection 