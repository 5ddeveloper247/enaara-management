@extends('layouts.auth')

@section('title', 'Forgot Password')
@section('page-title', 'Forgot Password')
@section('page-subtitle', 'Enter your email to get a reset link')

@section('content')
    <div>
        @if ($errors->any())
            <div class="alert alert-danger mb-3">
                <ul class="mb-0 list-unstyled">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success mb-3">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">
                    <i class="bi bi-envelope me-2"></i>Email Address <span class="text-danger">*</span>
                </label>

                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" required>

                @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-auth w-100 mb-3">
                <i class="bi bi-send me-2"></i>Send Reset Link
            </button>

            <div class="auth-footer">
                <p class="mb-0">Remembered your password? <a href="{{ route('login') }}">Back to login</a></p>
            </div>
        </form>
    </div>
@endsection

