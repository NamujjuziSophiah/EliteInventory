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

        // Default MIME type
        $mime = 'application/octet-stream';
        try {
            // Prefer using the local filesystem path when available (local "public" disk)
            if (method_exists($disk, 'path')) {
                /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                $localPath = $disk->path($path);
                if (is_file($localPath)) {
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $detected = $finfo->file($localPath);
                    if ($detected) {
                        $mime = $detected;
                    }
                }
            } else {
                // As a safe fallback, attempt to call mimeType only if the method exists.
                // Wrap in try/catch because some adapters may throw if they don't support
                // mime type detection or if the underlying driver can't access the file.
                try {
                    /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                    if (method_exists($disk, 'mimeType') || is_callable([$disk, 'mimeType'])) {
                        $detected = $disk->mimeType($path);
                        if ($detected) {
                            $mime = $detected;
                        }
                    }
                } catch (\Throwable $e) {
                    // ignore and continue with default mime
                }
            }
        } catch (\Throwable $e) {
            // ignore and keep default mime
        }

        $stream = $disk->readStream($path);
        if ($stream === false || ! is_resource($stream)) {
            abort(500);
        }

        return response()->stream(function() use ($stream) {
            while (! feof($stream)) {
                echo fread($stream, 8192);
            }
            // make sure to close the stream when done
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mime,
            'Content-Length' => $disk->size($path),
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
