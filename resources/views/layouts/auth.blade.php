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
    {{-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> --}}

    <style>
        body {
            /* background: black; */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            overflow: hidden;
            font-family: 'Instrument Sans';
        }

        .wrapper {
            /* max-width: 450px; */
            width: 100%;
            height: 100%;
            background-image: url(https://images.unsplash.com/photo-1554469384-e58fac16e23a?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D);


        }

        .auth-card {
            color: #ffffff;
            background: rgba(1, 36, 69, 0.819);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-header h2 {
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 3rem;
            /* text-transform: uppercase */
        }

        .auth-header p {
            color: #eef4f8;
            margin: 0;
            font-size: 17px;
        }

        .form-label {
            font-weight: 500;
            color: #b3b6b8;
            margin-bottom: 0.5rem;
        }



        form input,
        form textarea,
        {
        background: transparent !important;
        box-shadow: 0 0 7px 4px #5a59593d !important;
        display: block;
        width: 100%;
        padding: .375rem .75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #212529 !important;
        border-radius: var(--bs-border-radius);
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out !important;
        }

        input:focus {
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .btn-auth {
            padding: 0.75rem;
            font-weight: 600;
            border-radius: 0.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background: #E6C671;
            border: none;
            color: #fff;
            color: #012445;
            transition: transform 0.2s;
        }

        .btn-auth:hover {
            transform: translateY(-2px) !important;
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #dee2e6;
        }

        .frg {
            color: #E6C671;
            text-decoration: none;
            font-weight: 500;
        }

        .frg:hover {
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
    <div class=" d-flex justify-content-between align-items-end w-100 h-100" style="z-index: 1000">
        <img src="/images/login3.png" class="img-fluid " style="height: 100%; width: 1000px">

        <div class="wrapper">
            <div class=" auth-card d-flex flex-column justify-content-center align-items-center h-100 w-100">
                <div class="auth-header" style="z-index: 1000">
                    <h2>@yield('page-title', 'Welcome Back')</h2>
                    <p>@yield('page-subtitle', 'Please sign in to continue')</p>
                </div>

                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>

    @stack('scripts')
</body>

</html>
