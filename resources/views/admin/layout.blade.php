<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - TransportSDR Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7fe; color: #2d3748; }
        
        /* Sidebar Styling */
        .sidebar {
            min-height: 100vh;
            background: #1a202c;
            color: white;
            transition: all 0.3s;
            z-index: 1000;
        }
        .sidebar-brand {
            padding: 1.5rem;
            font-weight: 700;
            font-size: 1.25rem;
            letter-spacing: 1px;
            color: #fff;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .nav-link {
            color: #a0aec0;
            padding: 0.8rem 1.5rem;
            margin: 0.2rem 1rem;
            border-radius: 8px;
            transition: 0.3s;
            display: flex;
            align-items: center;
        }
        .nav-link i { width: 25px; font-size: 1.1rem; }
        .nav-link:hover { color: #fff; background: rgba(255,255,255,0.05); }
        .nav-link.active { background: #4c51bf; color: white; box-shadow: 0 4px 12px rgba(76, 81, 191, 0.3); }

        /* Content Area */
        .main-wrapper { width: 100%; }
        .content-area { padding: 2rem; width: 100%; }
        
        /* Mobile Adjustments */
        @media (max-width: 991.98px) {
            .sidebar { position: fixed; left: -100%; }
            .sidebar.show { left: 0; width: 280px; }
            .content-area { padding: 1rem; }
        }
        @media (min-width: 992px) {
            .sidebar { width: 280px; position: sticky; top: 0; }
            .main-wrapper { display: flex; }
        }

        /* Card & UI Enhancements */
        .card { border: none; border-radius: 15px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.07); transition: transform 0.2s; }
        .card:hover { transform: translateY(-2px); }
        .stats-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
        }
        .btn { border-radius: 8px; font-weight: 500; padding: 0.6rem 1.2rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark d-lg-none px-3">
        <a class="navbar-brand" href="#">TransportSDR</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
    </nav>

    <div class="main-wrapper">
        <div class="sidebar collapse d-lg-block" id="sidebarMenu">
            <div class="sidebar-brand text-center">
                <i class="fas fa-truck-fast me-2 text-primary"></i> TransportSDR
            </div>
            <div class="py-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link {{ Route::currentRouteName() == 'admin.dashboard' ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-th-large me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::currentRouteName() == 'admin.users.index' ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                            <i class="fas fa-users me-2"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::currentRouteName() == 'admin.plans.index' ? 'active' : '' }}" href="{{ route('admin.plans.index') }}">
                            <i class="fas fa-gem me-2"></i> Plans
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::currentRouteName() == 'admin.subscriptions.index' ? 'active' : '' }}" href="{{ route('admin.subscriptions.index') }}">
                            <i class="fas fa-credit-card me-2"></i> Subscriptions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::currentRouteName() == 'admin.shifts.index' ? 'active' : '' }}" href="{{ route('admin.shifts.index') }}">
                            <i class="fas fa-business-time me-2"></i> Shifts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ str_contains(Route::currentRouteName(), 'admin.vehicles') ? 'active' : '' }}" href="{{ route('admin.vehicles.index') }}">
                            <i class="fas fa-truck me-2"></i> Vehicles
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ str_contains(Route::currentRouteName(), 'admin.vendors') ? 'active' : '' }}" href="{{ route('admin.vendors.index') }}">
                            <i class="fas fa-handshake me-2"></i> Vendors
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ str_contains(Route::currentRouteName(), 'admin.finance') ? 'active' : '' }}" href="{{ route('admin.finance.index') }}">
                            <i class="fas fa-wallet me-2"></i> Finance
                        </a>
                    </li>
                    <div class="px-4 mt-4 mb-2 small text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Resources</div>
                    <li class="nav-item">
                        <a class="nav-link {{ str_contains(Route::currentRouteName(), 'admin.document-templates') ? 'active' : '' }}" href="{{ route('admin.document-templates.index') }}">
                            <i class="fas fa-file-invoice me-2"></i> Templates
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ str_contains(Route::currentRouteName(), 'admin.routes') ? 'active' : '' }}" href="{{ route('admin.routes.index') }}">
                            <i class="fas fa-map-marked-alt me-2"></i> Routes
                        </a>
                    </li>
                    <li class="nav-item mt-4 px-3">
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100 shadow-sm">
                                <i class="fas fa-power-off me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <div class="content-area">
            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>