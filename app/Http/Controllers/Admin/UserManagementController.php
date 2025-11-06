<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::withTrashed()->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|in:admin,manager,cashier',
        ]);

        // Prevent creating a user for a role that's already taken by an active user
        $role = $data['role'];
        $exists = User::where('role', $role)->whereNull('deleted_at')->exists();
        if ($exists) {
            return redirect()->back()->withErrors(['role' => 'The selected role is already assigned to an active user.'])->withInput();
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $role,
        ]);

        return redirect()->route('admin.users.index')->with('status', 'User created.');
    }

    public function edit($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::withTrashed()->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|string|in:admin,manager,cashier',
        ]);

        // If changing role and the role is taken by another active user, block
        if ($user->role !== $data['role']) {
            $exists = User::where('role', $data['role'])->whereNull('deleted_at')->where('id', '!=', $user->id)->exists();
            if ($exists) {
                return redirect()->back()->withErrors(['role' => 'The selected role is already assigned to another active user.'])->withInput();
            }
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return redirect()->route('admin.users.index')->with('status', 'User updated.');
    }

    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        if (! $user->trashed()) {
            return redirect()->route('admin.users.index')->with('status', 'User is not deleted.');
        }

        // Ensure role isn't taken currently
        $exists = User::where('role', $user->role)->whereNull('deleted_at')->exists();
        if ($exists) {
            return redirect()->route('admin.users.index')->withErrors(['role' => 'Cannot restore user: another active user has the same role.']);
        }

        $user->restore();
        return redirect()->route('admin.users.index')->with('status', 'User restored.');
    }

    public function restoreBulk(Request $request)
    {
        $ids = $request->input('restore_ids', []);
        if (empty($ids)) {
            return redirect()->route('admin.users.index')->with('status', 'No users selected for restore.');
        }

        $restored = 0;
        foreach ($ids as $id) {
            $user = User::withTrashed()->find($id);
            if (! $user || ! $user->trashed()) {
                continue;
            }

            // skip restore if role currently taken
            $exists = User::where('role', $user->role)->whereNull('deleted_at')->exists();
            if ($exists) {
                continue;
            }

            $user->restore();
            $restored++;
        }

        return redirect()->route('admin.users.index')->with('status', "$restored user(s) restored.");
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        // soft delete so DB unique constraints remain meaningful
        $user->delete();
        return redirect()->route('admin.users.index')->with('status', 'User deleted (soft-delete).');
    }

    // Additional actions (create/edit) can be added later
}

