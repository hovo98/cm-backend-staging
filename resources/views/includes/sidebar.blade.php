<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center" href="{{ route('profile') }}">
        <img class="float-left" style="max-width: 144px; height: auto;" src="{{ asset('img/mdash-white-green-yellow.svg') }}" alt="">
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item active">
        <a class="nav-link" href="{{ route('profile') }}">
            <i class="fas fa-fw fa-user"></i>
            <span>Profile</span></a>
    </li>

    <hr class="sidebar-divider">
    <div class="sidebar-heading">
        Users
    </div>

    <li class="nav-item">
        <a class="nav-link pb-0" href="{{ route('users') }}">
            <i class="fas fa-fw fa-user"></i>
            <span>Users</span></a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('users-blocked') }}">
            <i class="fas fa-fw fa-trash"></i>
            <span>Blocked</span></a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Messages
    </div>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('messages') }}">
            <i class="fas fa-fw fa-table"></i>
            <span>Messages</span></a>
    </li>
    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Companies
    </div>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('companies') }}">
            <i class="fas fa-fw fa-table"></i>
            <span>Companies</span></a>
    </li>
    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Export
    </div>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('export-data-view') }}">
            <i class="fas fa-fw fa-user"></i>
            <span>All Data Export</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
