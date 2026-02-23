<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

                $payload = $request->all();
                $json = json_encode($payload);
                if ($json === false) {
                    $json = json_encode(['error' => 'json_encode failed']);
                }
                // truncate to 1000 chars if needed
                if (mb_strlen($json) > 1000) {
                    $json = mb_substr($json, 0, 1000);
                }

                $auditableType = $request->route() ? ($request->route()->getName() ?? $request->path()) : $request->path();
                $auditableId = $request->route() ? ($request->route()->parameter('id') ?? null) : null;

                ActivityLog::create([
                    'user_id' => $user->id ?? null,
                    'role' => $user->role ?? null,
                    'auditable_type' => $auditableType,
                    'auditable_id' => $auditableId,
                    'action' => $method,
                    'old_values' => null,
                    'new_values' => $json,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            } catch (\Exception $e) {
                // log the exception so we can diagnose why audit logging fails
                Log::error('AuditLogMiddleware failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            }
        }

        return $response;
    }
}
