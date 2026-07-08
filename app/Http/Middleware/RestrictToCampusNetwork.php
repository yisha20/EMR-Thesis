<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\IpUtils;

class RestrictToCampusNetwork
{
    public function handle($request, Closure $next)
    {
        if (!config('campus.network_restriction')) {
            return $next($request);
        }

        $allowedIps = config('campus.allowed_ips', []);

        if (empty($allowedIps) || IpUtils::checkIp($request->ip(), $allowedIps)) {
            return $next($request);
        }

        abort(403, 'This EMR system is available only from the MSU-IIT campus network or an authorized ICTC connection.');
    }
}
