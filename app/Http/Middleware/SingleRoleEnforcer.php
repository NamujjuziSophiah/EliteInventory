<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class SingleRoleEnforcer
{
    /**
     * Prevent registration of a role if it's already taken.
     * Expect role passed as request input 'role'.
     */
    public function handle($request, Closure $next)
    {
        $role = $request->input('role');
        if ($role) {
            $exists = DB::table('users')->where('role', $role)->whereNull('deleted_at')->exists();
            if ($exists) {
                return redirect()->back()->withErrors(['role' => "The selected role ($role) is already assigned."]);
            }
        }

        return $next($request);
    }
}
