<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\UserLog;

class UserLogController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user && ($user->role ?? null) === 'admin') {
            $logs = UserLog::orderBy('created_at', 'desc')->limit(50)->get();
        } else {
            $logs = UserLog::where('user_id', auth()->id())->orderBy('created_at', 'desc')->limit(50)->get();
        }
        return view('admin.user_logs.index', compact('logs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'logfile' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('logfile');
        $path = $file->store('user_logs', 'public');

        $log = UserLog::create([
            'user_id' => auth()->id(),
            'title' => $data['title'] ?? $file->getClientOriginalName(),
            'filename' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        return redirect()->back()->with('success', 'Log uploaded');
    }

    public function download($id)
    {
        $log = UserLog::findOrFail($id);
        $user = auth()->user();
        // allow if admin or owner
        // Use non-strict comparison for the id check to avoid type-mismatch between
        // the authenticated user's id (int) and the model's stored user_id (may be
        // returned as string by some drivers). This keeps owner checks robust in
        // tests and different DB drivers.
        if (! ($user && (($user->role ?? null) === 'admin' || $user->id == $log->user_id))) {
            abort(403);
        }

        if (! Storage::disk('public')->exists($log->filename)) {
            abort(404);
        }

        $disk = Storage::disk('public');
        $name = $log->title ?? basename($log->filename);

        // Return file contents directly for simpler handling across drivers and tests
        $content = $disk->get($log->filename);
        return response($content, 200, [
            'Content-Type' => $log->mime_type ?? 'application/octet-stream',
            'Content-Length' => $disk->size($log->filename),
            'Content-Disposition' => 'attachment; filename="' . $name . '"',
        ]);
    }
}
