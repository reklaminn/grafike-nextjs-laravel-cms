<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Voucher issued for a confirmed Booking.  The PDF file itself lives in
 * Spatie media-library under the 'voucher_pdf' single-file collection;
 * `pdf_url` is a cached download URL for embedding into the
 * confirmation email without an extra join.
 *
 * Lifecycle (`status`):
 *   draft    — created but not yet PDF-rendered
 *   issued   — PDF generated, sent to customer
 *   redeemed — operator scanned the code at check-in
 *   voided   — voided by admin (e.g. booking cancelled)
 */
class Voucher extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'booking_id', 'code', 'status',
        'issued_at', 'redeemed_at', 'voided_at',
        'pdf_url', 'pdf_hash', 'redeemed_by_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'issued_at'   => 'datetime',
            'redeemed_at' => 'datetime',
            'voided_at'   => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('voucher_pdf')
            ->singleFile()
            ->acceptsMimeTypes(['application/pdf']);
    }

    /**
     * Generate a short, scan-friendly voucher code.  Caller must verify
     * uniqueness against the `vouchers.code` unique index (collision rate
     * is ~1 in 33M; in practice the loop is never hit twice).
     */
    public static function generateCode(): string
    {
        return 'V-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
    }
}
