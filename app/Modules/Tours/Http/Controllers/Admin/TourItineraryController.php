<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Modules\Tours\Http\Requests\Admin\StoreTourItineraryRequest;
use App\Modules\Tours\Models\Port;
use App\Modules\Tours\Models\Tour;
use App\Modules\Tours\Models\TourItinerary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Rota Takvimi editörü (eski sistem Tab 2 karşılığı) — dil bazlı.
 *
 * Yapı dil bazlı tutuluyor (eski sistemde de "Her dil için ayrı rota").
 * `?lang=` query param ile hangi dilin rotasının düzenleneceği seçilir;
 * verilmezse ilk aktif dil.
 *
 * Stop'lar gün numarasına göre gruplanır: aynı güne birden çok port
 * (multi-stop, 1,1,1,2,3,3 deseni) düşebilir.  Kayıtta gün+stop yapısı
 * tamamen silinip yeniden kurulur (booking referansı yok → güvenli).
 */
class TourItineraryController extends Controller
{
    public function edit(Tour $tour, Request $request): View
    {
        $languages = Language::active()->orderBy('sort_order')->get();

        // Hangi dilin rotası? ?lang param ya da ilk dil.
        $languageId = (int) $request->integer('lang');
        if ($languageId === 0) {
            $languageId = (int) ($languages->first()?->id ?? 0);
        }

        $itinerary = $tour->itineraries()
            ->where('language_id', $languageId)
            ->with(['days.stops.port.translations', 'originPort.translations'])
            ->first();

        // Stop satırlarını flat listeye düz (Alpine x-data init JSON)
        $stopRows = [];
        if ($itinerary) {
            foreach ($itinerary->days->sortBy('day_number') as $day) {
                foreach ($day->stops->sortBy('sort_order') as $stop) {
                    $stopRows[] = [
                        'day_number'     => $day->day_number,
                        'point_type'     => $stop->point_type ?? 'visit',
                        'title'          => $stop->title ?? $day->title,
                        'port_id'        => $stop->port_id,
                        'arrival_time'   => $stop->arrival_time?->format('H:i'),
                        'departure_time' => $stop->departure_time?->format('H:i'),
                        'accommodation'  => $stop->accommodation,
                        'description'    => $stop->description ?? $day->description,
                    ];
                }
            }
        }

        $ports = Port::query()->with('translations')->ordered()->get()->map(fn ($p) => [
            'id'    => $p->id,
            'label' => $p->translations->first()?->name ?? $p->slug,
        ])->values();

        return view('tours::admin.itinerary.edit', [
            'tour'           => $tour,
            'languages'      => $languages,
            'languageId'     => $languageId,
            'itinerary'      => $itinerary,
            'stopRows'       => $stopRows,
            'ports'          => $ports,
        ]);
    }

    public function update(StoreTourItineraryRequest $request, Tour $tour): RedirectResponse
    {
        $data       = $request->validated();
        $languageId = (int) $data['language_id'];

        DB::transaction(function () use ($tour, $data, $languageId) {
            /** @var TourItinerary $itinerary */
            $itinerary = $tour->itineraries()->updateOrCreate(
                ['language_id' => $languageId],
                [
                    'origin_port_id' => $data['origin_port_id'] ?? null,
                    'title'          => $data['title']   ?? null,
                    'summary'        => $data['summary'] ?? null,
                ]
            );

            // Delete-recreate: mevcut günler (cascade ile stop'lar) silinir.
            $itinerary->days()->delete();

            // Stop'ları gün numarasına göre grupla
            $rows = $data['stops'] ?? [];
            $byDay = [];
            foreach ($rows as $row) {
                $dayNum = (int) $row['day_number'];
                $byDay[$dayNum][] = $row;
            }
            ksort($byDay);

            foreach ($byDay as $dayNum => $dayRows) {
                // Gün başlığı = ilk stop'un başlığı (yoksa "Gün N")
                $dayTitle = trim((string) ($dayRows[0]['title'] ?? '')) ?: ('Gün ' . $dayNum);

                $day = $itinerary->days()->create([
                    'day_number' => $dayNum,
                    'title'      => $dayTitle,
                    'description'=> $dayRows[0]['description'] ?? null,
                ]);

                foreach (array_values($dayRows) as $i => $row) {
                    $day->stops()->create([
                        'port_id'        => $row['port_id'] ?? null,
                        'point_type'     => $row['point_type'] ?? 'visit',
                        'title'          => $row['title'] ?? null,
                        'accommodation'  => $row['accommodation'] ?? null,
                        'description'    => $row['description'] ?? null,
                        'arrival_time'   => $row['arrival_time']   ?? null,
                        'departure_time' => $row['departure_time'] ?? null,
                        'sort_order'     => $i,
                    ]);
                }
            }
        });

        return redirect()
            ->route('admin.tours.itinerary.edit', ['tour' => $tour, 'lang' => $languageId])
            ->with('success', 'Rota kaydedildi.');
    }
}
