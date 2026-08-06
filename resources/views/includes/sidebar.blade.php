@php
$auth = Auth::user(); 
$sidebarUserName = $auth ? trim($auth->fullName()) : 'IIT Clinic';
$sidebarUserName = $sidebarUserName !== '' ? $sidebarUserName : ($auth->username ?? 'IIT Clinic');
$sidebarGivenName = $auth ? trim(($auth->first_name ?? '') . ' ' . ($auth->middle_name ?? '')) : 'IIT Clinic';
$sidebarLastName = $auth ? trim($auth->last_name ?? '') : '';
$sidebarGivenName = $sidebarGivenName !== '' ? $sidebarGivenName : $sidebarUserName;
$sidebarRoleName = optional(optional($auth)->role)->name ?? 'EMR';
$isStudent = in_array($sidebarRoleName, ['Student', 'Patient']);
$homeRoute = $isStudent ? route('student.dashboard') : route('dashboard');
$sidebarRoleClass = 'role-badge role-' . \Illuminate\Support\Str::slug($sidebarRoleName);
if ($sidebarRoleName === 'Administrator') {
    $sidebarRoleClass .= ' badge-danger';
} elseif ($sidebarRoleName === 'Doctor') {
    $sidebarRoleClass .= ' badge-success';
} elseif ($sidebarRoleName === 'Nurse') {
    $sidebarRoleClass .= ' badge-primary';
}
@endphp
<aside id="mySidepanel" class="sidepanel sidebar desktop-sidebar collapsed">
        <div class="sidepanel-header">
            <a class="sidepanel-brand" href="{{ $homeRoute }}">
                <span class="sidepanel-logo">
                    <img src="{{ $auth->avatar ?? asset('img/no_avatar.jpg') }}" alt="{{ $sidebarUserName }}" onerror="this.onerror=null;this.src='{{ asset('img/no_avatar.jpg') }}';">
                </span>
                <span class="sidepanel-brand-copy">
                    <strong class="sidebar-user-name">
                        <span>{{ $sidebarGivenName }}</span>
                        @if ($sidebarLastName !== '')
                            <span>{{ $sidebarLastName }}</span>
                        @endif
                    </strong>
                    <small class="{{ $sidebarRoleClass }}">{{ $sidebarRoleName }}</small>
                </span>
            </a>
        </div>

        <button type="button" class="sidebar-edge-toggle" onclick="toggleSidebar()" aria-label="Expand sidebar" aria-expanded="false">
            <span class="toggle-arrow" aria-hidden="true">
                <i class="fa fa-angle-right"></i>
            </span>
        </button>

        @unless ($isStudent)
            <div class="sidepanel-search">
                <i class="fa fa-search"></i>
                <input type="search" placeholder="Search...">
            </div>
        @endunless

        <div class="sidepanel-menu">
            @if ($isStudent)
            <div class="sidepanel-section-label">Patient Portal</div>
            <a class="sidebar-tooltip-trigger {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}" data-toggle="tooltip" data-placement="right" data-container="body" title="Dashboard" aria-label="Dashboard">
                <i class="fa fa-home"></i><span>Dashboard</span>
            </a>
            <a class="sidebar-tooltip-trigger {{ request()->routeIs('student.complaints.*') ? 'active' : '' }}" href="{{ route('student.complaints.index') }}" data-toggle="tooltip" data-placement="right" data-container="body" title="My Complaints" aria-label="My Complaints">
                <i class="fa fa-file-text-o"></i><span>My Complaints</span>
            </a>
            <a class="sidebar-tooltip-trigger {{ request()->routeIs('student.medical-history') ? 'active' : '' }}" href="{{ route('student.medical-history') }}" data-toggle="tooltip" data-placement="right" data-container="body" title="Health History" aria-label="Health History">
                <i class="fa fa-heartbeat"></i><span>My Health Record</span>
            </a>
            <a class="sidebar-tooltip-trigger {{ request()->routeIs('student.prescriptions.*') ? 'active' : '' }}" href="{{ route('student.prescriptions.index') }}" data-toggle="tooltip" data-placement="right" data-container="body" title="My Prescriptions" aria-label="My Prescriptions">
                <i class="fa fa-medkit"></i><span>My Prescriptions</span>
            </a>
            @if(optional($auth->patientAccount)->patient_type !== 'dependent')
            @if(optional(auth()->user()->patientAccount)->patient_type === 'faculty')<a class="sidebar-tooltip-trigger {{ request()->routeIs('patient.dependents.*') ? 'active' : '' }}" href="{{ route('patient.dependents.index') }}" data-toggle="tooltip" data-placement="right" data-container="body" title="My Dependents" aria-label="My Dependents">
                <i class="fa fa-user-plus"></i><span>My Dependents</span>
            </a>@endif
            @endif
            <a class="sidebar-tooltip-trigger {{ request()->routeIs('student.profile') ? 'active' : '' }}" href="{{ route('student.profile') }}" data-toggle="tooltip" data-placement="right" data-container="body" title="Profile" aria-label="Profile">
                <i class="fa fa-user-o"></i><span>Profile</span>
            </a>
            @else
            <div class="sidepanel-section-label">Core Workspace</div>
            <a class="sidebar-tooltip-trigger {{ request()->is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" data-toggle="tooltip" data-placement="right" data-container="body" title="Dashboard" aria-label="Dashboard">
                <i class="fa fa-line-chart"></i><span>Dashboard</span>
            </a>
            <a class="sidebar-tooltip-trigger {{ request()->is('profile/*') ? 'active' : '' }}" href="{{ route('profile.show', auth()->user()->id) }}" data-toggle="tooltip" data-placement="right" data-container="body" title="Profile" aria-label="Profile">
                <i class="fa fa-user-o"></i><span>Profile</span>
            </a>

            <div class="sidepanel-section-label">Management</div>
            <a class="sidebar-tooltip-trigger {{ request()->is('patients*') ? 'active' : '' }}" href="/patients" data-toggle="tooltip" data-placement="right" data-container="body" title="Manage Patients" aria-label="Manage Patients">
                <i class="fa fa-address-card-o"></i><span>Manage Patients</span>
            </a>
            <a class="sidebar-tooltip-trigger {{ request()->is('student-complaints*') ? 'active' : '' }}" href="{{ route('student-complaints.index') }}" data-toggle="tooltip" data-placement="right" data-container="body" title="{{ $sidebarRoleName === 'Doctor' ? 'Consultation Queue' : 'Student Intake Queue' }}" aria-label="{{ $sidebarRoleName === 'Doctor' ? 'Consultation Queue' : 'Student Intake Queue' }}">
                <i class="fa fa-inbox"></i><span>{{ $sidebarRoleName === 'Doctor' ? 'Consultation Queue' : 'Student Intake Queue' }}</span>
            </a>
            @if ($sidebarRoleName === 'Administrator')
            <a class="sidebar-tooltip-trigger {{ request()->is('users*') ? 'active' : '' }}" href="/users" data-toggle="tooltip" data-placement="right" data-container="body" title="Manage Users" aria-label="Manage Users">
                <i class="fa fa-id-badge"></i><span>Manage Users</span>
            </a>
            <a class="sidebar-tooltip-trigger {{ request()->routeIs('admin.monitoring.*') ? 'active' : '' }}" href="{{ route('admin.monitoring.index') }}" data-toggle="tooltip" data-placement="right" data-container="body" title="System Monitoring" aria-label="System Monitoring">
                <i class="fa fa-heartbeat"></i><span>System Monitoring</span>
            </a>
            @endif
            @if ($sidebarRoleName === 'Doctor')
            <a class="sidebar-tooltip-trigger {{ request()->routeIs('medical-records.*') ? 'active' : '' }}" href="{{ route('medical-records.index') }}" data-toggle="tooltip" data-placement="right" data-container="body" title="Medical Records" aria-label="Medical Records">
                <i class="fa fa-file-text-o"></i><span>Medical Records</span>
            </a>
            @else
            <a class="sidebar-tooltip-trigger {{ request()->is('services*') ? 'active' : '' }}" href="/services" data-toggle="tooltip" data-placement="right" data-container="body" title="Manage Services" aria-label="Manage Services">
                <i class="fa fa-briefcase"></i><span>Manage Services</span>
            </a>
            @endif

            <div class="sidepanel-section-label">Support</div>
            <a class="sidebar-tooltip-trigger {{ request()->routeIs('support.problem.*') ? 'active' : '' }}" href="{{ route('support.problem.create') }}" data-toggle="tooltip" data-placement="right" data-container="body" title="Report a Problem" aria-label="Report a Problem">
                <i class="fa fa-exclamation-circle"></i><span>Report a Problem</span>
            </a>
            <a class="sidebar-tooltip-trigger {{ request()->is('doctors') ? 'active' : '' }}" href="/doctors" data-toggle="tooltip" data-placement="right" data-container="body" title="Clinic Staff" aria-label="Clinic Staff">
                <i class="fa fa-stethoscope"></i><span>Clinic Staff</span>
            </a>
            <a class="sidebar-tooltip-trigger {{ request()->is('about') ? 'active' : '' }}" href="/about" data-toggle="tooltip" data-placement="right" data-container="body" title="About" aria-label="About">
                <i class="fa fa-info-circle"></i><span>About</span>
            </a>
            <a class="sidebar-tooltip-trigger {{ request()->is('contact') ? 'active' : '' }}" href="/contact" data-toggle="tooltip" data-placement="right" data-container="body" title="Contact" aria-label="Contact">
                <i class="fa fa-envelope-o"></i><span>Contact</span>
            </a>
            @endif
        </div>

        <div class="sidepanel-footer">
            <a class="sidebar-tooltip-trigger" href="{{ route('logout') }}" data-toggle="tooltip" data-placement="right" data-container="body" title="Logout" aria-label="Logout" data-confirm="Are you sure you want to log out?" data-confirm-title="Confirm logout" data-confirm-form="logout-form">
                <i class="fa fa-sign-out"></i><span>Logout</span>
            </a>
            <div class="theme-mode-row">
                <span class="theme-mode-label"><i class="fa fa-moon-o"></i><span>Dark Mode</span></span>
                <button type="button" class="theme-toggle" onclick="toggleThemeMode(this)" aria-label="Toggle dark mode">
                    <span></span>
                </button>
            </div>
            @unless ($isStudent)
                <small class="sidebar-copyright">Electronic Medical Record &copy; 2026</small>
            @endunless
        </div>
    </aside>

    <script>

    function toggleSidebar() {
      const sidepanel = document.getElementById("mySidepanel");
      $('.sidebar-tooltip-trigger').tooltip('hide');
      sidepanel.classList.toggle("collapsed");
      document.body.classList.toggle("sidebar-collapsed", sidepanel.classList.contains("collapsed"));
      syncSidebarToggle();
      syncSidebarTooltips();
    }

    function syncSidebarToggle() {
      const sidepanel = document.getElementById("mySidepanel");
      const toggle = sidepanel.querySelector(".sidebar-edge-toggle");
      const arrow = toggle.querySelector(".toggle-arrow i");
      const isCollapsed = sidepanel.classList.contains("collapsed");

      toggle.setAttribute("aria-expanded", isCollapsed ? "false" : "true");
      toggle.setAttribute("aria-label", isCollapsed ? "Expand sidebar" : "Collapse sidebar");
      arrow.classList.toggle("fa-angle-right", isCollapsed);
      arrow.classList.toggle("fa-angle-left", !isCollapsed);
    }

    function syncSidebarTooltips() {
      const isCollapsed = document.getElementById("mySidepanel").classList.contains("collapsed");
      $('.sidebar-tooltip-trigger').tooltip(isCollapsed ? 'enable' : 'disable');
    }

    function setThemeMode(isDark) {
      const themeButton = document.querySelector(".theme-toggle");
      document.body.classList.toggle("dark-mode", isDark);
      localStorage.setItem("emr-theme", isDark ? "dark" : "light");

      if (themeButton) {
        themeButton.classList.toggle("is-active", isDark);
        themeButton.setAttribute("aria-pressed", isDark ? "true" : "false");
      }
    }

    function toggleThemeMode(button) {
      setThemeMode(!button.classList.contains("is-active"));
    }

    setThemeMode(localStorage.getItem("emr-theme") === "dark");
    syncSidebarToggle();
    
    </script>
