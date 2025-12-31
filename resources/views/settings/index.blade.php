@extends('layouts.app')

@section('title', 'Landing Page Settings')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">
                <i class="bi bi-gear me-2"></i>Landing Page Settings
            </h1>
            <p class="text-muted">Manage images and colors for the landing page</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form id="settingsForm" action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('POST')

        <!-- Images Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="bi bi-images me-2"></i>Images
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <!-- Hero Image -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Hero Image</label>
                        <div class="mb-3">
                            @php
                                $heroImage = $settings['hero_image'] ?? asset('landing pages/img/hero.jpg');
                                $isUploaded = isset($settings['hero_image_raw']) && $settings['hero_image_raw'];
                            @endphp
                            <div class="position-relative mb-3">
                                <img src="{{ $heroImage }}" 
                                     alt="Hero Image" 
                                     class="img-fluid rounded border" 
                                     style="max-height: 200px; width: 100%; object-fit: cover;">
                                @if($isUploaded)
                                    <form action="{{ route('settings.delete-image', 'hero_image') }}" method="POST" class="d-inline delete-image-form">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2"
                                                data-image-type="Hero Image">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <input type="file" class="form-control" name="hero_image" accept="image/*">
                            <small class="text-muted">Recommended size: 1920x1080px. Max size: 2MB</small>
                        </div>
                    </div>

                    <!-- About Image -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">About Section Image</label>
                        <div class="mb-3">
                            @php
                                $aboutImage = $settings['about_image'] ?? asset('landing pages/img/about.jpg');
                                $isAboutUploaded = isset($settings['about_image_raw']) && $settings['about_image_raw'];
                            @endphp
                            <div class="position-relative mb-3">
                                <img src="{{ $aboutImage }}" 
                                     alt="About Image" 
                                     class="img-fluid rounded border" 
                                     style="max-height: 200px; width: 100%; object-fit: cover;">
                                @if($isAboutUploaded)
                                    <form action="{{ route('settings.delete-image', 'about_image') }}" method="POST" class="d-inline delete-image-form">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2"
                                                data-image-type="About Image">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <input type="file" class="form-control" name="about_image" accept="image/*">
                            <small class="text-muted">Recommended size: 800x600px. Max size: 2MB</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hostel Details Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="bi bi-building me-2"></i>Hostel Details
                </h5>
            </div>
            <div class="card-body">
                <form id="hostelDetailForm" action="{{ route('settings.hostel-detail.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('POST')
                    <div class="row g-4">
                        <!-- Hostel Name -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Hostel Name *</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="hostel_name" 
                                   id="hostel_name"
                                   value="{{ $settings['hostel_name'] ?? 'ISACK HOSTEL' }}"
                                   placeholder="Enter hostel name"
                                   maxlength="255"
                                   required>
                            <small class="text-muted">This name will appear throughout the system (landing page, dashboard, login, etc.)</small>
                        </div>
                        
                        <!-- Hostel Logo -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Hostel Logo</label>
                            @if(isset($settings['hostel_logo']) && $settings['hostel_logo'])
                                <div class="mb-2">
                                    <img src="{{ $settings['hostel_logo'] }}" 
                                         alt="Hostel Logo" 
                                         class="img-fluid rounded border" 
                                         style="max-height: 100px; width: auto;">
                                </div>
                            @endif
                            <input type="file" 
                                   class="form-control" 
                                   name="logo" 
                                   accept="image/*">
                            <small class="text-muted">Recommended size: 200x200px. Max size: 2MB</small>
                        </div>
                        
                        <!-- Hostel Description -->
                        <div class="col-12">
                            <label class="form-label fw-bold">Description</label>
                            <textarea class="form-control" 
                                      name="description" 
                                      id="hostel_description"
                                      rows="3"
                                      placeholder="Enter hostel description (optional)"
                                      maxlength="1000">{{ $settings['hostel_description'] ?? '' }}</textarea>
                            <small class="text-muted">Brief description about your hostel (optional)</small>
                        </div>
                    </div>
                    
                    <!-- Hostel Detail Form Submit Button -->
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="submit" id="saveHostelDetailBtn" class="btn btn-primary" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
                            <i class="bi bi-save me-2"></i><span id="saveHostelDetailText">Save Hostel Details</span>
                            <span id="saveHostelDetailSpinner" class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Colors Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="bi bi-palette me-2"></i>Color Scheme
                </h5>
                <small class="text-muted">Match the owner panel colors (Blue gradient)</small>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <!-- Primary Color -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Primary Color</label>
                        <div class="input-group mb-3">
                            <input type="color" 
                                   class="form-control form-control-color" 
                                   name="primary_color" 
                                   value="{{ $settings['primary_color'] ?? '#1e3c72' }}"
                                   title="Choose primary color">
                            <input type="text" 
                                   class="form-control" 
                                   value="{{ $settings['primary_color'] ?? '#1e3c72' }}"
                                   id="primary_color_text"
                                   readonly>
                        </div>
                    </div>

                    <!-- Secondary Color -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Secondary Color</label>
                        <div class="input-group mb-3">
                            <input type="color" 
                                   class="form-control form-control-color" 
                                   name="secondary_color" 
                                   value="{{ $settings['secondary_color'] ?? '#2a5298' }}"
                                   title="Choose secondary color">
                            <input type="text" 
                                   class="form-control" 
                                   value="{{ $settings['secondary_color'] ?? '#2a5298' }}"
                                   id="secondary_color_text"
                                   readonly>
                        </div>
                    </div>

                    <!-- Light Color -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Light Color</label>
                        <div class="input-group mb-3">
                            <input type="color" 
                                   class="form-control form-control-color" 
                                   name="light_color" 
                                   value="{{ $settings['light_color'] ?? '#EFF5F9' }}"
                                   title="Choose light color">
                            <input type="text" 
                                   class="form-control" 
                                   value="{{ $settings['light_color'] ?? '#EFF5F9' }}"
                                   id="light_color_text"
                                   readonly>
                        </div>
                    </div>

                    <!-- Dark Color -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Dark Color</label>
                        <div class="input-group mb-3">
                            <input type="color" 
                                   class="form-control form-control-color" 
                                   name="dark_color" 
                                   value="{{ $settings['dark_color'] ?? '#1D2A4D' }}"
                                   title="Choose dark color">
                            <input type="text" 
                                   class="form-control" 
                                   value="{{ $settings['dark_color'] ?? '#1D2A4D' }}"
                                   id="dark_color_text"
                                   readonly>
                        </div>
                    </div>
                </div>

                <!-- Color Preview -->
                <div class="mt-4">
                    <label class="form-label fw-bold">Color Preview</label>
                    <div class="d-flex gap-2 flex-wrap">
                        <div class="p-3 rounded" 
                             id="primary_preview"
                             style="background: linear-gradient(135deg, {{ $settings['primary_color'] ?? '#1e3c72' }} 0%, {{ $settings['secondary_color'] ?? '#2a5298' }} 100%); color: white; min-width: 150px;">
                            Primary Gradient
                        </div>
                        <div class="p-3 rounded" 
                             id="light_preview"
                             style="background-color: {{ $settings['light_color'] ?? '#EFF5F9' }}; min-width: 150px;">
                            Light Color
                        </div>
                        <div class="p-3 rounded" 
                             id="dark_preview"
                             style="background-color: {{ $settings['dark_color'] ?? '#1D2A4D' }}; color: white; min-width: 150px;">
                            Dark Color
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="bi bi-telephone me-2"></i>Contact Information
                </h5>
            </div>
            <div class="card-body">
                <form id="contactForm" action="{{ route('settings.contact.update') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="row g-4">
                        <!-- Address -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Address</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="contact_address" 
                                   id="contact_address"
                                   value="{{ $settings['contact_address'] ?? '123 Hostel Street' }}"
                                   placeholder="Street Address" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">City/Country</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="contact_city" 
                                   id="contact_city"
                                   value="{{ $settings['contact_city'] ?? 'Dar es Salaam, Tanzania' }}"
                                   placeholder="City, Country" required>
                        </div>

                        <!-- Phone Numbers -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Phone Number 1</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="contact_phone1" 
                                   id="contact_phone1"
                                   value="{{ $settings['contact_phone1'] ?? '+255 XXX XXX XXX' }}"
                                   placeholder="+255 XXX XXX XXX" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Phone Number 2 (Optional)</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="contact_phone2" 
                                   id="contact_phone2"
                                   value="{{ $settings['contact_phone2'] ?? '+255 XXX XXX XXX' }}"
                                   placeholder="+255 XXX XXX XXX">
                        </div>

                        <!-- Email Addresses -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email Address 1</label>
                            <input type="email" 
                                   class="form-control" 
                                   name="contact_email1" 
                                   id="contact_email1"
                                   value="{{ $settings['contact_email1'] ?? 'info@isackhostel.com' }}"
                                   placeholder="info@isackhostel.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email Address 2 (Optional)</label>
                            <input type="email" 
                                   class="form-control" 
                                   name="contact_email2" 
                                   id="contact_email2"
                                   value="{{ $settings['contact_email2'] ?? 'bookings@isackhostel.com' }}"
                                   placeholder="bookings@isackhostel.com">
                        </div>

                        <!-- WhatsApp Number -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">WhatsApp Number</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="whatsapp_number" 
                                   id="whatsapp_number"
                                   value="{{ $settings['whatsapp_number'] ?? '+255 XXX XXX XXX' }}"
                                   placeholder="+255 XXX XXX XXX" required>
                            <small class="text-muted">This number will be used for WhatsApp chat button</small>
                        </div>

                        <!-- Booking Timeout -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Booking Timeout</label>
                            <div class="mb-2">
                                <label class="form-label small">Select Unit:</label>
                                <select class="form-select" name="booking_timeout_unit" id="booking_timeout_unit" required>
                                    <option value="hours" {{ ($settings['booking_timeout_unit'] ?? 'hours') === 'hours' ? 'selected' : '' }}>Hours</option>
                                    <option value="minutes" {{ ($settings['booking_timeout_unit'] ?? 'hours') === 'minutes' ? 'selected' : '' }}>Minutes</option>
                                </select>
                            </div>
                            <div id="hoursInputGroup" class="timeout-input-group" style="display: {{ ($settings['booking_timeout_unit'] ?? 'hours') === 'hours' ? 'block' : 'none' }};">
                                <label class="form-label small">Enter Time in Hours:</label>
                                <input type="number" 
                                       class="form-control booking-timeout-value" 
                                       data-unit="hours"
                                       id="booking_timeout_value_hours"
                                       value="{{ ($settings['booking_timeout_unit'] ?? 'hours') === 'hours' ? ($settings['booking_timeout_value'] ?? ($settings['booking_timeout_hours'] ?? '24')) : '' }}"
                                       min="1" 
                                       max="168"
                                       placeholder="24"
                                       required>
                                <small class="text-muted">Enter time in hours (1-168 hours)</small>
                            </div>
                            <div id="minutesInputGroup" class="timeout-input-group" style="display: {{ ($settings['booking_timeout_unit'] ?? 'hours') === 'minutes' ? 'block' : 'none' }};">
                                <label class="form-label small">Enter Time in Minutes:</label>
                                <input type="number" 
                                       class="form-control booking-timeout-value" 
                                       data-unit="minutes"
                                       id="booking_timeout_value_minutes"
                                       value="{{ ($settings['booking_timeout_unit'] ?? 'hours') === 'minutes' ? ($settings['booking_timeout_value'] ?? '1440') : '' }}"
                                       min="1" 
                                       max="10080"
                                       placeholder="1440"
                                       required>
                                <small class="text-muted">Enter time in minutes (1-10080 minutes = up to 7 days)</small>
                            </div>
                            <input type="hidden" name="booking_timeout_value" id="booking_timeout_value" value="{{ $settings['booking_timeout_value'] ?? ($settings['booking_timeout_hours'] ?? '24') }}">
                            <input type="hidden" name="booking_timeout_hours" id="booking_timeout_hours_hidden" value="{{ $settings['booking_timeout_hours'] ?? '24' }}">
                        </div>
                        
                    </div>
                    
                    <!-- Contact Form Submit Button -->
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="submit" id="saveContactBtn" class="btn btn-primary" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
                            <i class="bi bi-save me-2"></i><span id="saveContactText">Update Contact Information</span>
                            <span id="saveContactSpinner" class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('dashboard.owner') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-2"></i>Cancel
            </a>
            <button type="submit" id="saveSettingsBtn" class="btn btn-primary" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
                <i class="bi bi-save me-2"></i><span id="saveSettingsText">Save Settings</span>
                <span id="saveSettingsSpinner" class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';
    
    // Wait for jQuery to be available
    function initSettings() {
        if (typeof window.jQuery === 'undefined') {
            console.log('Waiting for jQuery...');
            setTimeout(initSettings, 50);
            return;
        }
        
        var $ = window.jQuery;
        
        $(document).ready(function() {
            console.log('Settings page script loaded');
            console.log('jQuery available:', typeof $ !== 'undefined');
    
    // Handle Booking Timeout Unit Change - Dynamic Show/Hide
    function initBookingTimeout() {
        const $unitSelect = $('#booking_timeout_unit');
        const $hoursGroup = $('#hoursInputGroup');
        const $minutesGroup = $('#minutesInputGroup');
        const $hoursInput = $('#booking_timeout_value_hours');
        const $minutesInput = $('#booking_timeout_value_minutes');
        const $hiddenHours = $('#booking_timeout_hours_hidden');
        const $valueInput = $('#booking_timeout_value');
        
        if (!$unitSelect.length) return;
        
        function updateTimeoutDisplay() {
            const unit = $unitSelect.val();
            
            if (unit === 'minutes') {
                // Hide hours input, show minutes input
                $hoursGroup.css('display', 'none');
                $minutesGroup.css('display', 'block');
                
                // Convert hours to minutes if needed
                if ($hoursInput.val() && (!$minutesInput.val() || $minutesInput.val() === '')) {
                    const hours = parseInt($hoursInput.val()) || 24;
                    $minutesInput.val(hours * 60);
                }
                
                // Update hidden value field
                const minutes = parseInt($minutesInput.val()) || 1440;
                $valueInput.val(minutes);
                $hiddenHours.val(Math.ceil(minutes / 60));
                
                // Make minutes input required, hours not required
                $minutesInput.prop('required', true);
                $hoursInput.prop('required', false);
                $hoursInput.removeAttr('name');
                $minutesInput.attr('name', 'booking_timeout_value');
            } else {
                // Hide minutes input, show hours input
                $minutesGroup.css('display', 'none');
                $hoursGroup.css('display', 'block');
                
                // Convert minutes to hours if needed
                if ($minutesInput.val() && (!$hoursInput.val() || $hoursInput.val() === '')) {
                    const minutes = parseInt($minutesInput.val()) || 1440;
                    $hoursInput.val(Math.round(minutes / 60));
                }
                
                // Update hidden value field
                const hours = parseInt($hoursInput.val()) || 24;
                $valueInput.val(hours);
                $hiddenHours.val(hours);
                
                // Make hours input required, minutes not required
                $hoursInput.prop('required', true);
                $minutesInput.prop('required', false);
                $minutesInput.removeAttr('name');
                $hoursInput.attr('name', 'booking_timeout_value');
            }
        }
        
        // Handle unit change
        $unitSelect.off('change').on('change', function() {
            updateTimeoutDisplay();
        });
        
        // Handle input changes
        $hoursInput.off('input').on('input', function() {
            const hours = parseInt($(this).val()) || 0;
            $valueInput.val(hours);
            $hiddenHours.val(hours);
        });
        
        $minutesInput.off('input').on('input', function() {
            const minutes = parseInt($(this).val()) || 0;
            $valueInput.val(minutes);
            $hiddenHours.val(Math.ceil(minutes / 60));
        });
        
        // Initialize on page load
        updateTimeoutDisplay();
    }
    
    // Initialize booking timeout handler
    initBookingTimeout();
    
    // Update color text inputs when color picker changes
    const colorInputs = {
        'primary_color': 'primary_color_text',
        'secondary_color': 'secondary_color_text',
        'light_color': 'light_color_text',
        'dark_color': 'dark_color_text'
    };

    Object.keys(colorInputs).forEach(colorKey => {
        const colorPicker = document.querySelector(`input[name="${colorKey}"]`);
        const textInput = document.getElementById(colorInputs[colorKey]);
        
        if (colorPicker && textInput) {
            colorPicker.addEventListener('input', function() {
                textInput.value = this.value;
                updatePreview();
            });
        }
    });

    function updatePreview() {
        const primaryColor = document.querySelector('input[name="primary_color"]').value;
        const secondaryColor = document.querySelector('input[name="secondary_color"]').value;
        const lightColor = document.querySelector('input[name="light_color"]').value;
        const darkColor = document.querySelector('input[name="dark_color"]').value;

        const primaryPreview = document.getElementById('primary_preview');
        const lightPreview = document.getElementById('light_preview');
        const darkPreview = document.getElementById('dark_preview');

        if (primaryPreview) {
            primaryPreview.style.background = `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)`;
        }
        if (lightPreview) {
            lightPreview.style.backgroundColor = lightColor;
        }
        if (darkPreview) {
            darkPreview.style.backgroundColor = darkColor;
        }
    }

    // Handle form submission with AJAX using jQuery
    const $form = $('#settingsForm');
    const $saveBtn = $('#saveSettingsBtn');
    const $saveText = $('#saveSettingsText');
    const $saveSpinner = $('#saveSettingsSpinner');

    console.log('Form elements check:', {
        form: $form.length,
        btn: $saveBtn.length,
        text: $saveText.length,
        spinner: $saveSpinner.length
    });

    if ($form.length && $saveBtn.length) {
        console.log('Attaching event handlers...');
        
        // Remove any existing handlers first
        $form.off('submit');
        $saveBtn.off('click');
        
        // Handle button click directly - more reliable than form submit
        $saveBtn.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            console.log('Save button clicked!');
            console.log('Form element:', $form[0]);
            console.log('Button element:', $saveBtn[0]);

            // Disable button and show spinner
            $saveBtn.prop('disabled', true);
            $saveText.text('Saving...');
            $saveSpinner.removeClass('d-none');

            // Get CSRF token
            const csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
            
            console.log('CSRF Token:', csrfToken ? 'Found' : 'Not found');
            console.log('Form action:', $form.attr('action'));

            // Create FormData from the form
            const formData = new FormData($form[0]);

            // Send AJAX request using jQuery
            console.log('Sending AJAX request...');
            console.log('FormData entries:', Array.from(formData.entries()));
            
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                success: function(response, textStatus, xhr) {
                    console.log('=== SUCCESS CALLBACK TRIGGERED ===');
                    console.log('Response:', response);
                    console.log('Response type:', typeof response);
                    console.log('Response success:', response ? response.success : 'N/A');
                    console.log('Status:', textStatus);
                    console.log('XHR Status:', xhr.status);
                    
                    // Reset button state first
                    $saveBtn.prop('disabled', false);
                    $saveText.text('Save Settings');
                    $saveSpinner.addClass('d-none');
                    
                    // Check if response exists and is successful
                    if (response && (response.success === true || response.success === undefined || response.success !== false)) {
                        console.log('Showing success message');
                        // Show SweetAlert success popup
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message || 'Settings updated successfully!',
                            confirmButtonColor: '#1e3c72',
                            confirmButtonText: 'OK',
                            timer: 3000,
                            timerProgressBar: true,
                            showClass: {
                                popup: 'animate__animated animate__fadeInDown'
                            },
                            hideClass: {
                                popup: 'animate__animated animate__fadeOutUp'
                            }
                        }).then(function() {
                            window.location.reload();
                        });
                    } else if (response && response.success === false) {
                        console.log('Response indicates failure');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.error || response.message || 'Failed to update settings',
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        console.log('Unexpected response format, treating as success');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Settings updated successfully!',
                            confirmButtonColor: '#1e3c72',
                            confirmButtonText: 'OK',
                            timer: 3000,
                            timerProgressBar: true
                        }).then(function() {
                            window.location.reload();
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('=== ERROR CALLBACK TRIGGERED ===');
                    console.error('Error:', error);
                    console.error('Status:', status);
                    console.error('Status Code:', xhr.status);
                    console.error('Response Text:', xhr.responseText);
                    console.error('Response JSON:', xhr.responseJSON);
                    console.error('Ready State:', xhr.readyState);
                    
                    // Reset button state
                    $saveBtn.prop('disabled', false);
                    $saveText.text('Save Settings');
                    $saveSpinner.addClass('d-none');
                    
                    let errorMessage = 'An error occurred while saving settings. Please try again.';
                    
                    // Check if it's actually a success with wrong status code
                    if (xhr.status === 200 && xhr.responseJSON && xhr.responseJSON.success !== false) {
                        console.log('Actually a success despite error callback');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: xhr.responseJSON.message || 'Settings updated successfully!',
                            confirmButtonColor: '#1e3c72',
                            confirmButtonText: 'OK',
                            timer: 3000,
                            timerProgressBar: true
                        }).then(function() {
                            window.location.reload();
                        });
                        return;
                    }
                    
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        } else if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMessage = errors.join('<br>');
                        }
                    } else if (xhr.responseText) {
                        // Try to parse as JSON
                        try {
                            const parsed = JSON.parse(xhr.responseText);
                            if (parsed.error) errorMessage = parsed.error;
                            else if (parsed.message) errorMessage = parsed.message;
                        } catch (e) {
                            errorMessage = xhr.responseText.substring(0, 200);
                        }
                    }
                    
                    showAlert('danger', errorMessage);
                },
                complete: function(xhr, status) {
                    console.log('=== AJAX REQUEST COMPLETE ===');
                    console.log('Final Status:', status);
                    console.log('Final XHR Status:', xhr.status);
                    console.log('Final Response:', xhr.responseText);
                }
            });
        });
        
        // Also handle form submit as backup
        $form.on('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Form submit event triggered - delegating to button click');
            $saveBtn.trigger('click');
        });
            } else {
                console.error('Settings form or save button not found!');
                if (!$form.length) console.error('Form #settingsForm not found');
                if (!$saveBtn.length) console.error('Button #saveSettingsBtn not found');
            }

            function showAlert(type, message) {
                // Remove existing alerts
                $('.alert-dismissible').remove();

                // Create new alert
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;

                // Insert at the top of the container
                const $container = $('.container-fluid').first();
                if ($container.length) {
                    $container.prepend(alertHtml);
                    
                    // Auto-dismiss after 5 seconds
                    setTimeout(function() {
                        $('.alert-dismissible').fadeOut(function() {
                            $(this).remove();
                        });
                    }, 5000);
                }
            }
        });
    }

    // Handle Contact Form Submission
    const $contactForm = $('#contactForm');
    const $saveContactBtn = $('#saveContactBtn');
    const $saveContactText = $('#saveContactText');
    const $saveContactSpinner = $('#saveContactSpinner');

    if ($contactForm.length && $saveContactBtn.length) {
        console.log('Contact form found, attaching handlers...');
        
        // Remove all existing handlers
        $saveContactBtn.off('click').off('onclick');
        $contactForm.off('submit');
        
        // Ensure button is enabled
        $saveContactBtn.prop('disabled', false);
        $saveContactBtn.removeAttr('onclick');
        
        $saveContactBtn.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            console.log('Contact save button clicked!');
            
            // Validate form
            if (!$contactForm[0].checkValidity()) {
                $contactForm[0].reportValidity();
                return;
            }

            // Disable button and show spinner
            $saveContactBtn.prop('disabled', true);
            $saveContactText.text('Updating...');
            $saveContactSpinner.removeClass('d-none');

            // Get CSRF token
            const csrfToken = $('meta[name="csrf-token"]').attr('content') || $contactForm.find('input[name="_token"]').val();
            
            console.log('CSRF Token:', csrfToken ? 'Found' : 'Not found');
            console.log('Contact form action:', $contactForm.attr('action'));

            // Create FormData from the form
            const contactFormData = new FormData($contactForm[0]);
            console.log('Contact FormData entries:', Array.from(contactFormData.entries()));

            // Send AJAX request
            $.ajax({
                url: $contactForm.attr('action'),
                type: 'POST',
                data: contactFormData,
                processData: false,
                contentType: false,
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                success: function(response) {
                    console.log('=== CONTACT UPDATE SUCCESS ===');
                    console.log('Response:', response);
                    
                    $saveContactBtn.prop('disabled', false);
                    $saveContactText.text('Update Contact Information');
                    $saveContactSpinner.addClass('d-none');
                    
                    if (response && (response.success === true || response.success !== false)) {
                        // Show SweetAlert success popup
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message || 'Contact information updated successfully!',
                            confirmButtonColor: '#1e3c72',
                            confirmButtonText: 'OK',
                            timer: 3000,
                            timerProgressBar: true,
                            showClass: {
                                popup: 'animate__animated animate__fadeInDown'
                            },
                            hideClass: {
                                popup: 'animate__animated animate__fadeOutUp'
                            }
                        }).then(function() {
                            window.location.reload();
                        });
                    } else {
                        // Show error with SweetAlert
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.error || response.message || 'Failed to update contact information',
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('=== CONTACT UPDATE ERROR ===');
                    console.error('Error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    
                    $saveContactBtn.prop('disabled', false);
                    $saveContactText.text('Update Contact Information');
                    $saveContactSpinner.addClass('d-none');
                    
                    let errorMessage = 'An error occurred while updating contact information.';
                    
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMessage = errors.join('<br>');
                        } else if (xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        } else if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                    }
                    
                    showAlert('danger', errorMessage);
                }
            });
        });
        
        $contactForm.on('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $saveContactBtn.trigger('click');
        });
    }
    
    // Handle Hostel Detail Form Submission
    const $hostelDetailForm = $('#hostelDetailForm');
    const $saveHostelDetailBtn = $('#saveHostelDetailBtn');
    const $saveHostelDetailText = $('#saveHostelDetailText');
    const $saveHostelDetailSpinner = $('#saveHostelDetailSpinner');

    if ($hostelDetailForm.length && $saveHostelDetailBtn.length) {
        console.log('Hostel detail form found, attaching handlers...');
        
        $saveHostelDetailBtn.off('click');
        $hostelDetailForm.off('submit');
        
        $saveHostelDetailBtn.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            console.log('Hostel detail save button clicked!');
            
            // Validate form
            if (!$hostelDetailForm[0].checkValidity()) {
                $hostelDetailForm[0].reportValidity();
                return;
            }

            // Disable button and show spinner
            $saveHostelDetailBtn.prop('disabled', true);
            $saveHostelDetailText.text('Saving...');
            $saveHostelDetailSpinner.removeClass('d-none');

            // Get CSRF token
            const csrfToken = $('meta[name="csrf-token"]').attr('content') || $hostelDetailForm.find('input[name="_token"]').val();
            
            console.log('CSRF Token:', csrfToken ? 'Found' : 'Not found');
            console.log('Hostel detail form action:', $hostelDetailForm.attr('action'));

            // Create FormData from the form
            const hostelDetailFormData = new FormData($hostelDetailForm[0]);
            console.log('Hostel Detail FormData entries:', Array.from(hostelDetailFormData.entries()));

            // Send AJAX request
            $.ajax({
                url: $hostelDetailForm.attr('action'),
                type: 'POST',
                data: hostelDetailFormData,
                processData: false,
                contentType: false,
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                success: function(response) {
                    console.log('=== HOSTEL DETAIL UPDATE SUCCESS ===');
                    console.log('Response:', response);
                    
                    $saveHostelDetailBtn.prop('disabled', false);
                    $saveHostelDetailText.text('Save Hostel Details');
                    $saveHostelDetailSpinner.addClass('d-none');
                    
                    if (response && (response.success === true || response.success !== false)) {
                        // Show SweetAlert success popup
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message || 'Hostel details updated successfully!',
                            confirmButtonColor: '#1e3c72',
                            confirmButtonText: 'OK',
                            timer: 3000,
                            timerProgressBar: true,
                            showClass: {
                                popup: 'animate__animated animate__fadeInDown'
                            },
                            hideClass: {
                                popup: 'animate__animated animate__fadeOutUp'
                            }
                        }).then(function() {
                            window.location.reload();
                        });
                    } else {
                        // Show error with SweetAlert
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.error || response.message || 'Failed to update hostel details',
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('=== HOSTEL DETAIL UPDATE ERROR ===');
                    console.error('Error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    
                    $saveHostelDetailBtn.prop('disabled', false);
                    $saveHostelDetailText.text('Save Hostel Details');
                    $saveHostelDetailSpinner.addClass('d-none');
                    
                    let errorMessage = 'An error occurred while updating hostel details.';
                    
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMessage = errors.join('<br>');
                        } else if (xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        } else if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                    }
                    
                    showAlert('danger', errorMessage);
                }
            });
        });
        
        $hostelDetailForm.on('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $saveHostelDetailBtn.trigger('click');
        });
    }
    
    // Handle delete image forms with SweetAlert
    $(document).on('submit', '.delete-image-form', function(e) {
        e.preventDefault();
        const form = $(this);
        const imageType = form.find('button[type="submit"]').data('image-type') || 'image';
        
        Swal.fire({
            title: 'Thibitisha Ufutaji',
            text: `Je, una uhakika unataka kufuta picha hii ya ${imageType}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ndio, Futa',
            cancelButtonText: 'Ghairi',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.off('submit').submit();
            }
        });
    });
    
    // Start initialization
    initSettings();
})();
</script>
@endpush

