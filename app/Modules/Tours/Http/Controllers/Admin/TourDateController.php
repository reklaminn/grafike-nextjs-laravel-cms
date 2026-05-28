<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Tours\Http\Requests\Admin\StoreTourDateRequest;
use App\Modules\Tours\Http\Requests\Admin\StoreTourDatesBulkRequest;
use App\Modules\Tours\Models\Tour;
use App\Modules\Tours\Models\TourDate;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Nested resource under Tour: admin.tours.dates.*
 *
 * Phase 5 will add bulk-create UX ("weekly recurring" departure
 * generator) + a FullCalendar.io drag UI; Phase 3 ships the
 * one-departure-at-a-time CRUD which is sufficient for the
 * small tour catalogues most agencies start with.
 */
class TourDateController extends Controller
{
    public function index(Tour $tour): View
    {
        $dates = $tour->dates()
            ->orderBy('starts_at')
            ->withCount(['bookings as bookings_count' => function ($q) {
                $q->whereIn('status', ['reserved', 'confirmed', 'completed']);
            }])
            ->paginate(50);

        return view('tours::admin.dates.index', compact('tour', 'dates'));
    }

    public function create(Tour $tour): View
    {
        $date = new TourDate([
            'capacity_total' => $tour->capacity_default,
            'capacity_left'  => $tour->capacity_default,
            'status'         => 'open',
        ]);

        return view('tours::admin.dates.form', compact('tour', 'date'));
    }

    public function store(StoreTourDateRequest $request, Tour $tour): RedirectResponse
    {
        $data = $request->validated();

        $tour->dates()->create([
            'starts_at'      => $data['starts_at'],
            'ends_at'        => $data['ends_at']        ?? null,
            'capacity_total' => (int) $data['capacity_total'],
            'capacity_left'  => (int) ($data['capacity_left'] ?? $data['capacity_total']),
            'price_override' => isset($data['price_override']) ? (int) $data['price_override'] : null,
            'status'         => $data['status'],
            'notes'          => $data['notes']          ?? null,
        ]);

        return redirect()
            ->route('admin.tours.dates.index', $tour)
            ->with('success', 'Departure tarihi eklendi.');
    }

    /**
     * Toplu tarih ekleme formu (cruise/günlük turlar için).
     * Birden çok kalkış tarihi + paylaşımlı gece sayısı / kapasite.
     */
    public function createBulk(Tour $tour): View
    {
        return view('tours::admin.dates.bulk', compact('tour'));
    }

    /**
     * Toplu tarih kaydı.  Her seçilen tarih için 1 TourDate oluşturur;
     * bitiş = kalkış + gece sayısı.  Aynı kalkış tarihi/saati zaten varsa
     * atlanır (idempotent — yanlışlıkla iki kez göndermeye karşı güvenli).
     */
    public function bulkStore(StoreTourDatesBulkRequest $request, Tour $tour): RedirectResponse
    {
        $data   = $request->validated();
        $nights = (int) ($data['nights'] ?? 0);
        $time   = $data['depart_time'] ?? '00:00';
        $cap    = (int) $data['capacity_total'];

        // Mevcut kalkış zamanlarını topla (dedup için)
        $existing = $tour->dates()->pluck('starts_at')->map(
            fn ($d) => Carbon::parse($d)->format('Y-m-d H:i')
        )->flip();

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($tour, $data, $nights, $time, $cap, $existing, &$created, &$skipped) {
            foreach ($data['dates'] as $rawDate) {
                $start = Carbon::parse($rawDate . ' ' . $time);
                $key   = $start->format('Y-m-d H:i');

                if (isset($existing[$key])) {
                    $skipped++;
                    continue;
                }

                $tour->dates()->create([
                    'starts_at'      => $start,
                    'ends_at'        => $nights > 0 ? $start->copy()->addDays($nights) : null,
                    'capacity_total' => $cap,
                    'capacity_left'  => $cap,
                    'price_override' => isset($data['price_override']) ? (int) $data['price_override'] : null,
                    'status'         => $data['status'],
                    'notes'          => $data['notes'] ?? null,
                ]);
                $created++;
            }
        });

        $msg = "{$created} departure eklendi.";
        if ($skipped > 0) {
            $msg .= " {$skipped} tarih zaten mevcut olduğu için atlandı.";
        }

        return redirect()
            ->route('admin.tours.dates.index', $tour)
            ->with('success', $msg);
    }

    public function edit(Tour $tour, TourDate $date): View
    {
        $this->ensureBelongs($tour, $date);

        return view('tours::admin.dates.form', compact('tour', 'date'));
    }

    public function update(StoreTourDateRequest $request, Tour $tour, TourDate $date): RedirectResponse
    {
        $this->ensureBelongs($tour, $date);

        $data = $request->validated();

        $date->update([
            'starts_at'      => $data['starts_at'],
            'ends_at'        => $data['ends_at']        ?? null,
            'capacity_total' => (int) $data['capacity_total'],
            'capacity_left'  => isset($data['capacity_left']) ? (int) $data['capacity_left'] : $date->capacity_left,
            'price_override' => isset($data['price_override']) ? (int) $data['price_override'] : null,
            'status'         => $data['status'],
            'notes'          => $data['notes']          ?? null,
        ]);

        return redirect()
            ->route('admin.tours.dates.index', $tour)
            ->with('success', 'Departure tarihi güncellendi.');
    }

    public function destroy(Tour $tour, TourDate $date): RedirectResponse
    {
        $this->ensureBelongs($tour, $date);

        if ($date->bookings()->whereIn('status', ['reserved', 'confirmed', 'completed'])->exists()) {
            return back()->with('error', 'Bu tarihte aktif rezervasyon var, önce iptal edin.');
        }

        $date->delete();

        return redirect()
            ->route('admin.tours.dates.index', $tour)
            ->with('success', 'Departure tarihi silindi.');
    }

    /**
     * Defensive guard against URL parameter mismatch — a determined
     * user could craft /admin/tours/X/dates/Y where date Y belongs
     * to tour Z.  Block at controller layer rather than relying on
     * route model binding semantics.
     */
    private function ensureBelongs(Tour $tour, TourDate $date): void
    {
        abort_unless($date->tour_id === $tour->id, 404);
    }
}
