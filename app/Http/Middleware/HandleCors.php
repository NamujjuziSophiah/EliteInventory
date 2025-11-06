<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Minimal CORS middleware replacement.
 * Adds permissive CORS headers and responds to preflight OPTIONS requests.
 */
class HandleCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Determine allowed origins from env or APP_URL as fallback
        $allowed = env('CORS_ALLOWED_ORIGINS');
        if (empty($allowed)) {
            $appUrl = env('APP_URL');
            $allowed = $appUrl ? $appUrl : '';
        }

        $allowedOrigins = array_filter(array_map('trim', explode(',', $allowed)));

        $origin = $request->headers->get('origin');

        $isAllowed = false;
        if ($origin && in_array($origin, $allowedOrigins, true)) {
            $isAllowed = true;
        }

        $headers = [
            // Only echo back allowed origin, do not use wildcard when restricting
            'Access-Control-Allow-Origin' => $isAllowed ? $origin : null,
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN',
        ];

        // Respond to preflight requests immediately
        if ($request->getMethod() === 'OPTIONS') {
            $response = response()->json('OK', 200);
            if ($isAllowed) {
                $response->headers->set('Access-Control-Allow-Origin', $origin);
                $response->headers->set('Vary', 'Origin');
            }
            $response->headers->set('Access-Control-Allow-Methods', $headers['Access-Control-Allow-Methods']);
            $response->headers->set('Access-Control-Allow-Headers', $headers['Access-Control-Allow-Headers']);
            return $response;
        }

        $response = $next($request);

        if ($isAllowed) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Vary', 'Origin');
        }
        $response->headers->set('Access-Control-Allow-Methods', $headers['Access-Control-Allow-Methods']);
        $response->headers->set('Access-Control-Allow-Headers', $headers['Access-Control-Allow-Headers']);

        return $response;
    }
}
