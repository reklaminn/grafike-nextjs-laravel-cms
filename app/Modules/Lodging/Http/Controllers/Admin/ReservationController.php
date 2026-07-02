<?php

declare(strict_types=1);

namespace App\Modules\Lodging\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Lodging\Enums\ReservationStatus;
use App\Modules\Lodging\Models\Reservation;
use App\Modules\Lodging\Models\RoomType;
use App\Modules\Lodging\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

/**
 * Rezervasyon gelen kutusu — liste (filtre), detay, durum değiştir, not,
 * CSV export.  WhatsApp yanıt deep-link'i detay görünümünde üretilir.
 */
class ReservationController extends Controller
{
    public function __construct(private readonly ReservationService $reservations)
    {
    }

    public function index(Request $request): View
    {
        $query = Reservation::query()->with('roomType:id,name')->latest();

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($roomTypeId = $request->integer('room_type')) {
            $query->where('room_type_id', $roomTypeId);
        }
        if ($from = $request->string('from')->toString()) {
            $query->whereDate('checkin', '>=', $from);
        }

        $reservations = $query->paginate(30)->withQueryString();

        return view('lodging::admin.reservations.index', [
            'reservations' => $reservations,
            'roomTypes'    => RoomType::query()->ordered()->get(['id', 'name']),
            'statuses'     => ReservationStatus::cases(),
            'pendingCount' => Reservation::query()->pending()->count(),
            'filters'      => $request->only(['status', 'room_type', 'from']),
        ]);
    }

    public function show(Reservation $reservation): View
    {
        $reservation->load('roomType');

        return view('lodging::admin.reservations.show', [
            'reservation'  => $reservation,
            'whatsappLink' => $this->whatsappLink($reservation),
        ]);
    }

    public function confirm(Reservation $reservation): RedirectResponse
    {
        $this->reservations->confirm($reservation);

        return back()->with('success', 'Rezervasyon onaylandı; tarihler dolu olarak işaretlendi.');
    }

    public function cancel(Reservation $reservation): RedirectResponse
    {
        $this->reservations->cancel($reservation);

        return back()->with('success', 'Rezervasyon iptal edildi; tarihler serbest bırakıldı.');
    }

    public function note(Request $request, Reservation $reservation): RedirectResponse
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:5000']]);

        $reservation->update(['admin_note' => $data['admin_note'] ?? null]);

        return back()->with('success', 'Not kaydedildi.');
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = Reservation::query()->with('roomType:id,name')->latest()->get();

        $filename = 'rezervasyonlar_' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fprintf($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($out, ['Kod', 'Oda Tipi', 'Misafir', 'Telefon', 'E-posta', 'Giriş', 'Çıkış', 'Gece', 'Kişi', 'Tutar', 'Durum', 'Tarih']);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->code,
                    $r->roomType?->name ?? '—',
                    $r->guest_name,
                    $r->guest_phone,
                    $r->guest_email,
                    optional($r->checkin)->format('Y-m-d'),
                    optional($r->checkout)->format('Y-m-d'),
                    $r->nights,
                    $r->adults . '+' . $r->children,
                    $r->est_total,
                    $r->status->label(),
                    $r->created_at?->format('Y-m-d H:i'),
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * wa.me deep link pre-filled with a reply to the guest.
     */
    private function whatsappLink(Reservation $reservation): ?string
    {
        $phone = preg_replace('/\D+/', '', (string) $reservation->guest_phone);
        if (! $phone) {
            return null;
        }

        // Turkish local numbers (0XXXXXXXXXX) → prepend country code 90.
        if (str_starts_with($phone, '0')) {
            $phone = '90' . substr($phone, 1);
        }

        $text = sprintf(
            "Merhaba %s, %s tarihli rezervasyon talebiniz (%s) için yazıyoruz.",
            $reservation->guest_name,
            optional($reservation->checkin)->format('d.m.Y'),
            $reservation->code,
        );

        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($text);
    }
}
