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
        $logs = UserLog::orderBy('created_at', 'desc')->limit(20)->get();
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
        if (! Storage::disk('public')->exists($log->filename)) {
            abort(404);
        }

        $disk = Storage::disk('public');
        $stream = $disk->readStream($log->filename);
        $name = $log->title ?? basename($log->filename);

        return response()->streamDownload(function () use ($stream) {
            while (! feof($stream)) {
                echo fread($stream, 8192);
            }
            if (is_resource($stream)) fclose($stream);
        }, $name, [
            'Content-Type' => $log->mime_type ?? 'application/octet-stream',
            'Content-Length' => $disk->size($log->filename),
        ]);
    }
}
