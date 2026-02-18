<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class ValidateSessionOnRestart
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Get or create the app start time marker
        $appStartMarker = Cache::rememberForever('app_start_marker', function () {
            return time();
        });

        // Get the session creation time (stored when user logs in)
        $sessionCreatedAt = session('_session_created_at');

        // If user is authenticated
        if (Auth::check()) {
            // If session was created before app restart, log them out
            if ($sessionCreatedAt && $sessionCreatedAt < $appStartMarker) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/login')->with('message', 'Session expired. Please log in again.');
            }
        }

        // If session creation time doesn't exist, set it now
        if (!$sessionCreatedAt) {
            session(['_session_created_at' => time()]);
        }

        return $next($request);
    }
}
