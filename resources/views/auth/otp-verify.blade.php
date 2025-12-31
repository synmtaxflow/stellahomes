@extends('layouts.app')

@section('title', 'OTP Verification - Hostel Management System')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-shield-check fs-1 text-primary"></i>
                        <h2 class="mt-3 mb-1 fw-bold">OTP Verification</h2>
                        <p class="text-muted">Enter the OTP code sent to your phone</p>
                        <p class="text-muted small">
                            <i class="bi bi-phone me-1"></i>{{ $maskedPhone ?? 'N/A' }}
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Error!</strong> {{ $errors->first() }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('otp.verify') }}" id="otpForm">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="otp_code" class="form-label">
                                <i class="bi bi-key me-1"></i> Enter OTP Code
                            </label>
                            <input 
                                type="text" 
                                class="form-control form-control-lg text-center @error('otp_code') is-invalid @enderror" 
                                id="otp_code" 
                                name="otp_code" 
                                placeholder="000000"
                                maxlength="6"
                                required 
                                autofocus
                                pattern="[0-9]{6}"
                                style="font-size: 1.5rem; letter-spacing: 0.5rem; font-weight: bold;"
                            >
                            @error('otp_code')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-info-circle me-1"></i>
                                Enter the 6-digit code sent to your phone
                            </small>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg" id="verifyBtn">
                                <i class="bi bi-check-circle me-2"></i>Verify OTP
                            </button>
                        </div>

                        <div class="text-center mb-3">
                            <form method="POST" action="{{ route('otp.resend') }}" id="resendForm">
                                @csrf
                                <button type="submit" class="btn btn-link text-decoration-none" id="resendBtn">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Resend OTP
                                </button>
                            </form>
                            <small class="text-muted d-block mt-2" id="resendTimer">
                                You can resend OTP in <span id="timer">120</span> seconds
                            </small>
                        </div>

                        <div class="text-center">
                            <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i>Back to Login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }
    .card {
        border-radius: 15px;
    }
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #5568d3 0%, #653a8f 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    #otp_code {
        text-align: center;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const otpInput = document.getElementById('otp_code');
        const resendBtn = document.getElementById('resendBtn');
        const resendForm = document.getElementById('resendForm');
        const timerElement = document.getElementById('timer');
        const resendTimer = document.getElementById('resendTimer');
        
        let timeLeft = 120; // 2 minutes in seconds
        let timerInterval;

        // Auto-format OTP input (numbers only)
        otpInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length === 6) {
                // Auto-submit when 6 digits are entered
                document.getElementById('otpForm').submit();
            }
        });

        // Start countdown timer
        function startTimer() {
            timerInterval = setInterval(function() {
                timeLeft--;
                timerElement.textContent = timeLeft;
                
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    resendBtn.disabled = false;
                    resendTimer.style.display = 'none';
                }
            }, 1000);
        }

        // Disable resend button initially
        resendBtn.disabled = true;
        startTimer();

        // Handle resend form submission
        resendForm.addEventListener('submit', function(e) {
            if (resendBtn.disabled) {
                e.preventDefault();
                return;
            }
            // Allow form to submit normally
        });
    });
</script>
@endpush
@endsection

