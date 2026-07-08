@php
    $activityLogs = collect($logs ?? [])->take(5);
    $actionBadgeMap = [
        'submitted' => 'Submitted',
        'reviewed' => 'Reviewed',
        'started' => 'Started',
        'completed' => 'Completed',
        'linked' => 'Linked',
        'created' => 'Created',
        'updated' => 'Updated',
        'archived' => 'Archived',
        'restored' => 'Restored',
        'deleted' => 'Deleted',
    ];

    $resolveActionType = function ($log) {
        $actionText = strtolower(trim(($log->action_text ?? $log->action ?? '') . ' ' . ($log->description ?? '')));
        $modelType = strtolower(trim($log->action_type ?? ''));

        foreach (['submitted', 'reviewed', 'started', 'completed', 'linked', 'created', 'updated', 'archived', 'restored', 'deleted'] as $type) {
            if ($modelType === $type || strpos($actionText, $type) !== false) {
                return $type;
            }
        }

        if ($modelType === 'added' || strpos($actionText, 'added') !== false) {
            return 'created';
        }

        return 'unknown';
    };
@endphp

<div class="dashboard-activity-list timeline">
    @forelse ($activityLogs as $log)
        @php
            $actionType = $resolveActionType($log);
            $badgeLabel = $actionBadgeMap[$actionType] ?? 'Activity';
            $actionTitle = ucfirst($log->action_text);
        @endphp

        <article class="timeline-item">
            <div class="timeline-marker" aria-hidden="true">
                <span class="timeline-dot timeline-dot-{{ $actionType }}"></span>
            </div>

            <div class="timeline-content">
                <div class="timeline-title-row">
                    <span class="activity-badge activity-badge-{{ $actionType }}">{{ $badgeLabel }}</span>
                    <strong class="timeline-title">{{ $actionTitle }}</strong>
                </div>

                <time class="timeline-time timeline-time-inline" datetime="{{ $log->created_at->toIso8601String() }}">
                    {{ $log->created_at->format('M j, Y') }} &bull; {{ $log->created_at->format('g:i A') }}
                </time>

                <p class="timeline-meta">
                    <strong>{{ $log->actor_name }}</strong>
                    {{ lcfirst($log->action_text) }}
                    @if ($log->action_target)
                        for <strong>{{ $log->action_target }}</strong>
                    @endif
                </p>

                @if ($log->description)
                    <p class="timeline-description">{{ $log->description }}</p>
                @endif
            </div>

            <time class="timeline-time" datetime="{{ $log->created_at->toIso8601String() }}">
                {{ $log->created_at->format('M j, Y') }} &bull; {{ $log->created_at->format('g:i A') }}
            </time>
        </article>
    @empty
        <p class="activity-empty">No recent activity yet.</p>
    @endforelse
</div>
