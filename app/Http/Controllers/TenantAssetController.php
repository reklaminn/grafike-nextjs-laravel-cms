<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TenantAssetController extends Controller
{
    public function show(string $path): BinaryFileResponse
    {
        $path = $this->normalizePath($path);

        if ($path === null) {
            abort(404);
        }

        $fullPath = $this->resolveAssetPath($path);

        if ($fullPath === null) {
            abort(404);
        }

        $mimeType = File::mimeType($fullPath) ?: 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    private function resolveAssetPath(string $path): ?string
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->path($path);
        }

        // Some theme assets are shared central theme records. If a file was
        // uploaded before/without tenant filesystem bootstrapping, keep preview
        // URLs working by falling back to central public storage.
        $centralPath = base_path('storage/app/public/'.$path);

        return is_file($centralPath) ? $centralPath : null;
    }

    private function normalizePath(string $path): ?string
    {
        $path = str_replace('\\', '/', rawurldecode($path));
        $path = ltrim($path, '/');

        if ($path === '' || str_contains($path, '..') || str_contains($path, "\0")) {
            return null;
        }

        return $path;
    }
}
