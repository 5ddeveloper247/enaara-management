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
            
            <div class="dropdown">

                <button class="btn btn-link text-decoration-none text-white bg-main rounded-circle position-relative d-flex align-items-center justify-content-center border-0" type="button" data-bs-toggle="dropdown" style="width: 50px; height: 50px;">
                    <i class="bi bi-bell"></i>
                    <span class="badge bg-danger rounded-pill position-absolute" style="top: -5px; right: -5px; font-size: 0.65rem; min-width: 18px; height: 18px;">3</span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <h6 class="dropdown-header">Notifications</h6>
                    </li>
                    <li><a class="dropdown-item" href="#">New user registered</a></li>
                    <li><a class="dropdown-item" href="#">System update available</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="#">View all</a></li>
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
