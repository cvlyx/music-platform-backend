<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FileUploadController extends Controller
{
    public function uploadAudio(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:51200', 'mimes:mp3,wav,ogg,flac,m4a,aac'],
        ]);

        $file = $request->file('file');
        $cloudName = config('services.cloudinary.cloud_name');
        $apiKey = config('services.cloudinary.api_key');
        $apiSecret = config('services.cloudinary.api_secret');

        $response = Http::attach(
            'file', file_get_contents($file->getRealPath()), $file->getClientOriginalName()
        )->post("https://api.cloudinary.com/v1_1/{$cloudName}/upload", [
            'upload_preset' => config('services.cloudinary.upload_preset', 'ml_default'),
            'folder' => 'clique-music/songs',
            'resource_type' => 'video',
        ]);

        if ($response->successful()) {
            return response()->json([
                'url' => $response->json('secure_url'),
                'duration' => (int) ($response->json('duration') ?? 0),
                'public_id' => $response->json('public_id'),
            ]);
        }

        return response()->json(['message' => 'Upload failed'], 500);
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp'],
            'folder' => ['sometimes', 'string'],
        ]);

        $file = $request->file('file');
        $cloudName = config('services.cloudinary.cloud_name');
        $folder = $request->get('folder', 'clique-music/images');

        $response = Http::attach(
            'file', file_get_contents($file->getRealPath()), $file->getClientOriginalName()
        )->post("https://api.cloudinary.com/v1_1/{$cloudName}/upload", [
            'upload_preset' => config('services.cloudinary.upload_preset', 'ml_default'),
            'folder' => $folder,
            'resource_type' => 'image',
        ]);

        if ($response->successful()) {
            return response()->json([
                'url' => $response->json('secure_url'),
                'public_id' => $response->json('public_id'),
            ]);
        }

        return response()->json(['message' => 'Upload failed'], 500);
    }
}
