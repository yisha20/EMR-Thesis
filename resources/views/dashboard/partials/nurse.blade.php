@php
    $pendingQueueCount = collect($kpis)->firstWhere('label', 'Pending Complaints')['value'] ?? null;
    $trendStatuses = ['Pending', 'Reviewed', 'In Consultation', 'Completed'];
    $trendColors = [
        'Pending' => '#1d4ed8',
        'Reviewed' => '#0f766e',
        'In Consultation' => '#d97706',
        'Completed' => '#16a34a',
    ];
    $trendStartDate = today()->subDays(6);
    $workflowCounts = \App\StudentComplaint::selectRaw('status, COUNT(*) as total')
        ->whereIn('status', $trendStatuses)
        ->groupBy('status')
        ->pluck('total', 'status');
    $workflowCounts['Completed'] = (int) ($workflowCounts['Completed'] ?? 0)
        + \App\StudentComplaint::where('status', 'Counter Resolved')->count();
    $workflowCounts['In Consultation'] = (int) ($workflowCounts['In Consultation'] ?? 0)
        + \App\StudentComplaint::where('status', 'Forwarded')->count();
    $workflowItems = collect($trendStatuses)->map(function ($status) use ($workflowCounts, $trendColors) {
        return [
            'label' => $status === 'Completed' ? 'Completed Complaints' : $status . ' Complaints',
            'status' => $status,
            'color' => $trendColors[$status],
            'total' => (int) ($workflowCounts[$status] ?? 0),
        ];
    });
    $workflowMax = max(1, (int) $workflowItems->max('total'));
    $workflowTotal = (int) $workflowItems->sum('total');
    $kpiIndicatorColors = [
        'Pending Complaints' => $trendColors['Pending'],
        'Reviewed Complaints' => $trendColors['Reviewed'],
        'Consultations Today' => $trendColors['In Consultation'],
        'Patients Today' => $trendColors['Completed'],
    ];
    $kpiDisplayMeta = [
        'Pending Complaints' => ['subtitle' => 'Needs review', 'class' => 'kpi-pending', 'icon' => 'fa-inbox'],
        'Reviewed Complaints' => ['subtitle' => 'Already reviewed', 'class' => 'kpi-reviewed', 'icon' => 'fa-check'],
        'Consultations Today' => ['subtitle' => 'Clinic visits today', 'class' => 'kpi-consultations', 'icon' => 'fa-stethoscope'],
        'Patients Today' => ['subtitle' => 'Registered today', 'class' => 'kpi-patients', 'icon' => 'fa-user-plus'],
    ];
@endphp

