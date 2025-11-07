<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageController extends Controller
{
    /**
     * Serve a file from the public storage disk. This is a fallback for setups
     * where `php artisan storage:link` hasn't been created.
     */
    public function show(Request $request, $path)
    {
        $path = ltrim($path, '/');
        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            abort(404);
        }

        $mime = $disk->mimeType($path) ?? 'application/octet-stream';
        $stream = $disk->readStream($path);
        return response()->stream(function() use ($stream) {
            while (! feof($stream)) {
                echo fread($stream, 8192);
            }
        }, 200, [
            'Content-Type' => $mime,
            'Content-Length' => $disk->size($path),
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
