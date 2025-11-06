<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function edit()
    {
        $settings = Setting::first();
        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        // Ensure copyright cannot be submitted/updated via the settings form
        $request->request->remove('copyright');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'currency' => 'nullable|string|max:10',
            'logo' => 'nullable|image|max:2048',
            'auto_redirect' => 'nullable|boolean',
            'default_markup_percent' => 'nullable|numeric|min:0',
            'sku_prefix' => 'nullable|string|max:10',
            'sku_padding' => 'nullable|integer|min:0',
            'sku_next' => 'nullable|integer|min:0',
        ]);

        $settings = Setting::first();
        if (! $settings) {
            $settings = new Setting();
        }

        $settings->name = $data['name'];
        $settings->currency = $data['currency'] ?? $settings->currency;
        $settings->auto_redirect = isset($data['auto_redirect']) ? (bool)$data['auto_redirect'] : ($settings->auto_redirect ?? false);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('public/system');
            $settings->logo = $path;
        }

        // Save the default markup percent if provided
        if ($request->filled('default_markup_percent')) {
            $settings->default_markup_percent = (float) $request->input('default_markup_percent');
        }

        // SKU settings
        if ($request->filled('sku_prefix')) {
            $settings->sku_prefix = $request->input('sku_prefix');
        }
        if ($request->filled('sku_padding')) {
            $settings->sku_padding = (int) $request->input('sku_padding');
        }
        if ($request->filled('sku_next')) {
            $settings->sku_next = (int) $request->input('sku_next');
        }

        $settings->save();

        return redirect()->route('admin.settings.edit')->with('status', 'Settings updated.');
    }

    // Force logout all users by clearing sessions and remember tokens
    public function forceLogout(Request $request)
    {
        // Only allow this for admins (route is inside admin middleware)
        $driver = config('session.driver');

        if ($driver === 'file') {
            $files = glob(storage_path('framework/sessions/*')) ?: [];
            foreach ($files as $f) {
                @unlink($f);
            }
        } elseif ($driver === 'database') {
            DB::table(config('session.table', 'sessions'))->truncate();
        } elseif ($driver === 'redis') {
            try {
                \Illuminate\Support\Facades\Redis::connection()->del([config('session.prefix', 'laravel:').'sessions']);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // Clear remember tokens so 'remember me' won't re-authenticate
    DB::table('users')->update(['remember_token' => null]);

        // Record audit log
        try {
            $admin = auth()->user();
            \App\Models\AuditLog::create([
                'user_id' => $admin->id ?? null,
                'action' => 'force_logout_all_users',
                'ip' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                'meta' => [
                    'note' => 'force logout executed',
                ],
            ]);
        } catch (\Throwable $e) {
            // don't break the flow if audit logging fails
        }

        return redirect()->route('admin.settings.edit')->with('status', 'All users logged out.');
    }
}