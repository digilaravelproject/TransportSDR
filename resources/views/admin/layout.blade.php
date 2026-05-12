<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - TransportSDR Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- DataTables dark theme -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="{{ asset('css/admin-dashboard.css') }}" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            /* background-color: #07080a; */
            background: linear-gradient(90deg, #232526 0%, #414345 100%);
            color: #c7d6e6;
        }

        /* Sidebar Styling */
        .sidebar {
            min-height: 100vh;
            /* background: linear-gradient(180deg,#0b1220,#0f1724);
             */
            background: linear-gradient(180deg, #232526 0%, #414345 100%);
            color: #cbd5e1;
            transition: all 0.3s;
            z-index: 1000;
            padding-top: 18px;
            border-right: 1px solid rgba(255,255,255,0.02);
        }

        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            font-weight: 700;
            font-size: 1.25rem;
            letter-spacing: 1px;
            color: #fff;
            display:flex;align-items:center;gap:10px
        }

        .nav-link {
            color: #94a3b8;
            padding: 0.8rem 1.25rem;
            margin: 0.2rem 0.6rem;
            border-radius: 12px;
            transition: 0.18s;
            display: flex;
            align-items: center;
            gap:12px;
            font-weight:500;
        }

        .nav-link i {
            width: 25px;
            font-size: 1.1rem;
        }

        .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.03);
        }

        .nav-link.active {
            background: linear-gradient(90deg,#15303a,#1f4b66);
            color: #e6f9f1;
            box-shadow: 0 6px 20px rgba(2,6,23,0.6);
        }

        /* Content Area */
        .main-wrapper {
            width: 100%;
        }

        .content-area {
            padding: 12px 18px !important;
            width: 100%;
            background: transparent;
        }

        /* Mobile Adjustments */
        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                left: -100%;
            }

            .sidebar.show {
                left: 0;
                width: 280px;
            }

            .content-area {
                padding: 1rem;
            }
        }

        @media (min-width: 992px) {
            .sidebar {
                width: 280px;
                position: sticky;
                top: 0;
            }

            .main-wrapper {
                display: flex;
            }
        }

        /* Card & UI Enhancements */
        .card {
            border: 1px solid rgba(255,255,255,0.03) !important;
            border-radius: 16px !important;
            background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01)) !important;
            box-shadow: 0 8px 24px rgba(2,6,23,0.8) !important;
            transition: transform 0.16s;
        }

        .card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(2,6,23,0.95) !important; }

        .stats-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.6rem 1.2rem;
        }
        /* Dark-theme overrides for DataTables and form controls */
        /* Tables */
        table.dataTable, .table {
            background: transparent;
            color: #e6eef6;
            border-collapse: separate;
        }
        table.dataTable thead th {
            background: rgba(255,255,255,0.03);
            color: #e6eef6;
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        table.dataTable tbody td {
            background: linear-gradient(180deg, rgba(0,0,0,0.0), rgba(255,255,255,0.01));
            color: #dce9f2;
            vertical-align: middle;
            border-top: 1px solid rgba(255,255,255,0.02);
        }
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select,
        input.form-control, textarea.form-control, select.form-control, .form-select {
            background: rgba(255,255,255,0.03) !important;
            border: 1px solid rgba(255,255,255,0.04) !important;
            color: #e6eef6 !important;
            box-shadow: none !important;
        }
        .dataTables_wrapper .dataTables_filter label, .dataTables_wrapper .dataTables_length label {
            color: #9fb3c8;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            background: rgba(255,255,255,0.02);
            color: #c7d6e6 !important;
            border: 1px solid rgba(255,255,255,0.02);
            border-radius: 6px;
            padding: 6px 10px;
            margin-left: 4px;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: linear-gradient(90deg,#15303a,#1f4b66);
            color: #e6f9f1 !important;
        }
        /* Card form areas */
        .card-body {
            background: transparent !important;
        }
        label, .form-label, .form-text {
            color: #94a3b8 !important;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        .btn-primary {
            background: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }
        .btn-outline-primary {
            color: #9fc5ff;
            border-color: rgba(159,197,255,0.12);
            background: transparent;
        }
        .btn-outline-danger {
            color: #ffb3b3;
            border-color: rgba(255,100,100,0.12);
            background: transparent;
        }
        /* Badges */
        .badge {
            color: #0b1220;
        }
        .badge.bg-success {
            background: #34d399 !important;
            color: #042018 !important;
        }
        .badge.bg-secondary {
            background: #94a3b8 !important;
            color: #071019 !important;
        }
        
        /* Global Overrides for hardcoded Light theme classes across views */
        .bg-light, .bg-white { background: transparent !important; }
        .table-light, .table-light th, .table-light td {
            background: rgba(255,255,255,0.02) !important;
            color: #a8c0d8 !important;
            border-bottom: 1px solid rgba(255,255,255,0.05) !important;
        }
        .table-hover tbody tr:hover td, .table-hover tbody tr:hover th {
            background-color: rgba(255, 255, 255, 0.04) !important;
        }
        .card-header.bg-light, .card-footer.bg-light {
            background: rgba(0,0,0,0.2) !important;
            border-bottom: 1px solid rgba(255,255,255,0.05) !important;
            border-top: 1px solid rgba(255,255,255,0.05) !important;
            color: #f8fafc !important;
        }
        .text-dark { color: #f8fafc !important; }
        .text-muted { color: #94a3b8 !important; }
        .list-group-item {
            background-color: transparent !important;
            border-color: rgba(255, 255, 255, 0.05) !important;
            color: #c7d6e6 !important;
        }
        .border-warning { border-color: rgba(245, 158, 11, 0.4) !important; }
        hr { border-color: rgba(255,255,255,0.1) !important; }
        .modal-content {
            /* background: linear-gradient(180deg,#0b1220,#0f1724) !important; */
            background: linear-gradient(180deg, #232526 0%, #414345 100%) !important;
            color: #c7d6e6;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
        }
        .modal-header { border-bottom: 1px solid rgba(255,255,255,0.05); }
        .modal-footer { border-top: 1px solid rgba(255,255,255,0.05); }
        .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }

        /* Small tweaks for table responsiveness */
        .table-responsive { padding: 6px; }
        .datatable_wrapper { overflow: visible; }
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
                        <a class="nav-link {{ Route::currentRouteName() == 'admin.dashboard' ? 'active' : '' }}"
                            href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-th-large me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::currentRouteName() == 'admin.users.index' ? 'active' : '' }}"
                            href="{{ route('admin.users.index') }}">
                            <i class="fas fa-users me-2"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::currentRouteName() == 'admin.plans.index' ? 'active' : '' }}"
                            href="{{ route('admin.plans.index') }}">
                            <i class="fas fa-gem me-2"></i> Plans
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::currentRouteName() == 'admin.subscriptions.index' ? 'active' : '' }}"
                            href="{{ route('admin.subscriptions.index') }}">
                            <i class="fas fa-credit-card me-2"></i> Subscriptions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::currentRouteName() == 'admin.shifts.index' ? 'active' : '' }}"
                            href="{{ route('admin.shifts.index') }}">
                            <i class="fas fa-business-time me-2"></i> Shifts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ str_contains(Route::currentRouteName(), 'admin.vehicles') ? 'active' : '' }}"
                            href="{{ route('admin.vehicles.index') }}">
                            <i class="fas fa-truck me-2"></i> Vehicles
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ str_contains(Route::currentRouteName(), 'admin.vehicle-types') ? 'active' : '' }}"
                            href="{{ route('admin.vehicle-types.index') }}">
                            <i class="fas fa-car-side me-2"></i> Vehicle Types
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ str_contains(Route::currentRouteName(), 'admin.vendors') ? 'active' : '' }}"
                            href="{{ route('admin.vendors.index') }}">
                            <i class="fas fa-handshake me-2"></i> Vendors
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ str_contains(Route::currentRouteName(), 'admin.finance') ? 'active' : '' }}"
                            href="{{ route('admin.finance.index') }}">
                            <i class="fas fa-wallet me-2"></i> Finance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ str_contains(Route::currentRouteName(), 'admin.inventory') ? 'active' : '' }}"
                            href="{{ route('admin.inventory.index') }}">
                            <i class="fas fa-boxes me-2"></i> Inventory
                        </a>
                    </li>
                    <div class="px-4 mt-4 mb-2 small text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">
                        Resources</div>
                    <li class="nav-item">
                        <a class="nav-link {{ str_contains(Route::currentRouteName(), 'admin.document-templates') ? 'active' : '' }}"
                            href="{{ route('admin.document-templates.index') }}">
                            <i class="fas fa-file-invoice me-2"></i> Templates
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ str_contains(Route::currentRouteName(), 'admin.staff') ? 'active' : '' }}"
                            href="{{ route('admin.staff.index') }}">
                            <i class="fas fa-users me-2"></i> Staff
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ str_contains(Route::currentRouteName(), 'admin.routes') ? 'active' : '' }}"
                            href="{{ route('admin.routes.index') }}">
                            <i class="fas fa-map-marked-alt me-2"></i> Routes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ str_contains(Route::currentRouteName(), 'admin.leads') ? 'active' : '' }}"
                            href="{{ route('admin.leads.index') }}">
                            <i class="fas fa-envelope-open-text me-2"></i> Leads
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ str_contains(Route::currentRouteName(), 'admin.trips') ? 'active' : '' }}"
                            href="{{ route('admin.trips.index') }}">
                            <i class="fas fa-route me-2"></i> Trips
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
    <!-- jQuery + DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Initialize any table with class .datatable automatically
        $(document).ready(function(){
            $('.datatable').each(function(){
                if (!$.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable({
                        pageLength: 10,
                        lengthChange: true,
                        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                        responsive: true
                    });
                }
            });
        });
    </script>
</body>

</html>
