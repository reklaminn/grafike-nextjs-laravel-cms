<?php

declare(strict_types=1);

namespace App\Modules\Lodging\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Lodging\Enums\AvailabilityStatus;
use App\Modules\Lodging\Models\RoomAvailability;
use App\Modules\Lodging\Models\RoomType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Müsaitlik yönetimi — oda tipi seç, elle bloklu tarih aralıkları ekle/kaldır.
 * Onaylı rezervasyonlar `source=reservation` satırı olarak salt-okunur görünür.
 */
class AvailabilityController extends Controller
{
    public function index(Request $request): View
    {
        $roomTypes = RoomType::query()->ordered()->get();

        $selectedId = $request->integer('room_type') ?: $roomTypes->first()?->id;
        $selected   = $selectedId ? $roomTypes->firstWhere('id', $selectedId) : null;

        $rows = collect();
        if ($selected) {
            $rows = RoomAvailability::query()
                ->where('room_type_id', $selected->id)
                ->where('end_date', '>=', now()->toDateString())
                ->orderBy('start_date')
                ->with('reservation:id,code')
                ->get();
        }

        return view('lodging::admin.availability.index', [
            'roomTypes' => $roomTypes,
            'selected'  => $selected,
            'rows'      => $rows,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'room_type_id' => ['required', 'integer', 'exists:room_types,id'],
            'start_date'   => ['required', 'date'],
            'end_date'     => ['required', 'date', 'after:start_date'],
            'qty'          => ['nullable', 'integer', 'min:1', 'max:1000'],
            'note'         => ['nullable', 'string', 'max:500'],
        ]);

        RoomAvailability::create([
            'room_type_id' => $data['room_type_id'],
            'start_date'   => $data['start_date'],
            'end_date'     => $data['end_date'],
            'qty'          => (int) ($data['qty'] ?? 1),
            'status'       => AvailabilityStatus::Blocked->value,
            'source'       => 'manual',
            'note'         => $data['note'] ?? null,
        ]);

        return redirect()
            ->route('admin.lodging.availability.index', ['room_type' => $data['room_type_id']])
            ->with('success', 'Tarih aralığı bloklandı.');
    }

    public function destroy(RoomAvailability $availability): RedirectResponse
    {
        // Reservation-derived rows are managed by the reservation lifecycle;
        // only manual blocks are removable here.
        if ($availability->source !== 'manual') {
            return back()->with('error', 'Rezervasyon kaynaklı kayıtlar buradan silinemez; rezervasyonu iptal edin.');
        }

        $roomTypeId = $availability->room_type_id;
        $availability->delete();

        return redirect()
            ->route('admin.lodging.availability.index', ['room_type' => $roomTypeId])
            ->with('success', 'Blok kaldırıldı.');
    }
}
