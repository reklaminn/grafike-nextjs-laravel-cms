<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $query = Media::latest();

        if ($search = $request->input('search')) {
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

        $media = $query->paginate(48)->withQueryString();

        // JSON response for picker / AJAX calls
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $media->getCollection()->map(fn ($m) => [
                    'id'            => $m->id,
                    'name'          => $m->name,
                    'file_name'     => $m->file_name,
                    'mime_type'     => $m->mime_type,
                    'size'          => $m->size,
                    'url'           => $m->getUrl(),
                    'thumbnail_url' => str_starts_with($m->mime_type, 'image/') ? $m->getUrl() : null,
                ]),
                'meta' => [
                    'total'        => $media->total(),
                    'per_page'     => $media->perPage(),
                    'current_page' => $media->currentPage(),
                    'last_page'    => $media->lastPage(),
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
        $request->validate([
            'file' => 'required|file|max:' . config('cms.media.max_upload_size', 10240),
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, config('cms.media.allowed_extensions', []))) {
            return response()->json(['error' => 'Bu dosya uzantısına izin verilmiyor.'], 422);
        }

        // Pakete göre depolama kotası — aktif tenant kotasını aşacaksa reddet.
        $tenant = (function_exists('tenancy') && tenancy()->initialized) ? tenancy()->tenant : null;
        if ($tenant) {
            $meter = app(\App\Services\Tenancy\TenantUsageMeter::class);
            if ($meter->wouldExceedStorage($tenant, (int) $file->getSize())) {
                $quota = $tenant->packageConfig()['max_storage_mb'] ?? null;

                return response()->json([
                    'error' => "Depolama kotası ({$quota} MB) aşıldı. Paketi yükseltin veya dosya silin.",
                ], 422);
            }
        }

        // Store as orphan media (not associated with a model yet)
        $path = $file->store('uploads/' . date('Y/m'), 'public');

        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => asset('storage/' . $path),
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ]);
    }

    public function show(Media $medium)
    {
        $medium->load('model');

        return view('admin.media.show', ['media' => $medium]);
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

        return back()->with('success', 'Medya bilgileri güncellendi.');
    }

    public function destroy(Media $medium)
    {
        $medium->delete();

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
