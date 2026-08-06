<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof ValidationException) {
            $status = 422;
        } elseif ($exception instanceof AuthenticationException) {
            $status = 401;
        } elseif ($exception instanceof AuthorizationException) {
            $status = 403;
        } else {
            $prepared = $this->prepareException($exception);
            $status = $prepared instanceof HttpExceptionInterface ? $prepared->getStatusCode() : 500;
        }

        if (! app()->environment('testing') && in_array($status, [403, 404, 419, 500], true)) {
            try {
                $monitor = app(\App\Services\WorkflowMonitor::class);
                $reference = $request->attributes->get('monitoring_error_reference');
                if (! $reference) {
                    $incident = $monitor->createIncident([
                        'severity' => $status >= 500 ? 'high' : ($status === 403 ? 'medium' : 'low'),
                        'category' => $status === 403 ? 'authorization' : ($status >= 500 ? 'server' : 'workflow'),
                        'event_type' => 'http_'.$status,
                        'deduplication_key' => implode(':', ['http', $status, optional($request->route())->getName() ?: 'unnamed', optional($request->user())->id ?: 'guest']),
                        'route_name' => optional($request->route())->getName(),
                        'request_method' => $request->method(),
                        'http_status' => $status,
                        'safe_message' => $status === 403 ? 'An authorization request was denied.' : 'An application request could not be completed.',
                        'technical_message' => $monitor->safeTechnicalMessage($exception),
                    ]);
                    $reference = optional($incident)->reference_code;
                }
                if ($status >= 500 && $reference && ! $request->expectsJson()) {
                    return response()->view('errors.500', ['errorReference' => $reference], 500);
                }
            } catch (\Throwable $monitoringFailure) {
                // Monitoring must never replace the application's original error response.
            }
        }
        return parent::render($request, $exception);
    }
}
