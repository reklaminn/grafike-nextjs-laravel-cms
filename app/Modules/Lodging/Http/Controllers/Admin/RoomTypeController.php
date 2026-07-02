<?php

declare(strict_types=1);

namespace App\Modules\Lodging\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Lodging\Http\Requests\Admin\StoreRoomTypeRequest;
use App\Modules\Lodging\Models\RoomType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Oda Tipleri CRUD (TourTag/TourCategory pattern'inde).
 */
class RoomTypeController extends Controller
{
    public function index(): View
    {
        $roomTypes = RoomType::query()->ordered()->paginate(30)->withQueryString();

        return view('lodging::admin.room-types.index', compact('roomTypes'));
    }

    public function create(): View
    {
        return view('lodging::admin.room-types.form', [
            'roomType' => new RoomType([
                'is_active'    => true,
                'currency'     => 'TRY',
                'capacity_min' => 1,
                'capacity_max' => 2,
                'unit_count'   => 1,
                'bedrooms'     => 1,
                'bathrooms'    => 1,
            ]),
        ]);
    }

    public function store(StoreRoomTypeRequest $request): RedirectResponse
    {
        $roomType = DB::transaction(function () use ($request) {
            $roomType = RoomType::create($this->payload($request));
            $this->uploadImages($request, $roomType);

            return $roomType;
        });

        return redirect()
            ->route('admin.lodging.room-types.edit', $roomType)
            ->with('success', 'Oda tipi oluşturuldu.');
    }

    public function edit(RoomType $roomType): View
    {
        return view('lodging::admin.room-types.form', compact('roomType'));
    }

    public function update(StoreRoomTypeRequest $request, RoomType $roomType): RedirectResponse
    {
        DB::transaction(function () use ($request, $roomType) {
            $roomType->update($this->payload($request));
            $this->uploadImages($request, $roomType);
        });

        return redirect()
            ->route('admin.lodging.room-types.edit', $roomType)
            ->with('success', 'Oda tipi güncellendi.');
    }

    public function destroy(RoomType $roomType): RedirectResponse
    {
        $roomType->delete();

        return redirect()
            ->route('admin.lodging.room-types.index')
            ->with('success', 'Oda tipi silindi.');
    }

    public function deleteMedia(RoomType $roomType, int $mediaId): RedirectResponse
    {
        $roomType->media()->where('id', $mediaId)->get()->each->delete();

        return back()->with('success', 'Görsel silindi.');
    }

    // ─────────────────────────────────────────────────────────────────────

    private function payload(StoreRoomTypeRequest $request): array
    {
        $data = $request->validated();

        // Amenities textarea (one per line) → array column.
        $amenities = collect(preg_split('/\r\n|\r|\n/', (string) ($data['amenities'] ?? '')))
            ->map(fn ($a) => trim($a))
            ->filter()
            ->values()
            ->all();

        return [
            'slug'         => $data['slug'],
            'name'         => $data['name'],
            'summary'      => $data['summary'] ?? null,
            'description'  => $data['description'] ?? null,
            'capacity_min' => (int) ($data['capacity_min'] ?? 1),
            'capacity_max' => (int) ($data['capacity_max'] ?? 2),
            'size_m2'      => $data['size_m2'] !== null && $data['size_m2'] !== '' ? (int) $data['size_m2'] : null,
            'bedrooms'     => (int) ($data['bedrooms'] ?? 1),
            'bathrooms'    => (int) ($data['bathrooms'] ?? 1),
            'base_price'   => (float) ($data['base_price'] ?? 0),
            'currency'     => strtoupper($data['currency'] ?? 'TRY'),
            'unit_count'   => (int) ($data['unit_count'] ?? 1),
            'amenities'    => $amenities,
            'sort_order'   => (int) ($data['sort_order'] ?? 0),
            'is_active'    => (bool) ($data['is_active'] ?? false),
        ];
    }

    private function uploadImages(StoreRoomTypeRequest $request, RoomType $roomType): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        foreach ($request->file('images') as $image) {
            $roomType->addMedia($image)->toMediaCollection('gallery');
        }
    }
}
