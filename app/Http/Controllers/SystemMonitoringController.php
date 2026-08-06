<?php

namespace App\Http\Controllers;

use App\Services\ClinicMonitoringService;
use App\Services\WorkflowMonitor;
use App\SystemIncident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SystemMonitoringController extends Controller
{
    public function index(ClinicMonitoringService $monitoring)
    {
        $result = $monitoring->run(false);
        $incidents = SystemIncident::latest('detected_at')->paginate(15);
        return view('admin.system-monitoring.index', compact('result', 'incidents'));
    }

    public function runChecks(ClinicMonitoringService $monitoring, WorkflowMonitor $workflow)
    {
        try {
            $result = $monitoring->run(true);
            return redirect()->route('admin.monitoring.index')->with('success',
                'System checks completed: '.$result['summary']['critical'].' critical and '.$result['summary']['warning'].' warning checks.');
        } catch (\Throwable $exception) {
            $reference = $workflow->failed('run_system_checks', $exception, ['category'=>'server']);
            return redirect()->route('admin.monitoring.index')->with('error', 'Monitoring could not complete. Error Reference: '.$reference);
        }
    }

    public function show(SystemIncident $incident)
    {
        return view('admin.system-monitoring.show', compact('incident'));
    }

    public function updateStatus(Request $request, SystemIncident $incident, WorkflowMonitor $monitor)
    {
        $data = $request->validate(['status'=>'required|in:investigating,resolved,false_positive','resolution_notes'=>'nullable|string|max:1000']);
        $monitor->resolveIncident($incident, $data['status'], $data['resolution_notes'] ?? null, $request->user()->id);
        return redirect()->route('admin.monitoring.index')->with('success', 'Incident status updated.');
    }

    public function report(ClinicMonitoringService $monitoring)
    {
        $report = $monitoring->dailyReport();
        $path = $monitoring->storeDailyReport($report);
        return response()->download($path, basename($path), ['Content-Type'=>'application/json']);
    }

    public function screenshot(SystemIncident $incident)
    {
        abort_unless($incident->screenshot_path && Storage::disk('local')->exists($incident->screenshot_path), 404);
        return Storage::disk('local')->download($incident->screenshot_path, 'incident-'.$incident->reference_code.'.'.pathinfo($incident->screenshot_path, PATHINFO_EXTENSION));
    }
}
