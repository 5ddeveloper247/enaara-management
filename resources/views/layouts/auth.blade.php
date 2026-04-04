<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Login') - {{ config('app.name', 'Admin Panel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Bootstrap CSS -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Custom Auth CSS -->
    <style>
        body {
            background: black;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Instrument Sans', sans-serif;
        }

        .auth-container {
            max-width: 450px;
            width: 100%;
            padding: 2rem;
        }

        .auth-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-header h2 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .auth-header p {
            color: #6c757d;
            margin: 0;
        }

        .form-label {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .form-control {
            padding: 0.75rem 1rem;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-auth {
            padding: 0.75rem 1rem;
            font-weight: 600;
            border-radius: 0.6rem;

            /* Dark blue gradient like dashboard */
            background: linear-gradient(135deg, #0f2a44 0%, #133a5a 100%);

            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #ffffff;

            /* Soft glow effect */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);

            transition: all 0.25s ease;
        }

        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: #fff;
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #dee2e6;
        }

        .auth-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .input-group-text {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .alert {
            border-radius: 0.5rem;
        }
    </style>

    @stack('styles')
</head>

<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>@yield('page-title', 'Welcome Back')</h2>
                <p>@yield('page-subtitle', 'Please sign in to continue')</p>
            </div>


            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>

    @stack('scripts')
</body>

</html>