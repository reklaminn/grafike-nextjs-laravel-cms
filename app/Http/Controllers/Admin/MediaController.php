<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        // `search` (medya kütüphanesi sayfası) + `q` (picker) ikisini de kabul et.
        $search = $request->input('search', $request->input('q'));

        $query = Media::latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('file_name', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            match ($type) {
                'image' => $query->where('mime_type', 'like', 'image/%'),
                'video' => $query->where('mime_type', 'like', 'video/%'),
                'document' => $query->whereIn('mime_type', [
                    'application/pdf', 'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]),
                default => null,
            };
        }

        if ($collection = $request->input('collection')) {
            $query->where('collection_name', $collection);
        }

        // JSON response for picker / AJAX calls — per_page'i (sınırlı) onurlandır,
        // zengin metadata + filtre için collection listesini de döndür.
        if ($request->expectsJson()) {
            $perPage = min(max((int) $request->input('per_page', 40), 1), 100);
            $media   = $query->paginate($perPage)->withQueryString();

            return response()->json([
                'data' => $media->getCollection()->map(fn ($m) => [
                    'id'            => $m->id,
                    'name'          => $m->name,
                    'file_name'     => $m->file_name,
                    'mime_type'     => $m->mime_type,
                    'size'          => $m->size,
                    'url'           => $m->getUrl(),
                    'thumbnail_url' => str_starts_with((string) $m->mime_type, 'image/') ? $m->getUrl() : null,
                    'is_image'      => str_starts_with((string) $m->mime_type, 'image/'),
                    'collection'    => $m->collection_name,
                    'alt_text'      => (string) ($m->getCustomProperty('alt_text') ?? ''),
                    'created_at'    => optional($m->created_at)->format('d.m.Y'),
                ]),
                'meta' => [
                    'total'        => $media->total(),
                    'per_page'     => $media->perPage(),
                    'current_page' => $media->currentPage(),
                    'last_page'    => $media->lastPage(),
                    'collections'  => Media::query()
                        ->select('collection_name')
                        ->distinct()
                        ->orderBy('collection_name')
                        ->pluck('collection_name')
                        ->filter()
                        ->values(),
                ],
            ]);
        }

        $media = $query->paginate(24)->withQueryString();

        // Get available collections for filter
        $collections = Media::select('collection_name')
            ->distinct()
            ->pluck('collection_name');

        // Stats
        $stats = [
            'total' => Media::count(),
            'images' => Media::where('mime_type', 'like', 'image/%')->count(),
            'total_size' => Media::sum('size'),
        ];

        return view('admin.media.index', compact('media', 'collections', 'stats'));
    }

    public function upload(Request $request)
    {
        // Maks dosya boyutu site ayarından (yoksa config varsayılanı, KB).
        $maxSizeKb = (int) \App\Models\SiteSetting::get('media.max_size_kb', config('cms.media.max_upload_size', 10240));

        $request->validate([
            'file' => 'required|file|max:' . $maxSizeKb,
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, config('cms.media.allowed_extensions', []))) {
            return response()->json(['error' => 'Bu dosya uzantısına izin verilmiyor.'], 422);
        }

        // SVG → gömülü script/onload XSS riski; içeriği sanitize et
        if (! \App\Services\Media\SvgGuard::sanitizeIfSvg($file)) {
            return response()->json(['error' => 'SVG dosyası güvenli değil veya bozuk.'], 422);
        }

        // Site ayarlarına göre yeniden boyutlandır + sıkıştır (raster görseller).
        // İşlenirse depoya işlenmiş geçici dosya gider; aksi halde orijinal.
        $processed = app(\App\Services\Media\MediaImageProcessor::class)->process($file, [
            'enabled'    => \App\Models\SiteSetting::get('media.compress_enabled', '1') === '1',
            'max_width'  => (int) \App\Models\SiteSetting::get('media.max_width', 2560),
            'max_height' => (int) \App\Models\SiteSetting::get('media.max_height', 0),
            'quality'    => (int) \App\Models\SiteSetting::get('media.quality', 82),
            'to_webp'    => \App\Models\SiteSetting::get('media.to_webp', '0') === '1',
        ]);

        // Depoya gidecek gerçek boyut (işlenmişse o, değilse orijinal).
        $effectiveSize = $processed ? (int) @filesize($processed['path']) : (int) $file->getSize();

        // Pakete göre depolama kotası — aktif tenant kotasını aşacaksa reddet.
        $tenant = (function_exists('tenancy') && tenancy()->initialized) ? tenancy()->tenant : null;
        if ($tenant) {
            $meter = app(\App\Services\Tenancy\TenantUsageMeter::class);
            if ($meter->wouldExceedStorage($tenant, $effectiveSize)) {
                if ($processed) {
                    @unlink($processed['path']); // geçici dosyayı temizle
                }
                $quota = $tenant->packageConfig()['max_storage_mb'] ?? null;

                return response()->json([
                    'error' => "Depolama kotası ({$quota} MB) aşıldı. Paketi yükseltin veya dosya silin.",
                ], 422);
            }
        }

        // Proper Spatie Media record on a standalone MediaAsset owner.
        // (Eski hali orphan dosya kaydedip asset('storage/..') döndürüyordu →
        //  grid'de görünmüyor + tenant'ta /tenancy/assets/storage/.. 500.)
        // addMedia, çalışan kapak/önizleme akışıyla aynı disk + getUrl() üretir.
        $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $asset = \App\Models\MediaAsset::create(['name' => $baseName]);

        if ($processed) {
            // İşlenmiş geçici dosyadan ekle; orijinal adı koru, uzantı değişebilir
            // (örn. .webp'ye çevrildiyse). addMedia geçici dosyayı taşır.
            $media = $asset->addMedia($processed['path'])
                ->usingName($baseName)
                ->usingFileName($baseName . '.' . $processed['extension'])
                ->toMediaCollection('library');
        } else {
            $media = $asset->addMedia($file)->toMediaCollection('library');
        }

        return response()->json([
            'success'   => true,
            'id'        => $media->id,
            'url'       => $media->getUrl(),
            'name'      => $media->name,
            'file_name' => $media->file_name,
            'size'      => $media->size,
            'mime'      => $media->mime_type,
            'disk'      => $media->disk,
            'path'      => $media->getPathRelativeToRoot(),
        ]);
    }

    public function show(Media $medium)
    {
        $medium->load('model');

        return view('admin.media.show', ['media' => $medium]);
    }

    /**
     * Tek görsel için AI alt yazısı üret — sonucu JSON döner, UI input'u
     * doldurur; admin gözden geçirip normal formdan kaydeder.
     */
    public function generateAlt(Media $medium, \App\Services\Ai\AiAltTextGenerator $generator)
    {
        $tenant = (function_exists('tenancy') && tenancy()->initialized) ? tenancy()->tenant : null;

        try {
            $alt = $generator->generate($medium, $tenant);
        } catch (\App\Services\Ai\Exceptions\AiQuotaExceededException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 402);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['ok' => true, 'alt' => $alt]);
    }

    /**
     * Alt yazısı eksik tüm görseller için kuyrukta toplu üretim başlat.
     */
    public function generateAltBulk()
    {
        $missing = Media::query()
            ->where('mime_type', 'like', 'image/%')
            ->where('mime_type', '!=', 'image/svg+xml')
            ->get()
            ->filter(fn (Media $media) => blank($media->getCustomProperty('alt_text')));

        foreach ($missing as $media) {
            \App\Jobs\Ai\GenerateAltTextJob::dispatch($media->id);
        }

        return back()->with(
            'success',
            $missing->isEmpty()
                ? 'Tüm görsellerin alt yazısı zaten dolu.'
                : "{$missing->count()} görsel için alt yazısı üretimi kuyruğa alındı — birkaç dakika içinde tamamlanır."
        );
    }

    public function update(Request $request, Media $medium)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'custom_properties.alt_text' => 'nullable|string|max:255',
            'custom_properties.title' => 'nullable|string|max:255',
        ]);

        $medium->name = $validated['name'];

        if (isset($validated['custom_properties'])) {
            foreach ($validated['custom_properties'] as $key => $value) {
                $medium->setCustomProperty($key, $value);
            }
        }

        $medium->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success'  => true,
                'id'       => $medium->id,
                'name'     => $medium->name,
                'alt_text' => (string) ($medium->getCustomProperty('alt_text') ?? ''),
            ]);
        }

        return back()->with('success', 'Medya bilgileri güncellendi.');
    }

    public function destroy(Request $request, Media $medium)
    {
        $id = $medium->id;
        $medium->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'id' => $id]);
        }

        return back()->with('success', 'Medya dosyası silindi.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        Media::whereIn('id', $request->input('ids'))->each(function ($media) {
            $media->delete();
        });

        return back()->with('success', count($request->input('ids')) . ' medya dosyası silindi.');
    }
}
