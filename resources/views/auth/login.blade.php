@extends('layouts.auth')

@section('title', 'Login')
@section('page-title', 'Welcome Back')
@section('page-subtitle', 'Please sign in to your account')

@section('content')
    <div>
        <div class="mb-3">
            <label for="email" class="form-label">
                <i class="bi bi-envelope me-2"></i>Email Address
            </label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email">
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">
                <i class="bi bi-lock me-2"></i>Password
            </label>
            <div class="input-group">
                <input type="password" class="form-control" id="password" name="password"
                    placeholder="Enter your password">
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                    <i class="bi bi-eye" id="togglePasswordIcon"></i>
                </button>
            </div>
        </div>

        <div class="mb-3 d-flex justify-content-between align-items-center">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">
                    Remember me
                </label>
            </div>
            <a href="#" class="text-decoration-none">Forgot password?</a>
        </div>

        <a href="{{ route('admin.dashboard') }}" class="btn btn-auth w-100 mb-3 text-decoration-none">
            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
        </a>

        <div class="auth-footer">
            <p class="mb-0">Don't have an account? <a href="{{ route('register') }}">Sign up here</a></p>
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
    </script>
@endsection
