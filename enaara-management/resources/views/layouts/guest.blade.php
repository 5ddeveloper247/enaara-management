<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Admin Panel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Bootstrap CSS -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .form-label { font-weight: 500; color: #2c3e50; margin-bottom: 0.5rem; }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-auth {
            padding: 0.75rem;
            font-weight: 600;
            border-radius: 0.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: #fff;
        }
        .btn-auth:hover { color: #fff; box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="mb-4 text-center">
                <a href="{{ url('/') }}" class="text-decoration-none text-dark">
                    <img src="{{ asset('images/enaara-logo.png') }}" alt="{{ config('app.name') }}" style="width: 48px; height: 48px; object-fit: contain;">
                </a>
            </div>
            @yield('content')
        </div>
    </div>

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
