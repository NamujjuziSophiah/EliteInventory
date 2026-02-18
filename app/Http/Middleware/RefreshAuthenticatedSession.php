<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RefreshAuthenticatedSession
{
    /**
     * Refresh authenticated user session on every request.
     */
    public function handle($request, Closure $next)
    {
        if ($request->hasSession() && Auth::check()) {
            $request->session()->migrate(true);
            $request->session()->put('last_refreshed_at', now()->toDateTimeString());
        }

        return $next($request);
    }
}
