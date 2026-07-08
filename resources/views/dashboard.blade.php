@extends('layouts.app')

@section('content')
@php $isNurseWorkspace = in_array($roleName, ['Nurse', 'Staff'], true); @endphp
<div class="dashboard-wrap dashboard-simplified">
    <div class="dashboard-heading {{ $isNurseWorkspace ? 'nurse-dashboard-heading' : '' }}">
        <div>
            <p class="eyebrow">{{ $roleName }} dashboard</p>
            <h1>Clinic workspace</h1>
            @unless ($isNurseWorkspace)
                <span>{{ now()->format('l, F j, Y') }}</span>
            @endunless
        </div>

        @if ($isNurseWorkspace)
            <div class="nurse-header-clock" data-nurse-clock>
                <div>
                    <strong data-nurse-clock-day>{{ now()->format('l') }}</strong>
                    <span data-nurse-clock-date>{{ now()->format('F j, Y') }}</span>
                </div>
                <div>
                    <strong data-nurse-clock-time>{{ now()->format('g:i A') }}</strong>
                    <span>Clinic Hours: 8:00 AM - 5:00 PM</span>
                </div>
            </div>
        @endif
    </div>

    @if ($isNurseWorkspace)
        @include('dashboard.partials.nurse')
    @elseif ($roleName === 'Doctor')
        @include('dashboard.partials.doctor')
    @else
    <section class="dashboard-kpi-grid" aria-label="Clinic key performance indicators">
        @foreach ($kpis as $kpi)
            <article class="dashboard-kpi-card kpi-{{ $kpi['tone'] }}">
                <span class="dashboard-kpi-icon"><i class="fa {{ $kpi['icon'] }}"></i></span>
                <div>
                    <span>{{ $kpi['label'] }}</span>
                    <strong>{{ number_format($kpi['value']) }}</strong>
                </div>
            </article>
        @endforeach
    </section>

    <section class="dashboard-shortcuts" aria-label="Quick actions">
        <a href="{{ route('patients.index') }}" class="dashboard-shortcut-card">
            <span class="dashboard-line-icon"><i class="fa fa-address-card-o"></i></span>
            <span><strong>Manage Patients</strong><small>Patient records</small></span>
            <i class="fa fa-angle-right"></i>
        </a>

        <a href="{{ route('student-complaints.index') }}" class="dashboard-shortcut-card">
            <span class="dashboard-line-icon"><i class="fa fa-inbox"></i></span>
            <span>
                <strong>{{ $roleName === 'Doctor' ? 'Consultation Queue' : 'Student Intake Queue' }}</strong>
                <small>Review complaints</small>
            </span>
            <i class="fa fa-angle-right"></i>
        </a>

        @if ($roleName === 'Administrator')
            <a href="{{ route('users.index') }}" class="dashboard-shortcut-card">
                <span class="dashboard-line-icon"><i class="fa fa-id-badge"></i></span>
                <span><strong>Manage Users</strong><small>Roles and access</small></span>
                <i class="fa fa-angle-right"></i>
            </a>
        @endif

        @if (in_array($roleName, ['Administrator', 'Nurse']))
            <a href="{{ route('services.index') }}" class="dashboard-shortcut-card">
                <span class="dashboard-line-icon"><i class="fa fa-briefcase"></i></span>
                <span><strong>Manage Services</strong><small>Clinic services</small></span>
                <i class="fa fa-angle-right"></i>
            </a>
        @endif

        @if ($roleName === 'Doctor')
            <a href="{{ route('medical-records.index') }}" class="dashboard-shortcut-card">
                <span class="dashboard-line-icon"><i class="fa fa-file-text-o"></i></span>
                <span><strong>Medical Records</strong><small>Clinical records</small></span>
                <i class="fa fa-angle-right"></i>
            </a>
        @endif
    </section>

    <div class="dashboard-main-grid">
        @if ($roleName === 'Doctor')
            <section class="dashboard-panel dashboard-recent-panel">
                <div class="dashboard-panel-header">
                    <div>
                        <p class="eyebrow">Clinical activity</p>
                        <h2>Recent Consultations</h2>
                    </div>
                    <a href="{{ route('medical-records.index') }}">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table dashboard-compact-table data-table">
                        <thead>
                            <tr><th>Patient</th><th>Complaint</th><th>Status</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($recentConsultations as $consultation)
                                <tr>
                                    <td><strong>{{ trim(optional($consultation->patient)->first_name . ' ' . optional($consultation->patient)->last_name) ?: 'Unknown patient' }}</strong></td>
                                    <td>{{ $consultation->chief_complaint ?: 'Not specified' }}</td>
                                    <td><span class="complaint-status status-{{ \Illuminate\Support\Str::slug($consultation->consultation_status ?: 'Completed') }}">{{ $consultation->consultation_status ?: 'Completed' }}</span></td>
                                    <td>{{ optional($consultation->date_of_consultation)->format('M j, Y') ?: 'Not set' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4">@include('includes.empty-state', ['title' => 'No recent consultations.', 'icon' => 'fa-stethoscope'])</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @else
            <section class="dashboard-panel dashboard-activity-card">
                <div class="dashboard-panel-header">
                    <div>
                        <p class="eyebrow">Audit trail</p>
                        <h2>Recent Activity</h2>
                    </div>
                    <a href="{{ route('activity.logs') }}">View all</a>
                </div>

                @include('dashboard.partials.activity-timeline', ['logs' => $recentActivityLogs])
            </section>
        @endif

        <aside class="dashboard-panel dashboard-analytics-card">
            <div class="dashboard-panel-header">
                <div>
                    <p class="eyebrow">At a glance</p>
                    <h2>{{ $analytics['title'] }}</h2>
                    <span>{{ $analytics['subtitle'] }}</span>
                </div>
            </div>

            @if ($analytics['type'] === 'bar')
                @php $barMax = max(1, (int) collect($analytics['items'])->max('value')); @endphp
                <div class="dashboard-bar-chart" role="img" aria-label="Consultations by service">
                    @forelse ($analytics['items'] as $item)
                        <div class="dashboard-bar-row">
                            <div><span>{{ $item['label'] }}</span><strong>{{ $item['value'] }}</strong></div>
                            <span class="dashboard-bar-track"><span style="width: {{ ($item['value'] / $barMax) * 100 }}%"></span></span>
                        </div>
                    @empty
                        @include('includes.empty-state', ['title' => 'No consultation data yet.', 'icon' => 'fa-bar-chart'])
                    @endforelse
                </div>
            @elseif ($analytics['type'] === 'donut')
                @php
                    $donutItems = collect($analytics['items']);
                    $donutActualTotal = (int) $donutItems->sum('value');
                    $donutTotal = max(1, $donutActualTotal);
                    $donutColors = ['#1d4ed8', '#0f766e', '#d97706', '#16a34a'];
                    $donutStart = 0;
                    $donutSegments = [];
                    foreach ($donutItems as $index => $item) {
                        $donutEnd = $donutStart + (($item['value'] / $donutTotal) * 100);
                        $donutSegments[] = $donutColors[$index] . ' ' . $donutStart . '% ' . $donutEnd . '%';
                        $donutStart = $donutEnd;
                    }
                @endphp
                <div class="dashboard-donut-layout">
                    <div class="dashboard-donut" style="background: {{ $donutActualTotal ? 'conic-gradient(' . implode(', ', $donutSegments) . ')' : 'var(--emr-surface-soft)' }};" role="img" aria-label="Complaint status distribution">
                        <span><strong>{{ $donutActualTotal }}</strong><small>Total</small></span>
                    </div>
                    <div class="dashboard-chart-legend">
                        @foreach ($donutItems as $index => $item)
                            <div><i style="background: {{ $donutColors[$index] }}"></i><span>{{ $item['label'] }}</span><strong>{{ $item['value'] }}</strong></div>
                        @endforeach
                    </div>
                </div>
            @else
                @php
                    $lineItems = collect($analytics['items'])->values();
                    $lineMax = max(1, (int) $lineItems->max('value'));
                    $linePoints = $lineItems->map(function ($item, $index) use ($lineItems, $lineMax) {
                        $x = $lineItems->count() > 1 ? 20 + (($index / ($lineItems->count() - 1)) * 260) : 150;
                        $y = 125 - (($item['value'] / $lineMax) * 95);
                        return round($x, 1) . ',' . round($y, 1);
                    })->implode(' ');
                @endphp
                <div class="dashboard-line-chart" role="img" aria-label="Daily consultation trend for the last seven days">
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
            @endif
        </aside>
    </div>
    @endif
</div>
@endsection
