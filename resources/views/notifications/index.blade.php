@extends('layouts.app')

@section('content')
<div class="dashboard-wrap notification-history-page">
    <div class="dashboard-heading"><p class="eyebrow">Clinic updates</p><h1>Notifications</h1><span>Consultation completion and queue notifications for clinic staff.</span></div>
    <section class="dashboard-panel notification-history-list">
        @forelse ($notifications as $notification)
            <article class="notification-history-item {{ $notification->is_read ? '' : 'is-unread' }}">
                <span class="notification-history-icon"><i class="fa fa-{{ $notification->type === 'consultation_completed' ? 'check' : 'bell-o' }}"></i></span>
                <div><div class="notification-history-title"><strong>{{ $notification->title }}</strong><time>{{ $notification->created_at->diffForHumans() }}</time></div><p>{{ $notification->message }}</p><div class="clinic-notification-actions">@unless ($notification->is_read)<form method="POST" action="{{ route('notifications.read', $notification) }}">@csrf<button class="btn btn-light btn-sm">Mark as Read</button></form>@endunless @if ($notification->related_consultation_id)<a href="{{ route('student-complaints.index') }}" class="btn btn-primary btn-sm">View Queue</a>@endif</div></div>
            </article>
        @empty
            @include('includes.empty-state', ['title' => 'No notifications yet.', 'icon' => 'fa-bell-o'])
        @endforelse
    </section>
    <div class="pagination justify-content-center">{{ $notifications->links() }}</div>
</div>
@endsection
