<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Log before attempt for debugging session/auth issues
        Log::info('Login attempt', ['email' => $credentials['email'], 'session_id' => $request->session()->getId()]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            $role = Auth::user()->role ?? null;

            Log::info('Login successful', ['user_id' => Auth::id(), 'email' => $credentials['email'], 'role' => $role, 'session_id' => $request->session()->getId(), 'auth_check' => Auth::check()]);
            switch ($role) {
                case 'admin':
                    return redirect('/admin');
                case 'manager':
                    return redirect('/manager');
                case 'cashier':
                    // Direct cashiers to their dashboard (retail POS link still available)
                    return redirect('/cashier/dashboard');
                default:
                    return redirect('/');
            }
        }

        Log::warning('Login failed', ['email' => $credentials['email'], 'session_id' => $request->session()->getId()]);

        return back()->withErrors(['email' => 'The provided credentials do not match our records.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
