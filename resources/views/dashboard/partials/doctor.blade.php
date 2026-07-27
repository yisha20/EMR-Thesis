@php
    $calendarEvents = collect($recentConsultations ?? [])
        ->filter(function ($consultation) {
            return $consultation->date_of_consultation;
        })
        ->map(function ($consultation) {
            $date = $consultation->date_of_consultation instanceof \Carbon\Carbon
                ? $consultation->date_of_consultation
                : \Carbon\Carbon::parse($consultation->date_of_consultation);
            $patientName = trim(optional($consultation->patient)->first_name . ' ' . optional($consultation->patient)->last_name);

            return [
                'date' => $date->format('Y-m-d'),
                'label' => $consultation->chief_complaint ?: ($consultation->performed_service ?: 'Consultation'),
                'meta' => $patientName ?: 'Unknown patient',
            ];
        })
        ->values();
    $doctorKpiMeta = [
        'Pending Consultations' => ['subtitle' => 'Needs review', 'class' => 'kpi-consultations', 'icon' => 'fa-clock-o'],
        'In Consultation' => ['subtitle' => 'Currently in progress', 'class' => 'kpi-pending', 'icon' => 'fa-stethoscope'],
        'Completed Today' => ['subtitle' => 'Finished today', 'class' => 'kpi-patients', 'icon' => 'fa-check'],
        'Total Patients' => ['subtitle' => 'Registered patients', 'class' => 'kpi-reviewed', 'icon' => 'fa-users'],
    ];
@endphp

