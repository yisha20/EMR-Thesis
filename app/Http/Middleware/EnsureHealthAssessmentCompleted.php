<?php

namespace App\Http\Middleware;

use Closure;

class EnsureHealthAssessmentCompleted
{
    public function handle($request, Closure $next)
    {
        $account = $request->user() ? $request->user()->ensurePatientAccount() : null;
        if ($account && ! $account->assessmentAllowsDashboard()) {
            return redirect()->route('patient.assessment.edit')
                ->with('assessment_required', 'Please complete the required Health Assessment Record before accessing the MSU-IIT Clinic Patient Portal.');
        }
        return $next($request);
    }
}
