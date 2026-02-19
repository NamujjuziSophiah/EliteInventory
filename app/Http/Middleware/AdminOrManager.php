<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOrManager
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthorized.');
        }

        $allowed = false;

        if (method_exists($user, 'hasAnyRole')) {
            $allowed = $user->hasAnyRole(['admin', 'manager']);
        } elseif (method_exists($user, 'hasRole')) {
            $allowed = $user->hasRole('admin') || $user->hasRole('manager');
        } else {
            $role = strtolower((string) ($user->role ?? $user->user_type ?? ''));
            $allowed = in_array($role, ['admin', 'manager'], true);
        }

        if (! $allowed) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}