<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CloudinaryController extends Controller
{
    /**
     * GET /api/admin/cloudinary-signature
     * Returns a signed upload signature for direct browser → Cloudinary uploads.
     */
    public function signature(Request $request): JsonResponse
    {
        $cloudName = config('services.cloudinary.cloud_name');
        $apiKey    = config('services.cloudinary.api_key');
        $apiSecret = config('services.cloudinary.api_secret');

        if (! $cloudName || ! $apiKey || ! $apiSecret) {
            return response()->json(['error' => 'Cloudinary is not configured.'], 500);
        }

        $timestamp = time();
        $folder    = $request->query('folder', 'modern-electronics');
        $params    = ['folder' => $folder, 'timestamp' => $timestamp];

        ksort($params);
        $paramString = '';
        foreach ($params as $k => $v) {
            $paramString .= "{$k}={$v}&";
        }
        $paramString = rtrim($paramString, '&');

        $signature = sha1($paramString . $apiSecret);

        return response()->json([
            'signature'  => $signature,
            'timestamp'  => $timestamp,
            'cloudName'  => $cloudName,
            'apiKey'     => $apiKey,
            'folder'     => $folder,
        ]);
    }
}