<div class="nurse-dashboard-layout">
    <aside class="consultation-complete-toast" data-consultation-toast role="status" aria-live="polite">
        <span class="consultation-toast-icon"><i class="fa fa-check"></i></span>
        <div><strong data-toast-title>Consultation Completed</strong><p data-toast-message></p><a href="{{ route('student-complaints.index') }}" data-toast-queue>View Queue</a></div>
        <button type="button" data-toast-close aria-label="Close notification">&times;</button>
    </aside>

    <section class="nurse-dashboard-shortcuts shortcut-stack" aria-label="Quick actions">
        <a href="{{ route('patients.index') }}" class="shortcut-dashboard-card">
            <span class="shortcut-card-media">
                <img src="{{ asset('img/shortcut-patients.svg') }}" alt="Manage Patients">
            </span>
            <div>
                <h3 class="shortcut-card-title">Manage Patients</h3>
                <p class="shortcut-card-description">View records and consultations.</p>
            </div>
            <span class="shortcut-card-action"><i class="fa fa-angle-right"></i></span>
        </a>
        <a href="{{ route('student-complaints.index') }}" class="shortcut-dashboard-card">
            <span class="shortcut-card-media">
                <img src="{{ asset('img/shortcut-intake.svg') }}" alt="Student Intake Queue">
            </span>
            <div>
                <h3 class="shortcut-card-title">Student Intake Queue</h3>
                <p class="shortcut-card-description">{{ $pendingQueueCount !== null ? number_format($pendingQueueCount) . ' pending concerns ready for review.' : 'Review student clinic concerns.' }}</p>
            </div>
            <span class="shortcut-card-action"><i class="fa fa-angle-right"></i></span>
        </a>
        <a href="{{ route('services.index') }}" class="shortcut-dashboard-card">
            <span class="shortcut-card-media">
                <img src="{{ asset('img/shortcut-services.svg') }}" alt="Manage Services">
            </span>
            <div>
                <h3 class="shortcut-card-title">Manage Services</h3>
                <p class="shortcut-card-description">Maintain clinic services.</p>
            </div>
            <span class="shortcut-card-action"><i class="fa fa-angle-right"></i></span>
        </a>
    </section>

    @include('dashboard.partials.queue-workspace')
    <section class="dashboard-panel next-student-card" hidden>
        <div class="dashboard-panel-header"><div><p class="eyebrow">Doctor consultation queue</p><h2>Next Student in Queue</h2></div></div>
        @if ($nextConsultation)
            <div class="next-student-content">
                <span class="next-student-priority urgency-badge urgency-{{ strtolower($nextConsultation->priority) }}">{{ $nextConsultation->priority }} Priority</span>
                <div class="next-student-identity"><strong>{{ $nextConsultation->complaint->student_name }}</strong><span>{{ $nextConsultation->complaint->student_id_number }}</span></div>
                <div class="next-student-details"><div><span>Chief Complaint</span><strong>{{ $nextConsultation->complaint->chief_complaint }}</strong></div><div><span>Forwarded</span><strong>{{ $nextConsultation->forwarded_at->format('M j, Y g:i A') }}</strong></div></div>
                <form method="POST" action="{{ route('consultations.call-student', $nextConsultation) }}">@csrf<button class="btn btn-primary" data-confirm="Call {{ $nextConsultation->complaint->student_name }} for consultation?" data-confirm-title="Call next student"><i class="fa fa-bullhorn"></i> Call Student</button></form>
            </div>
        @else
            @include('includes.empty-state', ['title' => 'No students waiting for consultation.', 'icon' => 'fa-check-circle-o'])
        @endif
    </section>

    <section class="nurse-analytics-row">
        <article class="dashboard-panel nurse-complaint-trends">
            <div class="nurse-chart-heading">
                <div>
                    <p class="eyebrow">Clinic queue</p>
                    <h2>Clinic Workflow Status</h2>
                    <span>Current student intake and consultation progress</span>
                </div>
            </div>

            <div class="nurse-workflow-chart" role="img" aria-label="Current clinic workflow status">
                @if ($workflowTotal > 0)
                    @foreach ($workflowItems as $item)
                        @php $barPercent = max(4, ($item['total'] / $workflowMax) * 100); @endphp
                        <article class="nurse-workflow-row">
                            <div class="nurse-workflow-meta">
                                <span>{{ $item['label'] }}</span>
                                <strong>{{ number_format($item['total']) }}</strong>
                            </div>
                            <div class="nurse-workflow-track" aria-label="{{ $item['label'] }}: {{ $item['total'] }}">
                                <span style="width: {{ $barPercent }}%; background: {{ $item['color'] }};"></span>
                            </div>
                        </article>
                    @endforeach
                @else
                    <p class="activity-empty">No workflow activity yet.</p>
                @endif
            </div>
        </article>

        <section class="nurse-kpi-grid" aria-label="Nurse dashboard key performance indicators">
            @foreach ($kpis as $kpi)
                @php $kpiMeta = $kpiDisplayMeta[$kpi['label']] ?? ['subtitle' => 'Current total', 'class' => 'kpi-default', 'icon' => $kpi['icon'] ?? 'fa-line-chart']; @endphp
                <article class="kpi-glass-card nurse-kpi-card {{ $kpiMeta['class'] }}">
                    <div>
                        <div class="kpi-topline">
                            <span class="kpi-dot" style="background: {{ $kpiIndicatorColors[$kpi['label']] ?? 'var(--emr-primary)' }}"></span>
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

    <section class="dashboard-panel dashboard-activity-card nurse-recent-activity">
        <div class="dashboard-panel-header">
            <div>
                <p class="eyebrow">Audit trail</p>
                <h2>Recent Activity</h2>
            </div>
            <a href="{{ route('activity.logs') }}">View all</a>
        </div>

        @include('dashboard.partials.activity-timeline', ['logs' => $recentActivityLogs])
    </section>
</div>

@push('js')
<script>
(function () {
    var clock = document.querySelector('[data-nurse-clock]');
    if (!clock) {
        return;
    }

    var clockDay = clock.querySelector('[data-nurse-clock-day]');
    var clockDate = clock.querySelector('[data-nurse-clock-date]');
    var clockTime = clock.querySelector('[data-nurse-clock-time]');
    var clockDayFormatter = new Intl.DateTimeFormat('en', { weekday: 'long' });
    var clockDateFormatter = new Intl.DateTimeFormat('en', { month: 'long', day: 'numeric', year: 'numeric' });
    var clockTimeFormatter = new Intl.DateTimeFormat('en', { hour: 'numeric', minute: '2-digit', second: '2-digit' });

    function renderClock() {
        var now = new Date();
        clockDay.textContent = clockDayFormatter.format(now);
        clockDate.textContent = clockDateFormatter.format(now);
        clockTime.textContent = clockTimeFormatter.format(now);
    }

    renderClock();
    setInterval(renderClock, 1000);
})();

(function () {
    var points = document.querySelectorAll('[data-trend-tooltip]');
    if (!points.length) {
        return;
    }

    var tooltip = document.createElement('div');
    tooltip.className = 'nurse-trend-tooltip';
    document.body.appendChild(tooltip);

    points.forEach(function (point) {
        point.addEventListener('mouseenter', function () {
            tooltip.textContent = point.getAttribute('data-trend-tooltip');
            tooltip.classList.add('is-visible');
        });

        point.addEventListener('mousemove', function (event) {
            tooltip.style.left = event.clientX + 12 + 'px';
            tooltip.style.top = event.clientY + 12 + 'px';
        });

        point.addEventListener('mouseleave', function () {
            tooltip.classList.remove('is-visible');
        });
    });
})();
</script>
@endpush
<div class="mb-3"><a href="{{route('emergency-intakes.create')}}" class="btn btn-danger btn-lg" onclick="return confirm('Open the dedicated Emergency / Walk-in Intake page?')">Start Emergency Intake</a> <a href="{{route('emergency-intakes.create')}}" class="btn btn-primary">Add Walk-in Patient</a> <a href="{{route('patients.index')}}" class="btn btn-outline-primary">Search Patient</a> <a href="{{route('dental-referrals.index')}}" class="btn btn-outline-primary">View Dental Referrals</a></div>
