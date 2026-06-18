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

        foreach ($this->fallbackPaths($path) as $fallbackPath) {
            if (is_file($fallbackPath)) {
                return $fallbackPath;
            }
        }

        // Son çare: Spatie media yolu "{id}/{file_name}" biçimindedir. Path'in
        // başındaki id'den Media kaydını bul ve onun KENDİ diski/yolundan servis
        // et — disk 'public' dışında (veya tenant root_override farklı) olsa bile
        // doğru dosyayı verir. Tenant DB'sine erişim için tenancy initialized
        // olmalı (route ?tenant ile sağlıyor).
        if (preg_match('#^(\d+)/#', $path, $m)) {
            try {
                $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find((int) $m[1]);
                if ($media) {
                    $absolute = $media->getPath();
                    if (is_file($absolute)) {
                        return $absolute;
                    }
                }
            } catch (\Throwable) {
                // tenant DB yoksa / media tablosu erişilemezse sessiz geç
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function fallbackPaths(string $path): array
    {
        $paths = [];

        // Tenant key: initialized tenancy'den, yoksa ?tenant query'sinden
        // (central admin domain'inde <img> isteği tenancy init etmemiş olabilir).
        $tenantKey = null;
        if (tenancy()->initialized && tenant()) {
            $tenantKey = (string) tenant()->getTenantKey();
        } elseif (is_string($q = request()->query('tenant')) && preg_match('/^[a-zA-Z0-9_-]+$/', $q)) {
            $tenantKey = $q;
        }

        if ($tenantKey) {
            $paths[] = base_path("storage/app/public/tenant_{$tenantKey}/{$path}");
            $paths[] = base_path("storage/app/public/tenant{$tenantKey}/{$path}");

            // Legacy filesystem bootstrap paths from earlier config.
            $paths[] = base_path("storage/tenant_{$tenantKey}/app/public/{$path}");
            $paths[] = base_path("storage/tenant{$tenantKey}/app/public/{$path}");
        }

        // Some theme assets are shared central theme records. If a file was
        // uploaded before/without tenant filesystem bootstrapping, keep preview
        // URLs working by falling back to central public storage.
        $paths[] = base_path('storage/app/public/'.$path);

        return array_values(array_unique($paths));
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
