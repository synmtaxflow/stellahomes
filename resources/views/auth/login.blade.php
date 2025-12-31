@extends('layouts.app')

@section('title', 'Login - Hostel Management System')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-building fs-1 text-primary"></i>
                        <h2 class="mt-3 mb-1 fw-bold">{{ \App\Models\HostelDetail::getHostelDetail()->hostel_name ?? 'Hostel Management' }}</h2>
                        <p class="text-muted">Sign in to your account</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Error!</strong> {{ $errors->first() }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="bi bi-person me-1"></i> Username
                            </label>
                            <input 
                                type="text" 
                                class="form-control form-control-lg @error('username') is-invalid @enderror" 
                                id="username" 
                                name="username" 
                                value="{{ old('username') }}" 
                                placeholder="Enter your username"
                                required 
                                autofocus
                            >
                            @error('username')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock me-1"></i> Password
                            </label>
                            <div class="input-group">
                                <input 
                                    type="password" 
                                    class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                    id="password" 
                                    name="password" 
                                    placeholder="Enter your password"
                                    required
                                >
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3 form-check d-flex justify-content-between align-items-center">
                            <div>
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>
                            <div>
                                <a href="{{ route('password.forgot') }}" class="text-decoration-none small">
                                    <i class="bi bi-question-circle me-1"></i>Forgot Password?
                                </a>
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                            </button>
                        </div>

                        <div class="text-center">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Login as: Owner, Matron/Patron, or Student
                            </small>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('landing') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-house me-1"></i>Go Back Home
                        </a>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <p class="text-muted small">
                    &copy; {{ date('Y') }} Hostel Management System. All rights reserved.
                </p>
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
</style>
@endpush

@push('scripts')
<script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const passwordIcon = document.getElementById('togglePasswordIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordIcon.classList.remove('bi-eye');
            passwordIcon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            passwordIcon.classList.remove('bi-eye-slash');
            passwordIcon.classList.add('bi-eye');
        }
    });
</script>
@endpush
@endsection