<div class="doctor-dashboard-layout">
    @include('dashboard.partials.queue-workspace')
    <section class="doctor-dashboard-grid">
        <article class="dashboard-panel doctor-calendar-card" data-doctor-calendar-events='@json($calendarEvents)'>
            <div class="doctor-panel-heading">
                <span class="doctor-panel-icon"><i class="fa fa-calendar-check-o"></i></span>
                <div>
                    <p class="eyebrow">Clinical calendar</p>
                    <h2 data-doctor-calendar-title>{{ now()->format('F Y') }}</h2>
                </div>
                <div class="doctor-calendar-actions" aria-label="Calendar month navigation">
                    <button type="button" data-doctor-calendar-prev aria-label="Previous month"><i class="fa fa-angle-left"></i></button>
                    <button type="button" data-doctor-calendar-next aria-label="Next month"><i class="fa fa-angle-right"></i></button>
                </div>
            </div>
            <div class="doctor-calendar-weekdays" aria-hidden="true">
                <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
            </div>
            <div class="doctor-calendar-grid" data-doctor-calendar-grid aria-label="Clinical calendar dates"></div>
            <div class="doctor-calendar-clock" aria-label="Current time">
                <strong>Current Time</strong>
                <span data-doctor-calendar-clock-time>{{ now()->format('g:i A') }}</span>
                <small data-doctor-calendar-clock-date>{{ now()->format('l, F j') }}</small>
            </div>
        </article>

        <aside class="dashboard-panel doctor-analytics-card" hidden aria-hidden="true">
            <div class="dashboard-panel-header">
                <div>
                    <p class="eyebrow">At a glance</p>
                    <h2>{{ $analytics['title'] }}</h2>
                    <span>{{ $analytics['subtitle'] }}</span>
                </div>
            </div>

            @php
                $lineItems = collect($analytics['items'])->values();
                $lineMax = max(1, (int) $lineItems->max('value'));
                $linePoints = $lineItems->map(function ($item, $index) use ($lineItems, $lineMax) {
                    $x = $lineItems->count() > 1 ? 20 + (($index / ($lineItems->count() - 1)) * 260) : 150;
                    $y = 125 - (($item['value'] / $lineMax) * 95);
                    return round($x, 1) . ',' . round($y, 1);
                })->implode(' ');
            @endphp
            <div class="dashboard-line-chart doctor-line-chart" role="img" aria-label="Daily consultation trend for the last seven days">
                <svg viewBox="0 0 300 150" preserveAspectRatio="none" aria-hidden="true">
                    <line x1="20" y1="125" x2="280" y2="125" class="chart-axis"></line>
                    <polyline points="{{ $linePoints }}" class="chart-line"></polyline>
                    @foreach ($lineItems as $index => $item)
                        @php
                            $x = $lineItems->count() > 1 ? 20 + (($index / ($lineItems->count() - 1)) * 260) : 150;
                            $y = 125 - (($item['value'] / $lineMax) * 95);
                        @endphp
                        <circle cx="{{ $x }}" cy="{{ $y }}" r="4" class="chart-point"><title>{{ $item['date'] }}: {{ $item['value'] }}</title></circle>
                    @endforeach
                </svg>
                <div class="dashboard-line-labels">
                    @foreach ($lineItems as $item)
                        <span title="{{ $item['date'] }}"><strong>{{ $item['value'] }}</strong>{{ $item['label'] }}</span>
                    @endforeach
                </div>
            </div>
        </aside>

        <section class="doctor-kpi-grid" aria-label="Doctor dashboard key performance indicators">
            @foreach ($kpis as $kpi)
                @php $kpiMeta = $doctorKpiMeta[$kpi['label']] ?? ['subtitle' => 'Current total', 'class' => 'kpi-default', 'icon' => $kpi['icon'] ?? 'fa-line-chart']; @endphp
                <article class="kpi-glass-card {{ $kpiMeta['class'] }}">
                    <div>
                        <div class="kpi-topline">
                            <span class="kpi-dot"></span>
                            <span class="kpi-title">{{ $kpi['label'] }}</span>
                        </div>
                        <div class="kpi-subtitle">{{ $kpiMeta['subtitle'] }}</div>
                    </div>

                    <div class="kpi-value">{{ number_format($kpi['value']) }}</div>
                    <div class="kpi-circle-icon"><i class="fa {{ $kpiMeta['icon'] }}"></i></div>
                </article>
            @endforeach
        </section>
    </section>

    <section class="doctor-dashboard-shortcuts shortcut-stack" aria-label="Quick actions">
        <a href="{{ route('patients.index') }}" class="shortcut-dashboard-card">
            <span class="shortcut-card-media">
                <img src="{{ asset('img/shortcut-patients.svg') }}" alt="Manage Patients">
            </span>
            <div>
                <h3 class="shortcut-card-title">Manage Patients</h3>
                <p class="shortcut-card-description">View patient records and consultations.</p>
            </div>
            <span class="shortcut-card-action"><i class="fa fa-angle-right"></i></span>
        </a>
        <a href="{{ route('student-complaints.index') }}" class="shortcut-dashboard-card">
            <span class="shortcut-card-media">
                <img src="{{ asset('img/shortcut-intake.svg') }}" alt="Consultation Queue">
            </span>
            <div>
                <h3 class="shortcut-card-title">Consultation Queue</h3>
                <p class="shortcut-card-description">Review student concerns and begin consultations.</p>
            </div>
            <span class="shortcut-card-action"><i class="fa fa-angle-right"></i></span>
        </a>
        <a href="{{ route('medical-records.index') }}" class="shortcut-dashboard-card">
            <span class="shortcut-card-media">
                <img src="{{ asset('img/shortcut-services.svg') }}" alt="Medical Records">
            </span>
            <div>
                <h3 class="shortcut-card-title">Medical Records</h3>
                <p class="shortcut-card-description">Review consultation history and clinical records.</p>
            </div>
            <span class="shortcut-card-action"><i class="fa fa-angle-right"></i></span>
        </a>
    </section>

    <article class="dashboard-panel doctor-consultation-card">
        <div class="dashboard-panel-header">
            <div>
                <p class="eyebrow">Clinical activity</p>
                <h2>Recent Consultations</h2>
            </div>
            <a href="{{ route('medical-records.index') }}">View all</a>
        </div>

        <div class="doctor-consultation-list">
            @forelse ($recentConsultations as $consultation)
                @php
                    $patientName = trim(optional($consultation->patient)->first_name . ' ' . optional($consultation->patient)->last_name) ?: 'Unknown patient';
                    $status = $consultation->consultation_status ?: 'Completed';
                @endphp
                <article class="doctor-consultation-item">
                    <span class="doctor-consultation-icon"><i class="fa fa-user-md"></i></span>
                    <div>
                        <div class="doctor-consultation-title">
                            <strong>{{ $patientName }}</strong>
                            <span class="complaint-status status-{{ \Illuminate\Support\Str::slug($status) }}">{{ $status }}</span>
                        </div>
                        <p>{{ $consultation->chief_complaint ?: 'Not specified' }}</p>
                        <time>{{ optional($consultation->date_of_consultation)->format('M j, Y') ?: 'Not set' }}</time>
                    </div>
                </article>
            @empty
                @include('includes.empty-state', ['title' => 'No recent consultations.', 'icon' => 'fa-stethoscope'])
            @endforelse
        </div>
    </article>
