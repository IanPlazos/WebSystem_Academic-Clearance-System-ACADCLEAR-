<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('super-admin.dashboard') }}">
        <div class="sidebar-brand-icon">
            <i class="fas fa-crown"></i>
        </div>
        <div class="sidebar-brand-text mx-3">AcadClear Central</div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('super-admin.dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Management</div>

    <li class="nav-item {{ request()->routeIs('super-admin.tenants.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('super-admin.tenants.index') }}">
            <i class="fas fa-fw fa-building"></i>
            <span>Universities</span>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('super-admin.plans.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('super-admin.plans.index') }}">
            <i class="fas fa-fw fa-tag"></i>
            <span>Pricing Plans</span>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('super-admin.plan-requests.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('super-admin.plan-requests.index') }}">
            <i class="fas fa-fw fa-inbox"></i>
            <span>Plan Requests</span>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('super-admin.support-chat.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('super-admin.support-chat.index') }}">
            <i class="fas fa-fw fa-comments"></i>
            <span>Support Chat</span>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('super-admin.subscriptions.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('super-admin.subscriptions.index') }}">
            <i class="fas fa-fw fa-credit-card"></i>
            <span>Subscriptions</span>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('super-admin.payments.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('super-admin.payments.index') }}">
            <i class="fas fa-fw fa-money-bill"></i>
            <span>Payments</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Analytics</div>

    <li class="nav-item {{ request()->routeIs('super-admin.analytics.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('super-admin.analytics.index') }}">
            <i class="fas fa-fw fa-chart-line"></i>
            <span>Reports & Analytics</span>
        </a>
    </li>
</ul>