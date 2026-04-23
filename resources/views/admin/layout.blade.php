<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - TransportSDR Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
            color: white;
            position: fixed;
            width: 16.666%;
            padding: 0;
            overflow-y: auto;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
        }

        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link.active {
            background: #007bff;
            color: white;
        }

        .content {
            margin-left: 16.666%;
            padding: 2rem;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .table th {
            background: #f8f9fa;
            border-top: none;
        }

        .badge-role {
            font-size: 0.8em;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar p-0">
            <div class="p-3">
                <h5 class="text-center mb-4">
                    <i class="fas fa-truck me-2"></i>
                    TransportSDR
                </h5>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link @if (Route::currentRouteName() == 'admin.dashboard') active @endif"
                            href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if (Route::currentRouteName() == 'admin.users.index') active @endif"
                            href="{{ route('admin.users.index') }}">
                            <i class="fas fa-users me-2"></i>
                            Manage Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if (Route::currentRouteName() == 'admin.plans.index') active @endif"
                            href="{{ route('admin.plans.index') }}">
                            <i class="fas fa-list me-2"></i>
                            Manage Plans
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if (Route::currentRouteName() == 'admin.subscriptions.index') active @endif"
                            href="{{ route('admin.subscriptions.index') }}">
                            <i class="fas fa-credit-card me-2"></i>
                            Manage Subscriptions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(Route::currentRouteName() == 'admin.shifts.index') active @endif" href="{{ route('admin.shifts.index') }}">
                            <i class="fas fa-clock me-2"></i>
                            Manage Shifts
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link @if (str_contains(Route::currentRouteName(), 'admin.template-categories')) active @endif"
                            href="{{ route('admin.template-categories.index') }}">
                            <i class="fas fa-tags me-2"></i>
                            Template Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if (str_contains(Route::currentRouteName(), 'admin.document-templates')) active @endif"
                            href="{{ route('admin.document-templates.index') }}">
                            <i class="fas fa-file-alt me-2"></i>
                            Templates
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(str_contains(Route::currentRouteName(), 'admin.routes')) active @endif" href="{{ route('admin.routes.index') }}">
                            <i class="fas fa-route me-2"></i>
                            Manage Routes
                        </a>
                    </li>
                    <li class="nav-item mt-3">
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-light w-100">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content w-100">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
