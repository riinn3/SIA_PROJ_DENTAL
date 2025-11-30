<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('admin.dashboard') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-tooth"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Ponce Miranda</div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Clinic Operations
    </div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAppt" aria-expanded="true">
            <i class="fas fa-fw fa-calendar-check"></i>
            <span>Appointments</span>
        </a>
        <div id="collapseAppt" class="collapse" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Manage:</h6>
                <a class="collapse-item" href="{{ route('admin.appointments.index', ['status' => 'pending']) }}">Pending Requests</a>
                <a class="collapse-item" href="{{ route('admin.appointments.index', ['status' => 'confirmed']) }}">Confirmed Schedule</a>
                <a class="collapse-item" href="{{ route('admin.appointments.index', ['status' => 'completed']) }}">History / Done</a>
                <a class="collapse-item text-danger" href="{{ route('admin.appointments.index', ['status' => 'cancelled']) }}">Archived / Cancelled</a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.services.index') }}">
            <i class="fas fa-fw fa-medkit"></i>
            <span>Services List</span>
        </a>    
    </li>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.schedules.index') }}">
            <i class="fas fa-fw fa-clock"></i>
            <span>Clinic Calendar</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Admin
    </div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUsers">
            <i class="fas fa-fw fa-users"></i>
            <span>Users</span>
        </a>
        <div id="collapseUsers" class="collapse" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="#">Active Patients</a>
                <a class="collapse-item" href="#">Staff & Admins</a>
                <a class="collapse-item text-danger" href="#">Deactivated / Archives</a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="#">
            <i class="fas fa-fw fa-chart-area"></i>
            <span>Reports & Analytics</span>
        </a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>