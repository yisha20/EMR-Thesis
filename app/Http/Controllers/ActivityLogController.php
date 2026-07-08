<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $activityDate = $request->input('activity_date');
        $logs = ActivityLog::with('user')
            ->when($activityDate, function ($query) use ($activityDate) {
                $query->whereDate('created_at', $activityDate);
            })
            ->latest()
            ->paginate(15)
            ->appends($request->only('activity_date'));

        return view('activity_logs.index', compact('logs', 'activityDate'));
    }
}
