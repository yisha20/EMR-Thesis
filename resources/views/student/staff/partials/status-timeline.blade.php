<section class="dashboard-panel status-timeline-panel">
    <div class="dashboard-panel-header"><div><p class="eyebrow">Workflow status</p><h2>Status Timeline</h2></div></div>
    <div class="status-step-list">
        @foreach ($timelineSteps as $step)
            <article class="status-step {{ $step['done'] ? 'is-complete' : '' }} {{ $step['current'] ? 'is-current' : '' }}">
                <span><i class="fa {{ $step['done'] ? 'fa-check' : 'fa-circle-o' }}"></i></span>
                <strong>{{ $step['label'] }}</strong>
            </article>
        @endforeach
    </div>

    <div class="compact-audit-list">
        @forelse ($complaint->statusLogs->take(4) as $log)
            <article><strong>{{ $log->to_status }}</strong><small>{{ optional($log->changedBy)->fullName() ?: 'System' }} &bull; {{ $log->created_at->format('M j, Y g:i A') }}</small></article>
        @empty
            <p class="activity-empty">No status changes recorded.</p>
        @endforelse
    </div>
</section>
