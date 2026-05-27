<?php

declare(strict_types=1);

namespace App\Modules\Tours\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Tours\Enums\BookingStatus;
use App\Modules\Tours\Exports\BookingManifestExport;
use App\Modules\Tours\Models\Booking;
use App\Modules\Tours\Models\TourDate;
use App\Modules\Tours\Services\Booking\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Admin booking management — list + detail + cancel + manifest export.
 *
 * Bookings are created by the public BookingController in the Tours
 * module; admins read + intervene through this controller.
 */
class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookings)
    {
    }

    public function index(Request $request): View
    {
        $query = Booking::query()
            ->with(['tourDate.tour.translations'])
            ->latest('created_at');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($search = $request->string('q')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_ref', 'like', "%{$search}%");
                if (filter_var($search, FILTER_VALIDATE_EMAIL)) {
                    $q->orWhereJsonContains('customer_snapshot->email', $search);
                }
            });
        }
        if ($from = $request->string('from')->toString()) {
            $query->whereHas('tourDate', fn ($q) => $q->whereDate('starts_at', '>=', $from));
        }
        if ($to = $request->string('to')->toString()) {
            $query->whereHas('tourDate', fn ($q) => $q->whereDate('starts_at', '<=', $to));
        }

        $bookings = $query->paginate(25)->withQueryString();

        return view('tours::admin.bookings.index', [
            'bookings' => $bookings,
            'filters'  => $request->only(['status', 'q', 'from', 'to']),
            'statuses' => BookingStatus::cases(),
        ]);
    }

    public function show(Booking $booking): View
    {
        // Phase 1.5.b: passengers.cabinType → cabin (master Cabin model)
        $booking->load([
            'tourDate.tour.translations',
            'passengers.cabin.category',
            'extras.tourExtra',
            'refunds',
            'member',
        ]);

        return view('tours::admin.bookings.show', compact('booking'));
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $reason = (string) $request->input('reason', '');

        try {
            $this->bookings->cancel($booking, $reason ?: null);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'İptal başarısız: ' . $e->getMessage());
        }

        return back()->with('success', "Rezervasyon iptal edildi. Kapasite serbest bırakıldı.");
    }

    /**
     * Download the passenger manifest for one departure as an Excel file.
     *
     *   /admin/tour-dates/{date}/manifest.xlsx
     *
     * Restricted to date with at least one active booking — empty
     * exports are a needless waste of clicks (admin sees a friendly
     * "no passengers yet" notice instead).
     */
    public function manifest(TourDate $date): BinaryFileResponse|RedirectResponse
    {
        $hasBookings = $date->bookings()
            ->whereIn('status', ['reserved', 'confirmed', 'completed'])
            ->exists();

        if (! $hasBookings) {
            return back()->with('error', 'Bu departure\'da henüz aktif rezervasyon yok.');
        }

        $filename = sprintf(
            'manifest-%s-%s.xlsx',
            $date->starts_at->format('Ymd-Hi'),
            substr(md5((string) $date->id), 0, 6),
        );

        return Excel::download(new BookingManifestExport($date), $filename);
    }
}
