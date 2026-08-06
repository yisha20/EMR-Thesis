<?php

namespace App\Http\Middleware;

use App\Services\WorkflowMonitor;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class MonitorWorkflowActions
{
    private const ACTIONS = [
        'student.register.store' => 'register_account',
        'patient.assessment.submit' => 'submit_health_assessment',
        'student.complaints.store' => 'submit_complaint',
        'patient.queue.acknowledge' => 'acknowledge_queue_call',
        'clinic-queues.store' => 'generate_queue_number',
        'clinic-queues.call-next' => 'call_next',
        'clinic-queues.update' => 'update_queue_status',
        'clinic-queues.transfer' => 'transfer_to_doctor',
        'clinic-queues.requeue' => 'recall_or_requeue',
        'counter-services.start' => 'start_counter_service',
        'counter-services.complete' => 'complete_counter_service',
        'consultations.call-student' => 'call_consultation_patient',
        'doctor.consultations.start' => 'start_consultation',
        'doctor.patients.health-record' => 'view_patient_record',
        'student-complaints.status' => 'review_or_triage_complaint',
        'student-complaints.resolve-counter' => 'route_to_counter',
        'student-complaints.forward' => 'forward_to_doctor',
        'student-complaints.start-consultation' => 'start_consultation',
        'student-complaints.complete-consultation' => 'complete_consultation',
        'student-complaints.clinical-notes' => 'save_soap',
        'consultations.medical-certificates.store' => 'generate_medical_certificate',
        'medical-certificates.issue' => 'issue_medical_certificate',
        'prescriptions.download' => 'download_authorized_document',
        'medical-certificates.pdf' => 'download_authorized_document',
        'health-assessments.pdf' => 'download_authorized_document',
        'emergency-intakes.store' => 'create_emergency_walk_in',
        'emergency-intakes.acknowledge' => 'assign_emergency_doctor',
        'patient-merges.store' => 'merge_provisional_record',
    ];

    public function handle($request, Closure $next)
    {
        $routeName = optional($request->route())->getName();
        $action = self::ACTIONS[$routeName] ?? null;
        if (! $action) return $next($request);

        $monitor = app(WorkflowMonitor::class);
        $context = $this->context($request, $routeName);
        $started = microtime(true);
        $monitor->attempted($action, $context);

        try {
            $response = $next($request);
            $context['http_status'] = $response->getStatusCode();
            $context['duration_ms'] = (int) ((microtime(true) - $started) * 1000);
            if ($response->getStatusCode() >= 400) {
                $monitor->failed($action, null, $context);
            } else {
                $monitor->succeeded($action, $context);
            }
            return $response;
        } catch (Throwable $exception) {
            if ($exception instanceof ValidationException || $exception instanceof AuthenticationException
                || ($exception instanceof HttpExceptionInterface && $exception->getStatusCode() < 500)) {
                throw $exception;
            }
            $context['duration_ms'] = (int) ((microtime(true) - $started) * 1000);
            $reference = $monitor->failed($action, $exception, $context);
            $request->attributes->set('monitoring_error_reference', $reference);
            throw $exception;
        }
    }

    private function context($request, $routeName)
    {
        $parameters = $request->route() ? $request->route()->parameters() : [];
        foreach ($parameters as $type => $resource) {
            if (is_object($resource) && isset($resource->id)) {
                return ['route_name' => $routeName, 'resource_type' => $type, 'resource_id' => $resource->id];
            }
            if (is_numeric($resource)) {
                return ['route_name' => $routeName, 'resource_type' => $type, 'resource_id' => $resource];
            }
        }
        return ['route_name' => $routeName];
    }
}
