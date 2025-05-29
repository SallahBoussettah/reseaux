@extends('layouts.dashboard')

@section('title', 'Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Settings</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                            @foreach($settings as $group => $groupSettings)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $loop->first ? 'active' : '' }}" 
                                            id="{{ $group }}-tab" 
                                            data-bs-toggle="tab" 
                                            data-bs-target="#{{ $group }}" 
                                            type="button" 
                                            role="tab" 
                                            aria-controls="{{ $group }}" 
                                            aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                        {{ ucfirst($group) }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content p-4" id="settingsTabsContent">
                            @foreach($settings as $group => $groupSettings)
                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                                     id="{{ $group }}" 
                                     role="tabpanel" 
                                     aria-labelledby="{{ $group }}-tab">
                                    
                                    <div class="row">
                                        @foreach($groupSettings as $setting)
                                            <div class="col-md-6 mb-4">
                                                <div class="form-group">
                                                    <label for="{{ $setting->key }}">
                                                        {{ $setting->label }}
                                                        @if($setting->description)
                                                            <i class="fas fa-info-circle" 
                                                               data-bs-toggle="tooltip" 
                                                               title="{{ $setting->description }}"></i>
                                                        @endif
                                                    </label>
                                                    
                                                    <input type="hidden" name="settings[{{ $setting->id }}][key]" value="{{ $setting->key }}">
                                                    
                                                    @if($setting->type == 'text')
                                                        <input type="text" 
                                                               class="form-control" 
                                                               id="{{ $setting->key }}" 
                                                               name="settings[{{ $setting->id }}][value]"
                                                               value="{{ $setting->value }}">
                                                    @elseif($setting->type == 'color')
                                                        <div class="input-group">
                                                            <input type="color" 
                                                                   class="form-control form-control-color" 
                                                                   id="{{ $setting->key }}_picker" 
                                                                   value="{{ $setting->value }}"
                                                                   onchange="document.getElementById('{{ $setting->key }}').value = this.value">
                                                            <input type="text" 
                                                                   class="form-control" 
                                                                   id="{{ $setting->key }}"
                                                                   name="settings[{{ $setting->id }}][value]"
                                                                   aria-label="Color value"
                                                                   value="{{ $setting->value }}"
                                                                   onchange="document.getElementById('{{ $setting->key }}_picker').value = this.value">
                                                        </div>
                                                    @elseif($setting->type == 'textarea')
                                                        <textarea class="form-control" 
                                                                  id="{{ $setting->key }}" 
                                                                  name="settings[{{ $setting->id }}][value]"
                                                                  rows="3">{{ $setting->value }}</textarea>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
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
    });
</script>
@endsection 