@php
$auth = Auth::user();
$activeUsers = \App\User::getActive();
$sidebarRoleName = optional(optional($auth)->role)->name ?? 'EMR';
$isStudent = $sidebarRoleName === 'Student';
$homeRoute = $isStudent ? route('student.dashboard') : route('dashboard');
$showClinicNotifications = (bool) $auth;
$topbarNotifications = $showClinicNotifications
    ? \App\ClinicNotification::forUser($auth)->latest()->take(8)->get()
    : collect();
$topbarUnreadCount = $showClinicNotifications
    ? \App\ClinicNotification::forUser($auth)->where('is_read', false)->count()
    : 0;
@endphp

<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm sticky-top emr-navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ $homeRoute }}">
            <span class="brand-mark"><i class="fa fa-university"></i><i class="fa fa-plus"></i></span>
            <span>{{ config('app.name', 'EMR') }}</span>
        </a>
        @if ($isStudent)
            <a class="mobile-patient-notifications" href="{{ route('notifications.index') }}" aria-label="Notifications, {{ $topbarUnreadCount }} unread">
                <i class="fa fa-bell-o"></i>
                @if($topbarUnreadCount)<span>{{ $topbarUnreadCount }}</span>@endif
            </a>
            <button id="mobileMenuToggle" class="mobile-menu-btn" type="button" aria-controls="studentMobileDrawer" aria-expanded="false" aria-label="Open patient menu">
                <i class="fa fa-bars"></i>
            </button>
        @else
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>
        @endif

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto"></ul>
            <ul class="navbar-nav ml-auto">
                @unless ($isStudent)
                    <li class="nav-item active-users-count {{ $activeUsers->count() > 0 ? 'has-active-users' : 'no-active-users' }}">
                        <span><i class="fa fa-circle"></i>{{ $activeUsers->count() }} Active</span>
                    </li>
                @endunless
                @if ($showClinicNotifications)
                    <li class="nav-item dropdown clinic-notification-menu" data-notification-menu data-unread-url="{{ route('notifications.unread') }}">
                        <a class="nav-link clinic-notification-toggle" href="#" id="clinicNotificationDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Clinic notifications">
                            <i class="fa fa-bell-o"></i>
                            <span class="clinic-notification-count {{ $topbarUnreadCount ? '' : 'is-empty' }}" data-notification-count>{{ $topbarUnreadCount }}</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right clinic-notification-dropdown" aria-labelledby="clinicNotificationDropdown">
                            <div class="clinic-notification-heading"><strong>Notifications</strong><span><form method="POST" action="{{ route('notifications.read-all') }}" class="d-inline">@csrf<button type="submit" class="notification-read-all">Read all</button></form> <a href="{{ route('notifications.index') }}">View all</a></span></div>
                            <div class="clinic-notification-list" data-notification-list>
                                @forelse ($topbarNotifications as $notification)
                                    <article class="clinic-notification-item {{ $notification->is_read ? '' : 'is-unread' }}">
                                        <div><strong>{{ $notification->title }}</strong><p>{{ $notification->message }}</p><time>{{ $notification->created_at->diffForHumans() }}</time></div>
                                        <div class="clinic-notification-actions">
                                            @unless ($notification->is_read)<form method="POST" action="{{ route('notifications.read', $notification) }}">@csrf<button type="submit">Mark read</button></form>@endunless
                                            @if ($notification->related_consultation_id)<a href="{{ route('student-complaints.index') }}">View Queue</a>@endif
                                        </div>
                                    </article>
                                @empty
                                    <p class="clinic-notification-empty">No notifications yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </li>
                @endif
                <img src="{{ $auth->avatar ?? asset('img/no_avatar.jpg') }}" alt="Avatar" class="avatar" onerror="this.onerror=null;this.src='{{ asset('img/no_avatar.jpg') }}';">
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                    </li>
                @else
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ $auth->first_name . " " . $auth->last_name }} <span class="caret"></span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                data-confirm="Are you sure you want to log out?"
                                data-confirm-title="Confirm logout"
                                data-confirm-form="logout-form">
                                {{ __('Logout') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>

@if ($isStudent)
    <div id="studentMobileDrawerOverlay" class="mobile-drawer-overlay" aria-hidden="true"></div>
    <nav id="studentMobileDrawer" class="mobile-drawer" aria-label="Patient mobile menu" aria-hidden="true">
        <div class="mobile-drawer-header">
            <div>
                <span class="mobile-drawer-kicker">Patient Portal</span>
                <strong>{{ config('app.name', 'EMR') }}</strong>
            </div>
            <button id="studentMobileDrawerClose" type="button" aria-label="Close student menu">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <div class="mobile-drawer-links">
            <a class="{{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}"><i class="fa fa-home"></i><span>Dashboard</span></a>
            <a class="{{ request()->routeIs('student.complaints.*') ? 'active' : '' }}" href="{{ route('student.complaints.index') }}"><i class="fa fa-file-text-o"></i><span>My Complaints</span></a>
            <a class="{{ request()->routeIs('student.medical-history') ? 'active' : '' }}" href="{{ route('student.medical-history') }}"><i class="fa fa-heartbeat"></i><span>My Health Record</span></a>
            <a class="{{ request()->routeIs('student.prescriptions.*') ? 'active' : '' }}" href="{{ route('student.prescriptions.index') }}"><i class="fa fa-medkit"></i><span>My Prescriptions</span></a>
            <a class="{{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}"><i class="fa fa-bell-o"></i><span>Notifications</span>@if($topbarUnreadCount)<strong class="drawer-unread-count">{{ $topbarUnreadCount }}</strong>@endif</a>
            @if(optional(Auth::user()->patientAccount)->patient_type !== 'dependent')
            <a class="{{ request()->routeIs('patient.dependents.*') ? 'active' : '' }}" href="{{ route('patient.dependents.index') }}"><i class="fa fa-user-plus"></i><span>My Dependents</span></a>
            @endif
            <a class="{{ request()->routeIs('student.profile') ? 'active' : '' }}" href="{{ route('student.profile') }}"><i class="fa fa-user-o"></i><span>Profile</span></a>
        </div>

        <a class="mobile-drawer-logout" href="{{ route('logout') }}" data-confirm="Are you sure you want to log out?" data-confirm-title="Confirm logout" data-confirm-form="logout-form">
            <i class="fa fa-sign-out"></i><span>Logout</span>
        </a>
    </nav>
@endif

@if ($showClinicNotifications)
<script>
(function () {
    var menu = document.querySelector('[data-notification-menu]');
    if (!menu || !window.fetch) return;
    var count = menu.querySelector('[data-notification-count]');
    var list = menu.querySelector('[data-notification-list]');
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function render(data) {
        count.textContent = data.unread_count;
        count.classList.toggle('is-empty', data.unread_count === 0);
        list.innerHTML = '';
        if (!data.notifications.length) {
            var empty = document.createElement('p');
            empty.className = 'clinic-notification-empty';
            empty.textContent = 'No unread notifications.';
            list.appendChild(empty);
            return;
        }
        data.notifications.forEach(function (notification) {
            var item = document.createElement('article');
            item.className = 'clinic-notification-item' + (notification.is_read ? '' : ' is-unread');
            var copy = document.createElement('div');
            var title = document.createElement('strong'); title.textContent = notification.title;
            var message = document.createElement('p'); message.textContent = notification.message;
            var time = document.createElement('time'); time.textContent = notification.timestamp;
            copy.append(title, message, time);
            var actions = document.createElement('div'); actions.className = 'clinic-notification-actions';
            var queue = document.createElement('a'); queue.href = notification.view_queue_url; queue.textContent = 'View Queue';
            if (!notification.is_read) {
                var read = document.createElement('button'); read.type = 'button'; read.textContent = 'Mark read'; read.dataset.readUrl = notification.read_url;
                actions.appendChild(read);
            }
            actions.appendChild(queue); item.append(copy, actions); list.appendChild(item);

            var toast = document.querySelector('[data-consultation-toast]');
            var seenKey = 'clinic-notification-seen-' + notification.id;
            if (toast && !notification.is_read && !sessionStorage.getItem(seenKey)) {
                toast.querySelector('[data-toast-title]').textContent = notification.title;
                toast.querySelector('[data-toast-message]').textContent = notification.message;
                toast.querySelector('[data-toast-queue]').href = notification.view_queue_url;
                toast.classList.add('is-visible');
                sessionStorage.setItem(seenKey, '1');
            }
        });
    }

    function poll() {
        fetch(menu.dataset.unreadUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function (response) { return response.json(); })
            .then(render)
            .catch(function () {});
    }

    list.addEventListener('click', function (event) {
        var button = event.target.closest('[data-read-url]');
        if (!button) return;
        fetch(button.dataset.readUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } }).then(poll);
    });
    document.addEventListener('click', function (event) {
        if (event.target.closest('[data-toast-close]')) document.querySelector('[data-consultation-toast]').classList.remove('is-visible');
    });
    poll();
    window.setInterval(function () { if (!document.hidden) poll(); }, {{ config('clinic_queue.staff_poll_seconds',30) * 1000 }});
    document.addEventListener('visibilitychange', function () { if (!document.hidden) poll(); });
})();
</script>
@endif
