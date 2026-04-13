<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminUploadController extends Controller
{
    /**
     * POST /api/admin/upload
     * Accepts a file (image or video), stores it under storage/app/public/uploads/,
     * and returns the public URL.
     *
     * Run `php artisan storage:link` once so /storage → storage/app/public.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:20480'], // 20 MB
        ]);

        $file      = $request->file('file');
        $mime      = $file->getMimeType() ?? '';
        $isVideo   = str_starts_with($mime, 'video/');
        $subfolder = $isVideo ? 'videos' : 'images';
        $folder    = 'uploads/' . $subfolder . '/' . now()->format('Y/m');

        // Store with a random unique name to avoid collisions
        $ext      = $file->getClientOriginalExtension() ?: ($isVideo ? 'mp4' : 'jpg');
        $filename = Str::uuid() . '.' . strtolower($ext);

        $relativePath = $folder . '/' . $filename;

        $file->storeAs($folder, $filename, 'public');

        return response()->json([
            'path' => $relativePath,
            'url' => url('/api/media/' . $relativePath),
        ]);
    }
}
