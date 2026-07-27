<?php

namespace App\Http\Controllers;

use App\MedicalRecord;
use App\Consultation;
use App\Models\ActivityLog;
use App\Patient;
use App\Service;
use App\StudentComplaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\ClinicQueue;
use App\Services\ClinicQueueService;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $roleName = $request->user()->role->name;
        $today = today();
        $totalPatients = Patient::count();
        $pendingComplaints = StudentComplaint::where('status', 'Pending')->count();
        $consultationsToday = MedicalRecord::whereDate('date_of_consultation', $today)->count();
        $activeServices = Service::where('status', 'Active')->count();
        $reviewedComplaints = StudentComplaint::where('status', 'Reviewed')->count();
        $patientsToday = Patient::where(function ($query) use ($today) {
            $query->whereDate('date_registered', $today)
                ->orWhere(function ($query) use ($today) {
                    $query->whereNull('date_registered')->whereDate('created_at', $today);
                });
        })->count();

        $pendingConsultations = MedicalRecord::where('consultation_status', 'Pending Consultation')->count();
        $inConsultation = MedicalRecord::where('consultation_status', 'In Consultation')->count();
        $completedToday = MedicalRecord::where('consultation_status', 'Completed')
            ->whereDate('updated_at', $today)
            ->count();

        $recentActivityLogs = ActivityLog::latest()->with('user')->take(5)->get();
        $recentConsultations = MedicalRecord::with('patient')
            ->where(function ($query) {
                $query->whereNull('record_type')->orWhere('record_type', 'Consultation');
            })
            ->orderBy('date_of_consultation', 'desc')
            ->orderBy('time_of_consultation', 'desc')
            ->take(5)
            ->get();

        $kpis = $this->kpisForRole($roleName, [
            'totalPatients' => $totalPatients,
            'pendingComplaints' => $pendingComplaints,
            'consultationsToday' => $consultationsToday,
            'activeServices' => $activeServices,
            'reviewedComplaints' => $reviewedComplaints,
            'patientsToday' => $patientsToday,
            'pendingConsultations' => $pendingConsultations,
            'inConsultation' => $inConsultation,
            'completedToday' => $completedToday,
        ]);

        $analytics = $this->analyticsForRole($roleName);
        $nextConsultation = null;
        if (in_array($roleName, ['Nurse', 'Staff'], true)) {
            $nextConsultation = Consultation::with(['complaint', 'patient'])
                ->where('status', 'Pending Consultation')
                ->orderByRaw("CASE priority WHEN 'High' THEN 1 WHEN 'Moderate' THEN 2 ELSE 3 END")
                ->orderBy('forwarded_at')
                ->first();
        }
        $queueEntries=collect(); $nextQueue=null; $queuePolicy='alternating';
        if (in_array($roleName,['Administrator','Nurse','Staff','Doctor'],true)) {
            $queueEntries=ClinicQueue::with(['complaint.student.user','account.user','consultation.forwarder','doctor'])
                ->where('queue_date',$today)->whereIn('status',['waiting','called','serving','missed'])
                ->when($roleName==='Doctor',function($query){$query->where('queue_type','consultation');})
                ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'moderate' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
                ->orderBy('created_at')->get();
            $nextQueue=app(ClinicQueueService::class)->nextCandidate($today->toDateString());
            $queuePolicy=DB::table('clinic_queue_dispatch_states')->where('queue_date',$today)->value('policy') ?: 'alternating';
        }

        return view('dashboard', compact(
            'roleName',
            'kpis',
            'analytics',
            'recentActivityLogs',
            'recentConsultations',
            'nextConsultation','queueEntries','nextQueue','queuePolicy'
        ));
    }

    private function kpisForRole($roleName, array $counts)
    {
        if ($roleName === 'Doctor') {
            return [
                ['label' => 'Pending Consultations', 'value' => $counts['pendingConsultations'], 'icon' => 'fa-clock-o', 'tone' => 'amber'],
                ['label' => 'In Consultation', 'value' => $counts['inConsultation'], 'icon' => 'fa-stethoscope', 'tone' => 'blue'],
                ['label' => 'Completed Today', 'value' => $counts['completedToday'], 'icon' => 'fa-check-circle-o', 'tone' => 'green'],
                ['label' => 'Total Patients', 'value' => $counts['totalPatients'], 'icon' => 'fa-users', 'tone' => 'teal'],
            ];
        }

        if (in_array($roleName, ['Nurse', 'Staff'], true)) {
            return [
                ['label' => 'Pending Complaints', 'value' => $counts['pendingComplaints'], 'icon' => 'fa-inbox', 'tone' => 'amber'],
                ['label' => 'Reviewed Complaints', 'value' => $counts['reviewedComplaints'], 'icon' => 'fa-check-square-o', 'tone' => 'teal'],
                ['label' => 'Consultations Today', 'value' => $counts['consultationsToday'], 'icon' => 'fa-stethoscope', 'tone' => 'green'],
                ['label' => 'Patients Today', 'value' => $counts['patientsToday'], 'icon' => 'fa-user-plus', 'tone' => 'blue'],
            ];
        }

        return [
            ['label' => 'Total Patients', 'value' => $counts['totalPatients'], 'icon' => 'fa-users', 'tone' => 'blue'],
            ['label' => 'Pending Complaints', 'value' => $counts['pendingComplaints'], 'icon' => 'fa-inbox', 'tone' => 'amber'],
            ['label' => 'Consultations Today', 'value' => $counts['consultationsToday'], 'icon' => 'fa-stethoscope', 'tone' => 'green'],
            ['label' => 'Active Services', 'value' => $counts['activeServices'], 'icon' => 'fa-medkit', 'tone' => 'teal'],
        ];
    }

    private function analyticsForRole($roleName)
    {
        if ($roleName === 'Doctor') {
            $startDate = today()->subDays(6);
            $counts = MedicalRecord::selectRaw('date_of_consultation, COUNT(*) as total')
                ->whereBetween('date_of_consultation', [$startDate, today()])
                ->groupBy('date_of_consultation')
                ->pluck('total', 'date_of_consultation');

            $days = collect(range(0, 6))->map(function ($offset) use ($startDate, $counts) {
                $date = $startDate->copy()->addDays($offset);

                return [
                    'label' => $date->format('D'),
                    'date' => $date->format('M j'),
                    'value' => (int) ($counts[$date->format('Y-m-d')] ?? 0),
                ];
            });

            return [
                'type' => 'line',
                'title' => 'Daily Consultation Trend',
                'subtitle' => 'Last 7 days',
                'items' => $days,
            ];
        }

        if (in_array($roleName, ['Nurse', 'Staff'], true)) {
            $statuses = ['Pending', 'Reviewed', 'In Consultation', 'Completed'];
            $counts = StudentComplaint::select('status', DB::raw('COUNT(*) as total'))
                ->whereIn('status', $statuses)
                ->groupBy('status')
                ->pluck('total', 'status');

            return [
                'type' => 'donut',
                'title' => 'Complaint Status Summary',
                'subtitle' => 'Current complaint distribution',
                'items' => collect($statuses)->map(function ($status) use ($counts) {
                    return ['label' => $status, 'value' => (int) ($counts[$status] ?? 0)];
                }),
            ];
        }

        $services = MedicalRecord::selectRaw("COALESCE(NULLIF(performed_service, ''), 'Unspecified') as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(function ($service) {
                return ['label' => $service->label, 'value' => (int) $service->total];
            });

        return [
            'type' => 'bar',
            'title' => 'Consultations by Service',
            'subtitle' => 'Top services by consultation volume',
            'items' => $services,
        ];
    }
}
