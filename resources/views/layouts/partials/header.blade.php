<!-- Header -->
<header class="admin-header py-2" style="z-index: 100;">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <button class="btn btn-link d-md-none sidebar-toggle me-2" type="button">
                <i class="bi bi-list fs-4"></i>
            </button>
            {{-- <h5 class="mb-0 fw-semibold">@yield('page-title', 'Dashboard')</h5> --}}
        </div>

        <div class="header-center flex-fill d-flex justify-content-center px-4">
            <div class="position-relative" style="max-width: 300px; width: 100%;">
                <input type="text" class="form-control rounded-pill border-0 bg-white text-dark" placeholder="Search..." id="globalSearch" style="height: 40px; padding-right: 70px; background: rgba(255, 255, 255, 0.1); color: var(--light-color);">
                <button class="btn btn-link text-decoration-none text-white bg-main position-absolute top-50 translate-middle-y rounded-circle d-flex align-items-center justify-content-center border-0" type="button" id="searchButton" style="right: 0px; width: 50px; height: 50px;">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>

        <div class="header-right d-flex align-items-center gap-3">
            <!-- Tenant Switcher -->
            <div class="dropdown">
                <button class="btn btn-link text-decoration-none text-white bg-main rounded-pill d-flex align-items-center border-0 px-3" type="button" data-bs-toggle="dropdown" style="height: 50px;">
                    <i class="bi bi-building me-2"></i>
                    <span class="d-none d-md-inline">Enaara</span>
                    <i class="bi bi-chevron-down ms-2"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <h6 class="dropdown-header">Switch Tenant</h6>
                    </li>
                    <li>
                        <a class="dropdown-item active" href="#">
                            <i class="bi bi-check-circle me-2 text-success"></i>Enaara
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="bi bi-circle me-2"></i>Madison Rawalpindi
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="bi bi-circle me-2"></i>Madison Lahore
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="bi bi-plus-circle me-2"></i>Add New Tenant
                        </a>
                    </li>
                </ul>
            </div>
            
            @php
                $unreadNotifications = Auth::user()?->unreadNotifications ?? collect();
                $unreadCount = $unreadNotifications->count();
            @endphp
            <div class="dropdown">

                <button class="btn btn-link text-decoration-none text-white bg-main rounded-circle position-relative d-flex align-items-center justify-content-center border-0" type="button" data-bs-toggle="dropdown" style="width: 50px; height: 50px;">
                    <i class="bi bi-bell"></i>
                    @if($unreadCount > 0)
                        <span class="badge bg-danger rounded-pill position-absolute" style="top: -5px; right: -5px; font-size: 0.65rem; min-width: 18px; height: 18px;">
                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                        </span>
                    @endif
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-0 overflow-hidden" style="width: 320px; border-radius: 12px;">
                    <li>
                        <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">Notifications</h6>
                            @if($unreadCount > 0)
                                <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="btn btn-link p-0 text-decoration-none small text-main" style="font-size: 0.75rem;">Mark all read</button>
                                </form>
                            @endif
                        </div>
                    </li>
                    <div class="notification-list" style="max-height: 350px; overflow-y: auto;">
                        @forelse($unreadNotifications->take(10) as $notification)
                            <li>
                                <a class="dropdown-item py-3 border-bottom d-flex align-items-start gap-3" href="{{ route('admin.notifications.read', $notification->id) }}">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                                        <i class="bi bi-envelope-paper"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-wrap small" style="line-height: 1.2;">{{ $notification->data['title'] ?? 'Notification' }}</div>
                                        <div class="text-muted small text-wrap mt-1" style="font-size: 0.75rem;">{{ $notification->data['message'] ?? '' }}</div>
                                        <div class="text-main small mt-2" style="font-size: 0.7rem;">{{ $notification->created_at->diffForHumans() }}</div>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li>
                                <div class="p-4 text-center text-muted">
                                    <i class="bi bi-bell-slash fs-3 d-block mb-2"></i>
                                    <span class="small">No new notifications</span>
                                </div>
                            </li>
                        @endforelse
                    </div>
                    @if($unreadCount > 0)
                        <li>
                            <a class="dropdown-item text-center py-2 bg-light small fw-semibold text-main" href="{{ route('admin.leave.request.index') }}">
                                View All Requests
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
            {{-- <div class="dropdown">
                <button class="btn btn-link text-decoration-none text-dark d-flex align-items-center" type="button"
                    data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-2"></i>
                    <span>{{ Auth::user()->name ?? 'Admin' }}</span>
                    <i class="bi bi-chevron-down ms-2"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                    @auth
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            @if (Route::has('logout'))
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </button>
                                </form>
                            @else
                                <a class="dropdown-item" href="#"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
                            @endif
                        </li>
                    @endauth
                </ul>
            </div> --}}
        </div>
    </div>
</header>