</div>

@push('js')
<script>
(function () {
    var card = document.querySelector('.doctor-calendar-card');
    if (!card) {
        return;
    }

    var events = JSON.parse(card.getAttribute('data-doctor-calendar-events') || '[]');
    var eventMap = events.reduce(function (map, event) {
        map[event.date] = map[event.date] || [];
        map[event.date].push(event);
        return map;
    }, {});
    var today = new Date();
    var viewedDate = new Date(today.getFullYear(), today.getMonth(), 1);
    var title = card.querySelector('[data-doctor-calendar-title]');
    var grid = card.querySelector('[data-doctor-calendar-grid]');
    var formatter = new Intl.DateTimeFormat('en', { month: 'long', year: 'numeric' });
    var clockTime = card.querySelector('[data-doctor-calendar-clock-time]');
    var clockDate = card.querySelector('[data-doctor-calendar-clock-date]');
    var clockTimeFormatter = new Intl.DateTimeFormat('en', { hour: 'numeric', minute: '2-digit', second: '2-digit' });
    var clockDateFormatter = new Intl.DateTimeFormat('en', { weekday: 'long', month: 'long', day: 'numeric' });

    function dateKey(date) {
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var day = String(date.getDate()).padStart(2, '0');
        return date.getFullYear() + '-' + month + '-' + day;
    }

    function renderCalendar() {
        var year = viewedDate.getFullYear();
        var month = viewedDate.getMonth();
        var firstDay = new Date(year, month, 1);
        var daysInMonth = new Date(year, month + 1, 0).getDate();
        var todayKey = dateKey(today);

        title.textContent = formatter.format(viewedDate);
        grid.innerHTML = '';

        for (var blank = 0; blank < firstDay.getDay(); blank++) {
            var spacer = document.createElement('span');
            spacer.className = 'doctor-calendar-day is-empty';
            grid.appendChild(spacer);
        }

        for (var day = 1; day <= daysInMonth; day++) {
            var cellDate = new Date(year, month, day);
            var key = dateKey(cellDate);
            var cell = document.createElement('button');
            cell.type = 'button';
            cell.className = 'doctor-calendar-day';
            cell.textContent = day;
            if (key === todayKey) {
                cell.classList.add('is-today');
            }
            if (eventMap[key]) {
                cell.classList.add('has-event');
                cell.setAttribute('aria-label', day + ', consultation scheduled');
            }
            grid.appendChild(cell);
        }
    }

    function renderClock() {
        var now = new Date();
        clockTime.textContent = clockTimeFormatter.format(now);
        clockDate.textContent = clockDateFormatter.format(now);
    }

    card.querySelector('[data-doctor-calendar-prev]').addEventListener('click', function () {
        viewedDate = new Date(viewedDate.getFullYear(), viewedDate.getMonth() - 1, 1);
        renderCalendar();
    });

    card.querySelector('[data-doctor-calendar-next]').addEventListener('click', function () {
        viewedDate = new Date(viewedDate.getFullYear(), viewedDate.getMonth() + 1, 1);
        renderCalendar();
    });

    renderCalendar();
    renderClock();
    setInterval(renderClock, 1000);
})();
</script>
@endpush
