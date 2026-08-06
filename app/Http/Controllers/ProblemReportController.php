<?php

namespace App\Http\Controllers;

use App\Services\WorkflowMonitor;
use Illuminate\Http\Request;

class ProblemReportController extends Controller
{
    public function create()
    {
        return view('support.report-problem');
    }

    public function store(Request $request, WorkflowMonitor $monitor)
    {
        $data = $request->validate([
            'attempted_action'=>'required|string|max:200',
            'what_happened'=>'required|string|max:1000',
            'resource_reference'=>'nullable|string|max:80',
            'additional_notes'=>'nullable|string|max:1000',
            'screenshot'=>'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
        $path = $request->hasFile('screenshot') ? $request->file('screenshot')->store('private/monitoring/problem-screenshots') : null;
        $user = $request->user();
        $incident = $monitor->createIncident([
            'severity'=>'medium','category'=>'user_reported','event_type'=>'staff_problem_report','user_id'=>$user->id,
            'user_role'=>optional($user->role)->name,'route_name'=>$request->input('reported_route') ?: url()->previous(),
            'safe_message'=>$data['attempted_action'].' — '.$data['what_happened'],
            'technical_message'=>'Staff-submitted pilot issue; review the sanitized report context.',
        ]);
        $incident->update([
            'resource_type'=>$data['resource_reference'] ? 'staff_reference' : null,
            'report_context'=>[
                'additional_notes'=>$data['additional_notes'] ?? null,
                'resource_reference'=>$data['resource_reference'] ?? null,
                'browser'=>substr((string)$request->userAgent(),0,500),
                'device'=>$request->input('device_summary'),
                'application_version'=>config('app.version','Pilot Build'),
                'environment'=>app()->environment(),
            ],
            'screenshot_path'=>$path,
        ]);
        return redirect()->route('support.problem.create')->with('success','Problem reported. Reference: '.$incident->reference_code);
    }
}
