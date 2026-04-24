<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <script>
        (function () {
            try {
                var savedTheme = localStorage.getItem('acadclear-theme');
                if (savedTheme === 'dark') {
                    document.documentElement.classList.add('dark-mode');
                }
            } catch (e) {}
        })();
    </script>

    <title>{{ config('app.name', 'AcadClear') }} - Admin Dashboard</title>

    <!-- Custom fonts for this template-->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <style>
        :root {
            --tenant-primary: #2563eb;
            --tenant-primary-dark: #1d4ed8;
            --tenant-primary-soft: rgba(37, 99, 235, 0.12);
            --tenant-sidebar-gradient: linear-gradient(180deg, #2f6fe4 0%, #0052cc 100%);
        }

        html[data-tenant-scheme="forest"] {
            --tenant-primary: #059669;
            --tenant-primary-dark: #047857;
            --tenant-primary-soft: rgba(5, 150, 105, 0.12);
            --tenant-sidebar-gradient: linear-gradient(180deg, #10b981 0%, #059669 45%, #064e3b 100%);
        }

        html[data-tenant-scheme="sunset"] {
            --tenant-primary: #d97706;
            --tenant-primary-dark: #b45309;
            --tenant-primary-soft: rgba(217, 119, 6, 0.12);
            --tenant-sidebar-gradient: linear-gradient(180deg, #f59e0b 0%, #d97706 45%, #7c2d12 100%);
        }

        @media (min-width: 768px) {
            #accordionSidebar {
                position: sticky;
                top: 0;
                height: 100vh;
                overflow-y: auto;
                flex-shrink: 0;
            }

            #content-wrapper {
                min-height: 100vh;
            }
        }

        .dark-mode body,
        .dark-mode {
            background-color: #111827;
            color: #e5e7eb;
        }

        .dark-mode #content,
        .dark-mode #content-wrapper,
        .dark-mode .container-fluid,
        .dark-mode .card,
        .dark-mode .card-header,
        .dark-mode .card-footer,
        .dark-mode .modal-content,
        .dark-mode .dropdown-menu,
        .dark-mode .list-group-item,
        .dark-mode .table,
        .dark-mode .table thead th,
        .dark-mode .table td,
        .dark-mode .table th,
        .dark-mode .form-control,
        .dark-mode .form-select,
        .dark-mode .input-group-text,
        .dark-mode .topbar,
        .dark-mode .sticky-footer {
            background-color: #1f2937 !important;
            color: #e5e7eb !important;
            border-color: #374151 !important;
        }

        .dark-mode .bg-white,
        .dark-mode .bg-light,
        .dark-mode .table-light,
        .dark-mode .thead-light th,
        .dark-mode .table thead,
        .dark-mode .table tbody,
        .dark-mode .table tr,
        .dark-mode .border,
        .dark-mode .table-bordered,
        .dark-mode .table-bordered td,
        .dark-mode .table-bordered th {
            background-color: #1f2937 !important;
            color: #e5e7eb !important;
            border-color: #374151 !important;
        }

        .dark-mode .text-gray-800,
        .dark-mode .text-gray-700,
        .dark-mode .text-gray-600,
        .dark-mode .text-dark,
        .dark-mode .card-header,
        .dark-mode .card-footer,
        .dark-mode .dropdown-item,
        .dark-mode .navbar-light .navbar-nav .nav-link,
        .dark-mode .small,
        .dark-mode label,
        .dark-mode .copyright,
        .dark-mode .modal-title,
        .dark-mode .modal-body {
            color: #e5e7eb !important;
        }

        .dark-mode .dropdown-item:hover,
        .dark-mode .dropdown-item:focus,
        .dark-mode .list-group-item:hover {
            background-color: #111827 !important;
        }

        .dark-mode .form-control::placeholder,
        .dark-mode .form-select::placeholder {
            color: #9ca3af;
        }

        .dark-mode .btn-light,
        .dark-mode .btn-outline-secondary,
        .dark-mode .btn-secondary {
            background-color: #374151;
            border-color: #4b5563;
            color: #e5e7eb;
        }

        .dark-mode .navbar-search .form-control {
            background-color: #111827 !important;
            border-color: #374151 !important;
        }

        .theme-toggle-btn {
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #374151;
            border-radius: 999px;
            width: 2.4rem;
            height: 2.4rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .dark-mode .theme-toggle-btn {
            background: #111827;
            border-color: #4b5563;
            color: #fbbf24;
        }

        #accordionSidebar {
            background: var(--tenant-sidebar-gradient) !important;
        }

        .btn-primary,
        .badge-primary,
        .bg-primary,
        .sidebar .nav-link:hover,
        .sidebar .nav-item.active .nav-link {
            background-color: var(--tenant-primary) !important;
            border-color: var(--tenant-primary-dark) !important;
        }

        .text-primary,
        .sidebar .nav-link i,
        .sidebar .sidebar-brand-text,
        .sidebar .sidebar-brand-icon,
        .navbar .text-primary,
        .nav-link .fa,
        .nav-link .fas {
            color: var(--tenant-primary) !important;
        }

        .dark-mode .btn-primary,
        .dark-mode .badge-primary,
        .dark-mode .bg-primary {
            background-color: var(--tenant-primary) !important;
            border-color: var(--tenant-primary-dark) !important;
        }

        .dark-mode .sidebar .nav-item.active .nav-link,
        .dark-mode .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.08) !important;
        }
    </style>

