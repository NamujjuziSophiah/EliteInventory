<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;

/**
 * Simple middleware that logs POST/PUT/PATCH/DELETE requests to activity_logs table.
 * It stores user, role, route action and request payload (truncated) for auditing.
 */
class AuditLogMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $method = strtoupper($request->method());
        if (in_array($method, ['POST','PUT','PATCH','DELETE'])) {
            try {
                $user = Auth::user();
                ActivityLog::create([
                    'user_id' => $user->id ?? null,
                    'role' => $user->role ?? null,
                    'auditable_type' => $request->route() ? ($request->route()->getName() ?? $request->path()) : $request->path(),
                    'auditable_id' => null,
                    'action' => $method,
                    'old_values' => null,
                    'new_values' => strlen(json_encode($request->all())) > 1000 ? substr(json_encode($request->all()), 0, 1000) : $request->all(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            } catch (\Exception $e) {
                // Do not break request on logging failure
            }
        }

        return $response;
    }
}
