@extends('layouts.guest')

@section('content')
    <p class="mb-4 text-muted small">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success mb-4">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between mt-4">
        <form method="POST" action="{{ route('verification.send') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-auth w-100">{{ __('Resend Verification Email') }}</button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-link text-decoration-none p-0">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
@endsection
