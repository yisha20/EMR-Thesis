@extends('layouts.app')

@section('content')
@php
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

<div class="activity-history-page">
    <header class="activity-history-header">
        <div>
            <p class="eyebrow">Audit trail</p>
            <h1>Activity History</h1>
            <span>Complete audit trail of clinic system actions.</span>
        </div>
        <a href="{{ route('dashboard') }}" class="activity-history-back">
            <i class="fa fa-arrow-left"></i>
            <span>Dashboard</span>
        </a>
    </header>

    <form method="GET" action="{{ route('activity.logs') }}" class="activity-history-filter">
        <label for="activity_date">Search by date</label>
        <div>
            <input type="date" id="activity_date" name="activity_date" value="{{ $activityDate }}">
            <button type="submit" class="btn btn-primary">Search</button>
            @if($activityDate)
                <a href="{{ route('activity.logs') }}" class="btn btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <section class="activity-history-panel">
        <div class="activity-history-list">
            @forelse($logs as $log)
                @php
                    $actionType = $resolveActionType($log);
                    $badgeLabel = $actionBadgeMap[$actionType] ?? 'Activity';
                    $actionTitle = ucfirst($log->action_text);
                @endphp

                <article class="activity-history-item">
                    <div class="activity-history-marker" aria-hidden="true">
                        <span class="timeline-dot timeline-dot-{{ $actionType }}"></span>
                    </div>

                    <div class="activity-history-content">
                        <div class="activity-history-title-row">
                            <span class="activity-badge activity-badge-{{ $actionType }}">{{ $badgeLabel }}</span>
                            <strong>{{ $actionTitle }}</strong>
                        </div>

                        <time class="activity-history-time activity-history-time-inline" datetime="{{ $log->created_at->toIso8601String() }}">
                            {{ $log->created_at->format('M j, Y') }} &bull; {{ $log->created_at->format('g:i A') }}
                        </time>

                        <p>
                            <strong>{{ $log->actor_name }}</strong>
                            {{ lcfirst($log->action_text) }}
                            @if($log->action_target)
                                for <strong>{{ $log->action_target }}</strong>
                            @endif
                        </p>

                        @if($log->description)
                            <span class="activity-history-description">{{ $log->description }}</span>
                        @endif
                    </div>

                    <time class="activity-history-time" datetime="{{ $log->created_at->toIso8601String() }}">
                        {{ $log->created_at->format('M j, Y') }} &bull; {{ $log->created_at->format('g:i A') }}
                    </time>
                </article>
            @empty
                <p class="activity-empty">No recent activity yet.</p>
            @endforelse
        </div>

        @if($logs->hasPages())
            <div class="activity-history-pagination">
                {{ $logs->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
