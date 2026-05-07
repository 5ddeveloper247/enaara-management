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
                <div class="input-group">
                    <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required autocomplete="current-password" style="border-right: none;">
                    <button class="btn btn-outline-light toggle-password" type="button" data-target="current_password" style="border-left: none; background: rgba(255, 255, 255, 0.1);">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
                @error('current_password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">
                    <i class="bi bi-lock me-2"></i>New password <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required autocomplete="new-password" style="border-right: none;">
                    <button class="btn btn-outline-light toggle-password" type="button" data-target="password" style="border-left: none; background: rgba(255, 255, 255, 0.1);">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
                <small class="text-muted">At least 8 characters with uppercase, lowercase, and numbers.</small>
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">
                    <i class="bi bi-lock-fill me-2"></i>Confirm new password <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" style="border-right: none;">
                    <button class="btn btn-outline-light toggle-password" type="button" data-target="password_confirmation" style="border-left: none; background: rgba(255, 255, 255, 0.1);">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
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

@push('scripts')
<script>
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        });
    });
</script>
@endpush
