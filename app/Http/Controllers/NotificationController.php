<?php

namespace App\Http\Controllers;

use App\ClinicNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = ClinicNotification::forUser($request->user())->latest()->paginate(15);
        return view('notifications.index', compact('notifications'));
    }

    public function unread(Request $request)
    {
        $baseQuery = ClinicNotification::forUser($request->user());
        $unreadCount = (clone $baseQuery)->where('is_read', false)->count();
        $notifications = (clone $baseQuery)->latest()->take(8)->get()->map(function ($notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'timestamp' => $notification->created_at->diffForHumans(),
                'is_read' => $notification->is_read,
                'notification_type'=>$notification->notification_type ?: $notification->type,
                'view_queue_url' => $notification->action_url ?: $this->defaultAction($notification),
                'read_url' => route('notifications.read', $notification),
                'priority' => $notification->priority ?: 'routine',
            ];
        });

        return response()->json(['unread_count' => $unreadCount, 'notifications' => $notifications]);
    }

    public function read(Request $request, ClinicNotification $notification)
    {
        abort_unless(ClinicNotification::forUser($request->user())->whereKey($notification->id)->exists(), 403);
        $notification->update(['is_read' => true,'read_at'=>$notification->read_at ?: now()]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back();
    }

    public function readAll(Request $request)
    {
        ClinicNotification::forUser($request->user())->where('is_read',false)
            ->update(['is_read'=>true,'read_at'=>now()]);
        return $request->expectsJson()?response()->json(['ok'=>true]):redirect()->back()->with('success','All notifications marked read.');
    }

    private function defaultAction(ClinicNotification $notification)
    {
        return optional(request()->user())->isPatientPortalUser()
            ? route('student.dashboard') : route('dashboard');
    }
}
