<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureRole
{
    /**
     * Handle an incoming request.
     * Accepts single role or comma-separated roles.
     */
    public function handle($request, Closure $next, $roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        // Support both pipe '|' and comma ',' separators in middleware usage
        $allowed = array_map('trim', preg_split('/[|,]/', $roles));

        // Safely read user's role and deny if missing
        $userRole = optional($user)->role ?? data_get($user, 'role');
        if (empty($userRole)) {
            abort(403, 'Unauthorized. (no role assigned)');
        }

        if (! in_array($userRole, $allowed, true)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
