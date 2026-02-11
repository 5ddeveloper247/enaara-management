@extends('layouts.auth')

@section('title', 'Register')
@section('page-title', 'Create Account')
@section('page-subtitle', 'Sign up to get started')

@section('content')
<div>
    <div class="mb-3">
        <label for="name" class="form-label">
            <i class="bi bi-person me-2"></i>Full Name
        </label>
        <input 
            type="text" 
            class="form-control" 
            id="name" 
            name="name" 
            placeholder="Enter your full name"
        >
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">
            <i class="bi bi-envelope me-2"></i>Email Address
        </label>
        <input 
            type="email" 
            class="form-control" 
            id="email" 
            name="email" 
            placeholder="Enter your email"
        >
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">
            <i class="bi bi-lock me-2"></i>Password
        </label>
        <div class="input-group">
            <input 
                type="password" 
                class="form-control" 
                id="password" 
                name="password" 
                placeholder="Create a password"
            >
            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                <i class="bi bi-eye" id="togglePasswordIcon"></i>
            </button>
        </div>
        <small class="text-muted">Must be at least 8 characters</small>
    </div>

    <div class="mb-3">
        <label for="password_confirmation" class="form-label">
            <i class="bi bi-lock-fill me-2"></i>Confirm Password
        </label>
        <div class="input-group">
            <input 
                type="password" 
                class="form-control" 
                id="password_confirmation" 
                name="password_confirmation" 
                placeholder="Confirm your password"
            >
            <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirmation">
                <i class="bi bi-eye" id="togglePasswordConfirmationIcon"></i>
            </button>
        </div>
    </div>

    <div class="mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="terms">
            <label class="form-check-label" for="terms">
                I agree to the <a href="#" class="text-decoration-none">Terms & Conditions</a>
            </label>
        </div>
    </div>

    <a href="{{ route('admin.dashboard') }}" class="btn btn-auth w-100 mb-3 text-decoration-none">
        <i class="bi bi-person-plus me-2"></i>Create Account
    </a>

    <div class="auth-footer">
        <p class="mb-0">Already have an account? <a href="{{ route('login') }}">Sign in here</a></p>
    </div>
</div>

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

    // Toggle password confirmation visibility
    document.getElementById('togglePasswordConfirmation').addEventListener('click', function() {
        const passwordInput = document.getElementById('password_confirmation');
        const passwordIcon = document.getElementById('togglePasswordConfirmationIcon');
        
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
@endsection

