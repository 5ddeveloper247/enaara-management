<!-- Left Sidebar -->
<aside class="admin-sidebar rounded-5">
    <div class="sidebar-header p-4">
        <div class="d-flex align-items-center justify-content-center">
            <img src="{{ asset('images/enaara-logo.png') }}" alt="Enaara Logo" class="img-fluid"
                style="max-height: 60px; object-fit: contain;">
        </div>
    </div>

    <nav class="sidebar-nav flex-fill overflow-y-auto">
        <ul class="list-unstyled mb-0">
            <!-- @if (validatePermissions('admin/dashboard'))
                <li class="mb-1 mx-3">
                    <a href="{{ route('admin.dashboard.index') }}" class="nav-link d-flex align-items-center text-white text-decoration-none px-3 py-2 rounded-pill {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                        <i class="bi bi-house-door me-2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
            @endif -->
            
            @php
                $moduleCategories = \App\Models\ModuleCategory::where('is_active', 1)
                    ->orderBy('display_order')
                    ->get();
            @endphp

            @foreach($moduleCategories as $category)
                @php
                    $menuItems = getLeftMenu($category->ID);
                @endphp
                
                @if($menuItems->count() > 0)
                    <div class="text-white px-4 opacity-50 small mt-3 mb-2">{{ $category->category_name }}</div>
                    
                    @foreach($menuItems as $menuItem)
                        @php
                            $routePath = $menuItem->route;
                            
                            if (empty($routePath)) {
                                continue;
                            }
                            
                            $isActive = false;
                            $menuUrl = '#';
                            
                            if (strpos($routePath, 'admin.') === 0) {
                                try {
                                    $menuUrl = route($routePath);
                                    $currentRoute = request()->route() ? request()->route()->getName() : '';
                                    if ($currentRoute) {
                                        $isActive = $currentRoute === $routePath || strpos($currentRoute, $routePath . '.') === 0;
                                    } else {
                                        $currentPath = request()->path();
                                        $menuPath = ltrim(str_replace('.', '/', $routePath), '/');
                                        $isActive = $currentPath === $menuPath || strpos($currentPath, $menuPath . '/') === 0;
                                    }
                                } catch (\Exception $e) {
                                    $menuUrl = url('/' . str_replace('.', '/', $routePath));
                                    $currentPath = request()->path();
                                    $menuPath = ltrim(str_replace('.', '/', $routePath), '/');
                                    $isActive = $currentPath === $menuPath || strpos($currentPath, $menuPath . '/') === 0 || request()->is($menuPath . '*');
                                }
                            } else {
                                $menuUrl = url($routePath);
                                $currentPath = request()->path();
                                $menuPath = ltrim($routePath, '/');
                                $isActive = $currentPath === $menuPath || strpos($currentPath, $menuPath . '/') === 0 || request()->is($menuPath . '*');
                            }
                            
                            $iconClass = $menuItem->css_class ?? 'bi bi-people';
                        @endphp
                        
                        <li class="mb-1 mx-3">
                            <a href="{{ $menuUrl }}" class="nav-link d-flex align-items-center text-white text-decoration-none px-3 py-2 rounded-pill {{ $isActive ? 'active' : '' }}">
                                <i class="{{ $iconClass }} me-2"></i>
                                <span>{{ $menuItem->module_name }}</span>
                            </a>
                        </li>
                    @endforeach
                @endif
            @endforeach


            {{-- EMPLOYE MANAGEMENT --}}
            <!-- <div class="text-white px-4 opacity-50 small mt-3 mb-2">Employee Management</div>
            <li class="mb-1 mx-3">
                <a href="{{ url('/admin/users') }}" class="nav-link d-flex align-items-center text-white text-decoration-none px-3 py-2 rounded-pill {{ request()->is('admin/users*') ? 'active' : '' }}">
                    <i class="bi bi-people me-2"></i>
                    <span>Users</span>
                </a>
            </li>
            <li class="mb-1 mx-3">
                <a href="{{ url('/admin/sbu') }}" class="nav-link d-flex align-items-center text-white text-decoration-none px-3 py-2 rounded-pill {{ request()->is('admin/sbu*') ? 'active' : '' }}">
                    <i class="bi bi-people me-2"></i>
                    <span>SBU</span>
                </a>
            </li>
            <li class="mb-1 mx-3">
                <a href="{{ url('/admin/departments') }}" class="nav-link d-flex align-items-center text-white text-decoration-none px-3 py-2 rounded-pill {{ request()->is('admin/departments*') ? 'active' : '' }}">
                    <i class="bi bi-building me-2"></i>
                    <span>Department</span>
                </a>
            </li>
            

            {{-- ATTENDENCE MANAGEMENT --}}
            <div class="text-white px-4 opacity-50 small mt-3 mb-2">Attendence</div>
            <li class="mb-1 mx-3">
                <a href="{{ url('/admin/daily-logs') }}" class="nav-link d-flex align-items-center text-white text-decoration-none px-3 py-2 rounded-pill {{ request()->is('admin/daily-logs*') ? 'active' : '' }}">
                    <i class="bi bi-clock-history me-2"></i>
                    <span>Daily Logs</span>
                </a>
            </li>
            <li class="mb-1 mx-3">
                <a href="{{ url('/admin/shift-planner') }}" class="nav-link d-flex align-items-center text-white text-decoration-none px-3 py-2 rounded-pill {{ request()->is('admin/shift-planner*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-week me-2"></i>
                    <span>Shift Planner</span>
                </a>
            </li>
            <li class="mb-1 mx-3">
                <a href="{{ url('/admin/regularization') }}" class="nav-link d-flex align-items-center text-white text-decoration-none px-3 py-2 rounded-pill {{ request()->is('admin/regularization*') ? 'active' : '' }}">
                    <i class="bi bi-patch-check me-2"></i>
                    <span>Regularization</span>
                </a>
            </li>
            <li class="mb-1 mx-3">
                <a href="{{ url('/admin/geofencing') }}" class="nav-link d-flex align-items-center text-white text-decoration-none px-3 py-2 rounded-pill {{ request()->is('admin/geofencing*') ? 'active' : '' }}">
                    <i class="bi bi-geo-alt-fill me-2"></i>
                    <span>Geofencing</span>
                </a>
            </li>

            {{-- LEAVE MANAGEMENT --}}
            <div class="text-white px-4 opacity-50 small mt-3 mb-2">Leave Management</div>
            <li class="mb-1 mx-3">
                <a href="{{ route('admin.leave.request.index') }}" class="nav-link d-flex align-items-center text-white text-decoration-none px-3 py-2 rounded-pill {{ request()->is('admin/leave-request*') ? 'active' : '' }}">
                    <i class="bi bi-envelope-paper me-2"></i>
                    <span>Leave Requests</span>
                </a>
            </li>
            <li class="mb-1 mx-3">
                <a href="{{ url('/admin/my-leaves') }}" class="nav-link d-flex align-items-center text-white text-decoration-none px-3 py-2 rounded-pill {{ request()->is('admin/my-leaves*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check me-2"></i>
                    <span>My Leaves</span>
                </a>
            </li>
            <li class="mb-1 mx-3">
                <a href="{{ url('/admin/leave-calendar') }}" class="nav-link d-flex align-items-center text-white text-decoration-none px-3 py-2 rounded-pill {{ request()->is('admin/leave-calendar*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-event me-2"></i>
                    <span>Leave Calendar</span>
                </a>
            </li>
            <li class="mb-1 mx-3">
                <a href="{{ url('/admin/balance-tracker') }}" class="nav-link d-flex align-items-center text-white text-decoration-none px-3 py-2 rounded-pill {{ request()->is('admin/balance-tracker*') ? 'active' : '' }}">
                    <i class="bi bi-wallet2 me-2"></i>
                    <span>Balance Tracker</span>
                </a>
            </li>

            {{-- PAYROLL & COMPLIANCE --}}
            <div class="text-white px-4 opacity-50 small mt-3 mb-2">Payroll & Compliance</div>
            <li class="mb-1 mx-3">
                <a href="{{ url('/admin/monthly-summary') }}" class="nav-link d-flex align-items-center text-white text-decoration-none px-3 py-2 rounded-pill {{ request()->is('admin/monthly-summary*') ? 'active' : '' }}">
                    <i class="bi bi-receipt-cutoff me-2"></i>
                    <span>Monthly Summary</span>
                </a>
            </li>
            <li class="mb-1 mx-3">
                <a href="{{ url('/admin/overtime') }}" class="nav-link d-flex align-items-center text-white text-decoration-none px-3 py-2 rounded-pill {{ request()->is('admin/overtime*') ? 'active' : '' }}">
                    <i class="bi bi-hourglass-split me-2"></i>
                    <span>Overtime Tracker</span>
                </a>
            </li>
            <li class="mb-1 mx-3">
                <a href="{{ url('/admin/audit-trails') }}" class="nav-link d-flex align-items-center text-white text-decoration-none px-3 py-2 rounded-pill {{ request()->is('admin/audit-trails*') ? 'active' : '' }}">
                    <i class="bi bi-shield-check me-2"></i>
                    <span>Audit Trail</span>
                </a>
            </li>

            {{-- CONFIGURATION --}}
            <div class="text-white px-4 opacity-50 small mt-3 mb-2">Configuration</div>
            <li class="mb-1 mx-3">
                <a href="{{ url('/admin/policies') }}" class="nav-link d-flex align-items-center text-white text-decoration-none px-3 py-2 rounded-pill {{ request()->is('admin/policies*') ? 'active' : '' }}">
                    <i class="bi bi-file-text me-2"></i>
                    <span>Policies</span>
                </a>
            </li>
            <li class="mb-1 mx-3">
                <a href="{{ url('/admin/workflows') }}" class="nav-link d-flex align-items-center text-white text-decoration-none px-3 py-2 rounded-pill {{ request()->is('admin/workflows*') ? 'active' : '' }}">
                    <i class="bi bi-diagram-2 me-2"></i>
                    <span>Workflows</span>
                </a>
            </li>
            <li class="mb-1 mx-3">
                <a href="{{ url('/admin/roles') }}" class="nav-link d-flex align-items-center text-white text-decoration-none px-3 py-2 rounded-pill {{ request()->is('admin/roles*') ? 'active' : '' }}">
                    <i class="bi bi-shield-lock me-2"></i>
                    <span>Roles & Permissions</span>
                </a>
            </li> -->
        </ul>
    </nav>

    <div class="sidebar-footer p-4">
        <div class="d-flex flex-column align-items-center text-center">
            <div class="mb-2">
                <img src="{{ asset('images/profile-placeholder.png') }}" alt="Profile"
                    class="rounded-circle border-2"
                    style="width: 50px; height: 50px; object-fit: cover; border-color: var(--primary-color) !important;"
                    onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'User') }}&background=e6c673&color=000&size=80'">
            </div>
            <h6 class="text-white fw-normal mb-0 small">{{ Auth::user()->name ?? 'Admin User' }}</h6>
            <a href="{{ route('logout') }}"
                class="text-white text-decoration-none d-inline-flex align-items-center justify-content-center rounded-circle"
                style="width: 36px; height: 36px;"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="bi bi-box-arrow-right fs-5"></i>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>
</aside>
