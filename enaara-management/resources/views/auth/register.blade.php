@extends('layouts.auth')

@section('title', 'Register')
@section('page-title', 'Create Account')
@section('page-subtitle', 'Sign up to get started')

@section('content')
<div>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">
                <i class="bi bi-person me-2"></i>Full Name
            </label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                value="{{ old('name') }}" placeholder="Enter your full name" required autofocus autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">
                <i class="bi bi-envelope me-2"></i>Email Address
            </label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                value="{{ old('email') }}" placeholder="Enter your email" required autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">
                <i class="bi bi-lock me-2"></i>Password
            </label>
            <div class="input-group">
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                    name="password" placeholder="Create a password" required autocomplete="new-password">
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                    <i class="bi bi-eye" id="togglePasswordIcon"></i>
                </button>
            </div>
            <small class="text-muted">Must be at least 8 characters</small>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">
                <i class="bi bi-lock-fill me-2"></i>Confirm Password
            </label>
            <div class="input-group">
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                    placeholder="Confirm your password" required autocomplete="new-password">
                <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirmation">
                    <i class="bi bi-eye" id="togglePasswordConfirmationIcon"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-auth w-100 mb-3">
            <i class="bi bi-person-plus me-2"></i>Create Account
        </button>
    </form>

    <div class="auth-footer">
        <p class="mb-0">Already have an account? <a href="{{ route('login') }}">Sign in here</a></p>
    </div>
</div>

<script>
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
