@extends('layouts.auth')

@section('title', 'Set new password')
@section('page-title', 'Change your password')
@section('page-subtitle', 'Enter your temporary password, then choose a new password')

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

        <form method="POST" action="{{ route('password.first-change.update') }}">
            @csrf
            <div class="mb-3">
                <label for="current_password" class="form-label">
                    <i class="bi bi-key me-2"></i>Temporary password <span class="text-danger">*</span>
                </label>
                <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required autocomplete="current-password">
                @error('current_password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">
                    <i class="bi bi-lock me-2"></i>New password <span class="text-danger">*</span>
                </label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required autocomplete="new-password">
                <small class="text-muted">At least 8 characters with uppercase, lowercase, and numbers.</small>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">
                    <i class="bi bi-lock-fill me-2"></i>Confirm new password <span class="text-danger">*</span>
                </label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Update password</button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-3 text-center">
            @csrf
            <button type="submit" class="btn btn-link text-muted btn-sm">Sign out</button>
        </form>
    </div>
@endsection