</head>

<body id="page-top">
    @php
        $scheme = $tenantColorScheme ?? 'blue';
    @endphp

    <!-- Debug - Remove after testing -->
    @if(isset($currentTenant))
    <div style="display:none;">
        Tenant: {{ json_encode($currentTenant) }}
    </div>
    @endif

    <!-- Page Wrapper -->
    <script>
        document.documentElement.setAttribute('data-tenant-scheme', @json($scheme));
    </script>
    <div id="wrapper" data-tenant-scheme="{{ $scheme }}">

        <!-- Sidebar -->
        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            @php
                $user = auth()->user();
                $dashboardRoute = route('profile.edit');

                if ($user->role === 'school_admin' && $user->hasPermission('tenant.dashboard.view')) {
                    $dashboardRoute = route('admin.dashboard');
                } elseif ($user->role === 'staff' && $user->hasPermission('tenant.dashboard.view')) {
                    $dashboardRoute = route('staff.dashboard');
                } elseif ($user->role === 'student' && $user->hasPermission('tenant.dashboard.view_own')) {
                    $dashboardRoute = route('student.dashboard');
                }
            @endphp
            @php
                $tenantLogo = $tenantLocalLogoUrl ?? data_get($currentTenant, 'logo_url') ?: data_get($currentTenant, 'logo');
                if ($tenantLogo && !str_starts_with($tenantLogo, 'http://') && !str_starts_with($tenantLogo, 'https://')) {
                    $tenantLogo = asset('storage/' . ltrim($tenantLogo, '/'));
                }
            @endphp
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ $dashboardRoute }}">
                <div class="sidebar-brand-icon d-flex align-items-center justify-content-center" style="width:2.25rem;height:2.25rem;overflow:hidden;">
                    @if($tenantLogo)
                        <img src="{{ $tenantLogo }}" alt="{{ $currentTenant['name'] ?? config('app.name') }} logo" style="width:100%;height:100%;object-fit:contain;">
                    @else
                        <i class="fas fa-laugh-wink rotate-n-15"></i>
                    @endif
                </div>
                <div class="sidebar-brand-text mx-3">{{ $user->role === 'school_admin' ? 'ACAD CLEAR ADMIN' : 'ACAD CLEAR' }}</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            @if(($user->role === 'school_admin' && $user->hasPermission('tenant.dashboard.view')) ||
                ($user->role === 'staff' && $user->hasPermission('tenant.dashboard.view')) ||
                ($user->role === 'student' && $user->hasPermission('tenant.dashboard.view_own')))
                <li class="nav-item {{ request()->routeIs('admin.dashboard') || request()->routeIs('staff.dashboard') || request()->routeIs('student.dashboard') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ $dashboardRoute }}">
                        <i class="fas fa-fw fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
            @endif

            @if($user->role === 'school_admin')
                @php
                    $canManageColleges = $user->hasPermission('tenant.colleges.manage');
                    $canManageDepartments = $user->hasPermission('tenant.departments.manage');
                    $canManageStudents = $user->hasPermission('tenant.students.manage');
                    $canManageStaff = $user->hasPermission('tenant.staff.manage');
                    $canViewReports = $user->hasPermission('tenant.reports.view');
                    $canViewClearances = $user->hasPermission('tenant.clearances.view');
                    $canExportClearances = $user->hasPermission('tenant.clearances.export');
                    $canManageProfile = $user->hasPermission('tenant.profile.manage');
                @endphp

                <!-- Divider -->
                <hr class="sidebar-divider">

                <!-- Heading -->
                <div class="sidebar-heading">
                    Management
                </div>

                <!-- Nav Item - Colleges -->
                @if($canManageColleges)
                    <li class="nav-item {{ request()->routeIs('admin.colleges.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.colleges.index') }}">
                            <i class="fas fa-fw fa-university"></i>
                            <span>Colleges</span></a>
                    </li>
                @endif

                <!-- Nav Item - Departments -->
                @if($canManageDepartments)
                    <li class="nav-item {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.departments.index') }}">
                            <i class="fas fa-fw fa-building"></i>
                            <span>Departments</span></a>
                    </li>
                @endif

                <!-- Nav Item - Students -->
                @if($canManageStudents)
                    <li class="nav-item {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.students.index') }}">
                            <i class="fas fa-fw fa-users"></i>
                            <span>Students</span></a>
                    </li>
                @endif

                <!-- Nav Item - Staff -->
                @if($canManageStaff)
                    <li class="nav-item {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.staff.index') }}">
                            <i class="fas fa-fw fa-user-tie"></i>
                            <span>Staff</span></a>
                    </li>

                    <li class="nav-item {{ request()->routeIs('admin.staff.create') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.staff.create') }}">
                            <i class="fas fa-fw fa-user-plus"></i>
                            <span>Add Role</span></a>
                    </li>
                @endif

                <hr class="sidebar-divider">

                <div class="sidebar-heading">
                    Operations
                </div>

                <!-- Nav Item - Reports -->
                @if($canViewReports)
                    <li class="nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.reports.index') }}">
                            <i class="fas fa-fw fa-file-alt"></i>
                            <span>Reports</span></a>
                    </li>
                @endif

                <!-- Nav Item - Clearances -->
                @if($canViewClearances)
                    <li class="nav-item {{ request()->routeIs('admin.clearances.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.clearances.index') }}">
                            <i class="fas fa-fw fa-list"></i>
                            <span>Clearances</span></a>
                    </li>
                @endif

                <!-- Nav Item - Export -->
                @if($canExportClearances)
                    <li class="nav-item {{ request()->routeIs('admin.clearances.export') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.clearances.export') }}">
                            <i class="fas fa-fw fa-download"></i>
                            <span>Export</span></a>
                    </li>
                @endif

                <hr class="sidebar-divider">

                <div class="sidebar-heading">
                    Account
                </div>

                @if($user->hasPermission('tenant.support_chat.access'))
                    <li class="nav-item {{ request()->routeIs('support.chat*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('support.chat') }}">
                            <i class="fas fa-fw fa-headset"></i>
                            <span>Support Chat</span></a>
                    </li>
                @endif

                <!-- Nav Item - Settings -->
                @if($canManageProfile)
                    <li class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('profile.edit') }}">
                            <i class="fas fa-fw fa-cog"></i>
                            <span>Settings</span></a>
                    </li>
                @endif

            @elseif($user->role === 'staff')
                @php
                    $canViewPlanRequests = $user->hasPermission('tenant.plan_requests.view');
                    $canManageColleges = $user->hasPermission('tenant.colleges.manage');
                    $canManageDepartments = $user->hasPermission('tenant.departments.manage');
                    $canManageStudents = $user->hasPermission('tenant.students.manage');
                    $canManageStaff = $user->hasPermission('tenant.staff.manage');
                    $canViewReports = $user->hasPermission('tenant.reports.view');
                    $canViewClearances = $user->hasPermission('tenant.clearances.view');
                    $canExportClearances = $user->hasPermission('tenant.clearances.export');
                    $canManageProfile = $user->hasPermission('tenant.profile.manage');
                @endphp

                <!-- Divider -->
                <hr class="sidebar-divider">

                <!-- Heading -->
                <div class="sidebar-heading">
                    Management
                </div>

                @if($canViewPlanRequests)
                    <li class="nav-item {{ request()->routeIs('staff.plan-requests.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('staff.plan-requests.index') }}">
                            <i class="fas fa-fw fa-clipboard-list"></i>
                            <span>Plan Requests</span>
                        </a>
                    </li>
                @endif

                @if($canManageColleges)
                    <li class="nav-item {{ request()->routeIs('staff.colleges.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('staff.colleges.index') }}">
                            <i class="fas fa-fw fa-university"></i>
                            <span>Colleges</span>
                        </a>
                    </li>
                @endif

                @if($canManageDepartments)
                    <li class="nav-item {{ request()->routeIs('staff.departments.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('staff.departments.index') }}">
                            <i class="fas fa-fw fa-building"></i>
                            <span>Departments</span>
                        </a>
                    </li>
                @endif

                @if($canManageStudents)
                    <li class="nav-item {{ request()->routeIs('staff.students.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('staff.students.index') }}">
                            <i class="fas fa-fw fa-users"></i>
                            <span>Students</span>
                        </a>
                    </li>
                @endif

                @if($canManageStaff)
                    <li class="nav-item {{ request()->routeIs('staff.staff.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('staff.staff.index') }}">
                            <i class="fas fa-fw fa-user-tie"></i>
                            <span>Staff</span>
                        </a>
                    </li>

                    <li class="nav-item {{ request()->routeIs('staff.staff.create') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('staff.staff.create') }}">
                            <i class="fas fa-fw fa-user-plus"></i>
                            <span>Add Role</span>
                        </a>
                    </li>
                @endif

                <hr class="sidebar-divider">

                <div class="sidebar-heading">
                    Operations
                </div>

                @if($canViewReports)
                    <li class="nav-item {{ request()->routeIs('staff.reports.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('staff.reports.index') }}">
                            <i class="fas fa-fw fa-file-alt"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                @endif

                @if($canViewClearances)
                    <li class="nav-item {{ request()->routeIs('staff.clearances.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('staff.clearances.index') }}">
                            <i class="fas fa-fw fa-list"></i>
                            <span>Clearances</span>
                        </a>
                    </li>
                @endif

                @if($canExportClearances)
                    <li class="nav-item {{ request()->routeIs('staff.clearances.export') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('staff.clearances.export') }}">
                            <i class="fas fa-fw fa-download"></i>
                            <span>Export</span>
                        </a>
                    </li>
                @endif

                <hr class="sidebar-divider">

                <div class="sidebar-heading">
                    Account
                </div>

                @if($user->hasPermission('tenant.support_chat.access'))
                    <li class="nav-item {{ request()->routeIs('support.chat*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('support.chat') }}">
                            <i class="fas fa-fw fa-headset"></i>
                            <span>Support Chat</span></a>
                    </li>
                @endif

                <!-- Nav Item - Settings -->
                @if($canManageProfile)
                    <li class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('profile.edit') }}">
                            <i class="fas fa-fw fa-cog"></i>
                            <span>Settings</span></a>
                    </li>
                @endif
            @else
                <!-- Divider -->
                <hr class="sidebar-divider">

                <!-- Heading -->
                <div class="sidebar-heading">
                    Clearance
                </div>

                <!-- Nav Item - My Clearances -->
                @if($user->hasPermission('tenant.student.clearances.view'))
                    <li class="nav-item {{ request()->routeIs('student.clearances.index') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('student.clearances.index') }}">
                            <i class="fas fa-fw fa-list"></i>
                            <span>My Clearances</span></a>
                    </li>
                @endif

                <!-- Nav Item - Summary -->
                @if($user->hasPermission('tenant.student.clearances.view'))
                    <li class="nav-item {{ request()->routeIs('student.clearances.summary') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('student.clearances.summary') }}">
                            <i class="fas fa-fw fa-chart-bar"></i>
                            <span>Summary</span></a>
                    </li>
                @endif

                <hr class="sidebar-divider">

                <div class="sidebar-heading">
                    Account
                </div>

                @if($user->hasPermission('tenant.support_chat.access'))
                    <li class="nav-item {{ request()->routeIs('support.chat*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('support.chat') }}">
                            <i class="fas fa-fw fa-headset"></i>
                            <span>Support Chat</span></a>
                    </li>
                @endif
            @endif

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Search -->
                    <form
                        class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..."
                                aria-label="Search" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Topbar Navbar -->
                    @php
                        $topbarNotificationCount = 0;
                        $topbarMailCount = 0;
                        $topbarNotifications = collect();
                        $topbarMails = collect();
                        $topbarNotificationLink = '#';
                        $topbarMailLink = route('support.chat');

                        $tenantSlugForSupport = (string) (request()->attributes->get('tenant_slug') ?? data_get($currentTenant ?? null, 'slug', ''));
                        if ($tenantSlugForSupport !== '') {
                            $supportSummary = app(\App\Services\TenantService::class)->getSupportChatSummary($tenantSlugForSupport);
                            $topbarMailCount = (int) ($supportSummary['unread_count'] ?? 0);
                            $topbarMails = collect($supportSummary['recent_messages'] ?? []);
                        }

                        if ($user->role === 'school_admin') {
                            $topbarNotificationLink = route('admin.clearances.index');

                            $topbarNotificationCount = \App\Models\Clearance::query()
                                ->where('status', 'pending')
                                ->whereHas('student', function ($q) use ($user) {
                                    $q->where('college_id', $user->college_id);
                                })
                                ->count();

                            $topbarNotifications = \App\Models\Clearance::query()
                                ->with(['student:id,name', 'department:id,name'])
                                ->where('status', 'pending')
                                ->whereHas('student', function ($q) use ($user) {
                                    $q->where('college_id', $user->college_id);
                                })
                                ->latest()
                                ->take(5)
                                ->get();

                        } elseif ($user->role === 'staff') {
                            $topbarNotificationLink = route('staff.dashboard');

                            if ($user->office_role) {
                                $topbarNotificationCount = \App\Models\ClearanceChecklistItem::query()
                                    ->where('status', 'pending')
                                    ->where('office_role', $user->office_role)
                                    ->whereHas('clearance', function ($q) use ($user) {
                                        $q->where('department_id', $user->department_id);
                                    })
                                    ->count();

                                $topbarNotifications = \App\Models\ClearanceChecklistItem::query()
                                    ->with(['clearance.student:id,name'])
                                    ->where('status', 'pending')
                                    ->where('office_role', $user->office_role)
                                    ->whereHas('clearance', function ($q) use ($user) {
                                        $q->where('department_id', $user->department_id);
                                    })
                                    ->latest()
                                    ->take(5)
                                    ->get();
                            }

                        } else {
                            $topbarNotificationLink = route('student.clearances.index');

                            $topbarNotificationCount = \App\Models\Clearance::query()
                                ->where('student_id', $user->id)
                                ->whereIn('status', ['pending', 'rejected'])
                                ->count();

                            $topbarNotifications = \App\Models\Clearance::query()
                                ->with('department:id,name')
                                ->where('student_id', $user->id)
                                ->whereIn('status', ['pending', 'rejected'])
                                ->latest()
                                ->take(5)
                                ->get();

                        }
                    @endphp
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item no-arrow mx-1 d-flex align-items-center">
                            <button class="theme-toggle-btn" type="button" id="themeToggleBtn" title="Toggle dark mode" aria-label="Toggle dark mode">
                                <i class="fas fa-moon" id="themeToggleIcon"></i>
                            </button>
                        </li>

                        <!-- Tenant Information - NEW -->
                        @if(isset($currentTenant) && $currentTenant)
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="tenantDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-building fa-fw text-primary"></i>
                                <span class="d-none d-lg-inline text-gray-600 small ml-1">{{ Str::limit($currentTenant['name'] ?? 'University', 20) }}</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="tenantDropdown">
                                <div class="dropdown-header bg-primary text-white">
                                    <i class="fas fa-building"></i> {{ $currentTenant['name'] ?? 'University' }}
                                </div>
                                <div class="dropdown-divider"></div>
                                <div class="px-3 py-2">
                                    <small><i class="fas fa-tag"></i> Plan: <strong>{{ $currentTenant['plan']['name'] ?? $currentTenant['plan'] ?? 'N/A' }}</strong></small>
                                </div>
                                @if(isset($currentTenant['subscription']['ends_at']))
                                <div class="px-3 py-2">
                                    <small><i class="fas fa-calendar"></i> Expires: {{ \Carbon\Carbon::parse($currentTenant['subscription']['ends_at'])->format('M d, Y') }}</small>
                                </div>
                                @elseif(isset($currentTenant['subscription_ends_at']))
                                <div class="px-3 py-2">
                                    <small><i class="fas fa-calendar"></i> Expires: {{ \Carbon\Carbon::parse($currentTenant['subscription_ends_at'])->format('M d, Y') }}</small>
                                </div>
                                @endif
                                <div class="px-3 py-2">
                                    <small><i class="fas fa-info-circle"></i> Status: 
                                        @if(($currentTenant['is_active'] ?? true) && ($currentTenant['status'] ?? 'active') === 'active')
                                            <span class="text-success">Active</span>
                                        @else
                                            <span class="text-danger">Inactive</span>
                                        @endif
                                    </small>
                                </div>
                                @if(isset($currentTenant['database']))
                                <div class="px-3 py-2">
                                    <small><i class="fas fa-database"></i> Database: {{ $currentTenant['database'] }}</small>
                                </div>
                                @endif
                                @if(isset($currentTenant['slug']))
                                <div class="px-3 py-2">
                                    <small><i class="fas fa-link"></i> ID: {{ $currentTenant['slug'] }}</small>
                                </div>
                                @endif
                            </div>
                        </li>
                        @endif

                        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                                aria-labelledby="searchDropdown">
                                <form class="form-inline mr-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small"
                                            placeholder="Search for..." aria-label="Search"
                                            aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>

                        <!-- Nav Item - Alerts -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                @if($topbarNotificationCount > 0)
                                    <span class="badge badge-danger badge-counter">{{ $topbarNotificationCount > 99 ? '99+' : $topbarNotificationCount }}</span>
                                @endif
                            </a>
                            <!-- Dropdown - Alerts -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">
                                    Alerts Center
                                </h6>
                                @forelse($topbarNotifications as $notification)
                                    <a class="dropdown-item d-flex align-items-center" href="{{ $topbarNotificationLink }}">
                                        <div class="mr-3">
                                            <div class="icon-circle bg-warning">
                                                <i class="fas fa-bell text-white"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="small text-gray-500">{{ $notification->created_at?->diffForHumans() ?? 'Just now' }}</div>
                                            @if($user->role === 'school_admin')
                                                <span class="font-weight-bold">Pending: {{ $notification->student?->name ?? 'Student' }} - {{ $notification->department?->name ?? 'Department' }}</span>
                                            @elseif($user->role === 'staff')
                                                <span class="font-weight-bold">Pending: {{ $notification->clearance?->student?->name ?? 'Student' }} - {{ $notification->item_name }}</span>
                                            @else
                                                <span class="font-weight-bold">{{ ucfirst($notification->status) }}: {{ $notification->department?->name ?? 'Clearance' }}</span>
                                            @endif
                                        </div>
                                    </a>
                                @empty
                                    <span class="dropdown-item text-center small text-gray-500">No alerts right now</span>
                                @endforelse
                                <a class="dropdown-item text-center small text-gray-500" href="{{ $topbarNotificationLink }}">Show All Alerts</a>
                            </div>
                        </li>

                        <!-- Nav Item - Messages -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-envelope fa-fw"></i>
                                @if($topbarMailCount > 0)
                                    <span class="badge badge-danger badge-counter">{{ $topbarMailCount > 99 ? '99+' : $topbarMailCount }}</span>
                                @endif
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="messagesDropdown">
                                <h6 class="dropdown-header">
                                    Support Inbox
                                </h6>
                                @forelse($topbarMails as $mailItem)
                                    <a class="dropdown-item d-flex align-items-center" href="{{ $topbarMailLink }}">
                                        <div class="dropdown-list-image mr-3">
                                            <i class="fas fa-envelope fa-lg text-primary"></i>
                                        </div>
                                        <div class="font-weight-bold">
                                            <div class="text-truncate">{{ \Illuminate\Support\Str::limit(data_get($mailItem, 'sender_name', 'Super Admin'), 45) }}</div>
                                            <div class="small text-gray-500">{{ \Illuminate\Support\Str::limit(data_get($mailItem, 'message', 'Support message'), 60) }}</div>
                                        </div>
                                    </a>
                                @empty
                                    <span class="dropdown-item text-center small text-gray-500">No support messages right now</span>
                                @endforelse
                                <a class="dropdown-item text-center small text-gray-500" href="{{ $topbarMailLink }}">Open support chat</a>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ auth()->user()->name ?? 'User' }}</span>
                                @if(auth()->user() && auth()->user()->profile_photo_url)
                                    <img src="{{ auth()->user()->profile_photo_url }}" alt="User Avatar" class="img-profile rounded-circle" style="width:32px; height:32px; object-fit:cover;">
                                @else
                                    <i class="fas fa-user-circle fa-fw text-gray-400"></i>
                                @endif
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                @if (Route::has('profile.edit'))
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                        Profile
                                    </a>
                                @endif
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    @yield('content')
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; AcadClear {{ date('Y') }} | v{{ config('app.version', '1.0.0') }}</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <!-- Custom scripts for all pages-->
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
    <script>
        (function () {
            var root = document.documentElement;
            var btn = document.getElementById('themeToggleBtn');
            var icon = document.getElementById('themeToggleIcon');

            function setIcon() {
                if (!icon) return;
                icon.className = root.classList.contains('dark-mode') ? 'fas fa-sun' : 'fas fa-moon';
            }

            setIcon();

            if (btn) {
                btn.addEventListener('click', function () {
                    root.classList.toggle('dark-mode');
                    try {
                        localStorage.setItem('acadclear-theme', root.classList.contains('dark-mode') ? 'dark' : 'light');
                    } catch (e) {}
                    setIcon();
                });
            }
        })();
    </script>

    @stack('scripts')

</body>

</html>