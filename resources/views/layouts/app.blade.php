<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Admin Panel'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto+Flex:opsz,wght,XOPQ,XTRA,YOPQ,YTDE,YTFI,YTLC,YTUC@8..144,100..1000,96,468,79,-203,738,514,712&display=swap"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Bootstrap CSS -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    <!-- Custom Admin CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @stack('styles')
</head>

<body>
    <div class="admin-wrapper d-flex">
        @include('layouts.partials.sidebar')

        <!-- Right Main Section -->
        <div class="admin-main d-flex flex-column flex-grow-1">
            @include('layouts.partials.header')

            <!-- Main Content Area -->
            <main class="admin-content flex-grow-1">
                @if (session('success'))
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: "{{ session('success') }}",
                                timer: 2000,
                                showConfirmButton: false
                            });
                        });
                    </script>
                @endif

                @if (session('error'))
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: "{{ session('error') }}",
                                confirmButtonColor: '#1a237e',
                                confirmButtonText: 'Dismiss'
                            });
                        });
                    </script>
                @endif

                <script>
                    // Global helper functions for SweetAlert
                    window.showAlert = function(icon, title, message, timer = 2000) {
                        const useHtml = typeof message === 'string' && (message.includes('<br') || message.includes('<br/>'));
                        const cfg = {
                            icon: icon,
                            title: title,
                            timer: icon === 'success' ? timer : null,
                            showConfirmButton: icon !== 'success',
                            confirmButtonColor: '#1a237e',
                            confirmButtonText: 'Dismiss'
                        };
                        if (useHtml) {
                            cfg.html = message;
                        } else if (typeof message === 'string' && message.includes('\n')) {
                            cfg.html = message.replace(/\n/g, '<br>');
                        } else {
                            cfg.text = message;
                        }
                        return Swal.fire(cfg);
                    };

                    window.showSuccess = function(message, title = 'Saved') {
                        return window.showAlert('success', title, message);
                    };

                    window.showError = function(message, title = 'Error') {
                        return window.showAlert('error', title, message);
                    };
                </script>

                @yield('content')
            </main>

            @include('layouts.partials.footer')
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>

    <!-- jQuery (required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <!-- DataTables Responsive Extension -->
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <!-- DataTables Buttons Extension -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom Admin JS -->
    <script src="{{ asset('js/app.js') }}"></script>


    @stack('scripts')
</body>

</html>
