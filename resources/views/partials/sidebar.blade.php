<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/">
        <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-tooth"></i></div>
        <div class="sidebar-brand-text mx-3">Ponce Miranda</div>
    </a>

    <hr class="sidebar-divider my-0">

    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'doctor')
        
        <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.dashboard') }}">
                <i class="fas fa-fw fa-tachometer-alt"></i><span>Admin Dashboard</span>
            </a>
        </li>

        <hr class="sidebar-divider">
        <div class="sidebar-heading">Clinic Operations</div>

        <li class="nav-item"><a class="nav-link" href="{{ route('admin.appointments.index') }}"><i class="fas fa-calendar-check"></i><span>Appointments</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('admin.services.index') }}"><i class="fas fa-medkit"></i><span>Services</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('admin.schedules.index') }}"><i class="fas fa-clock"></i><span>Calendar</span></a></li>
        
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Admin</div>
        <li class="nav-item"><a class="nav-link" href="{{ route('admin.patients.index') }}"><i class="fas fa-users"></i><span>Patients</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('admin.staff.index') }}"><i class="fas fa-user-md"></i><span>Staff</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('admin.reports.index') }}"><i class="fas fa-chart-area"></i><span>Reports</span></a></li>

    @else

        <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="fas fa-fw fa-home"></i>
                <span>My Home</span>
            </a>
        </li>

        <hr class="sidebar-divider">

        <li class="nav-item">
            <a class="nav-link" href="{{ route('patient.booking.step1') }}">
                <i class="fas fa-fw fa-calendar-plus"></i>
                <span>Book Appointment</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('patient.history') }}">
                <i class="fas fa-fw fa-history"></i>
                <span>My History</span>
            </a>
        </li>

    @endif

    <hr class="sidebar-divider d-none d-md-block">
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>