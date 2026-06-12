<?php

declare(strict_types=1);

namespace App\Modules\Tours\Exports;

use App\Modules\Tours\Models\BookingPassenger;
use App\Modules\Tours\Models\TourDate;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Passenger manifest export — XLSX with one row per passenger on a
 * given departure.  Used by operators at check-in and submitted to
 * port authorities for cruises.
 *
 * Operators run this from the booking-detail page (single departure)
 * or from the departure-detail page (all bookings on that departure).
 */
class BookingManifestExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    public function __construct(public readonly TourDate $tourDate)
    {
    }

    public function collection(): \Illuminate\Support\Collection
    {
        // Phase 1.5.b: cabinType / priceTier relation'ları kaldırıldı.
        // Yeni: cabin (Cabin master + category translation).
        return BookingPassenger::query()
            ->whereHas('booking', function ($q) {
                $q->where('tour_date_id', $this->tourDate->id)
                  ->whereIn('status', ['reserved', 'confirmed', 'completed']);
            })
            ->with(['booking', 'cabin.category.translations', 'cabin.translations'])
            ->orderBy('booking_id')
            ->orderByDesc('is_lead')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Booking Ref', 'Status', 'Tip',
            'Ad', 'Soyad', 'Cinsiyet', 'Doğum',
            'Kimlik Tipi', 'TCKN/Passport', 'Uyruk',
            'Kabin', 'Fiyat (kuruş)', 'Lead', 'Not',
        ];
    }

    public function map($passenger): array
    {
        // Cabin display: prefer specific cabin name → fallback to category
        // (e.g. "Junior Suite Deck 7" or just "Suite")
        $cabinName = $passenger->cabin?->translations->first()?->name
            ?? $passenger->cabin?->category?->translations?->first()?->name
            ?? '—';

        return [
            $passenger->booking?->booking_ref,
            $passenger->booking?->status?->value,
            $passenger->passenger_type?->value,
            $passenger->first_name,
            $passenger->last_name,
            $passenger->gender,
            $passenger->date_of_birth?->format('Y-m-d'),
            $passenger->id_type,
            $passenger->id_number,
            $passenger->nationality,
            $cabinName,
            $passenger->price,
            $passenger->is_lead ? 'Lead' : '',
            $passenger->notes,
        ];
    }

    public function title(): string
    {
        $when = $this->tourDate->starts_at?->format('Y-m-d') ?? 'unknown-date';

        return "Manifest-{$when}";
    }
}
