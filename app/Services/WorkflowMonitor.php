<?php

namespace App\Services;

use App\SystemIncident;
use App\WorkflowActionLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class WorkflowMonitor
{
    public function generateReference()
    {
        return 'ERR-'.now()->format('Ymd-His').'-'.strtoupper(Str::random(6));
    }

    public function attempted($action, array $context = [])
    {
        return $this->writeAction($action, 'attempted', $context);
    }

    public function succeeded($action, array $context = [])
    {
        return $this->writeAction($action, 'succeeded', $context);
    }

    public function failed($action, Throwable $exception = null, array $context = [])
    {
        $reference = $context['error_reference'] ?? $this->generateReference();
        $context['error_reference'] = $reference;
        $context['http_status'] = $context['http_status'] ?? $this->statusFor($exception);
        $this->writeAction($action, 'failed', $context);

        $this->createIncident(array_merge($context, [
            'reference_code' => $reference,
            'severity' => ($context['http_status'] ?? 500) >= 500 ? 'high' : 'medium',
            'category' => $context['category'] ?? 'workflow',
            'event_type' => $action.'_failed',
            'safe_message' => $context['safe_message'] ?? 'A monitored workflow action could not be completed.',
            'technical_message' => $this->safeTechnicalMessage($exception),
        ]));

        return $reference;
    }

    public function createIncident(array $data)
    {
        if (! Schema::hasTable('system_incidents')) return null;

        $identity = $this->identity($data);
        $dedupe = $data['deduplication_key'] ?? null;
        if ($dedupe) {
            $existing = SystemIncident::where('deduplication_key', $dedupe)
                ->whereIn('status', ['open', 'investigating'])->first();
            if ($existing) {
                $existing->update(['detected_at' => now()]);
                return $existing;
            }
        }

        return SystemIncident::create(array_merge($identity, [
            'reference_code' => $data['reference_code'] ?? $this->generateReference(),
            'severity' => $data['severity'] ?? 'medium',
            'category' => $data['category'] ?? 'workflow',
            'event_type' => Str::limit($data['event_type'] ?? 'unknown_event', 100, ''),
            'deduplication_key' => $dedupe,
            'status' => $data['status'] ?? 'open',
            'safe_message' => Str::limit(strip_tags($data['safe_message'] ?? 'Monitoring detected an issue.'), 1000),
            'technical_message' => $this->sanitize($data['technical_message'] ?? null),
            'detected_at' => $data['detected_at'] ?? now(),
        ]));
    }

    public function resolveIncident(SystemIncident $incident, $status, $notes = null, $resolverId = null)
    {
        $incident->update([
            'status' => $status,
            'resolved_at' => in_array($status, ['resolved', 'false_positive'], true) ? now() : null,
            'resolved_by' => in_array($status, ['resolved', 'false_positive'], true) ? ($resolverId ?: Auth::id()) : null,
            'resolution_notes' => $this->sanitize($notes),
        ]);
        return $incident;
    }

    public function safeTechnicalMessage(Throwable $exception = null)
    {
        if (! $exception) return null;
        return $this->sanitize(get_class($exception).': '.$exception->getMessage());
    }

    private function writeAction($action, $result, array $context)
    {
        if (! Schema::hasTable('workflow_action_logs')) return null;
        $identity = $this->identity($context);
        unset($identity['request_method']);
        return WorkflowActionLog::create(array_merge($identity, [
            'action_name' => Str::limit($action, 100, ''),
            'result' => $result,
            'error_reference' => $context['error_reference'] ?? null,
            'duration_ms' => isset($context['duration_ms']) ? max(0, (int) $context['duration_ms']) : null,
        ]));
    }

    private function identity(array $context)
    {
        $user = Auth::user();
        return [
            'user_id' => $context['user_id'] ?? optional($user)->id,
            'user_role' => $context['user_role'] ?? optional(optional($user)->role)->name,
            'resource_type' => isset($context['resource_type']) ? Str::limit($context['resource_type'], 80, '') : null,
            'resource_id' => isset($context['resource_id']) && is_numeric($context['resource_id']) ? (int) $context['resource_id'] : null,
            'route_name' => $context['route_name'] ?? optional(request()->route())->getName(),
            'request_method' => $context['request_method'] ?? request()->method(),
            'http_status' => $context['http_status'] ?? null,
        ];
    }

    private function sanitize($value)
    {
        if ($value === null || $value === '') return null;
        $value = strip_tags((string) $value);
        $value = preg_replace('/([A-Z]:\\\\|\/)(?:[^\s:]+[\\\\\/])+[^\s:]*/i', '[private-path]', $value);
        $value = preg_replace('/\b(password|password_confirmation|remember_token|reset_token|soap|diagnosis|prescription)\b\s*[:=]\s*[^,;\s]+/i', '$1=[redacted]', $value);
        $value = preg_replace('/\s+/', ' ', $value);
        return Str::limit(trim($value), 2000);
    }

    private function statusFor(Throwable $exception = null)
    {
        return $exception && method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;
    }
}
