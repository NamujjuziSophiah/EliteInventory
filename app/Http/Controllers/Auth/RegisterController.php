<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|in:admin,manager,cashier',
        ]);

        // Strong server-side enforcement: prevent registration when the selected role is already taken
        // Wrap creation in a transaction to avoid races and check role existence before creating.
        $role = $data['role'];

        $exists = User::where('role', $role)->whereNull('deleted_at')->exists();
        if ($exists) {
            return redirect()->back()->withErrors(['role' => 'The selected role is already assigned.'])->withInput();
        }

        // Prevent same personal details (name) from being used to register for a different role.
        $conflict = User::where('name', $data['name'])
            ->whereNull('deleted_at')
            ->where('role', '!=', $role)
            ->exists();

        if ($conflict) {
            return redirect()->back()->withErrors(['name' => 'A user with these personal details already exists with a different role. Please use different details or contact the administrator.'])->withInput();
        }

        // Create user inside a transaction
        try {
            DB::beginTransaction();
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $role,
            ]);
            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['role' => 'The selected role is no longer available.'])->withInput();
        }

        // Do NOT auto-login the user after registration. Instead send them to login.
        return redirect()->route('login')->with('status', 'Registration successful. Please log in to continue.');
    }
}
