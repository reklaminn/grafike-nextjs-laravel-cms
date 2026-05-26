<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models;

use App\Modules\Tours\Enums\RefundStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Refund record against a Booking.  The lifecycle is owned by
 * RefundService (Phase 2) which talks to IyzicoGateway and processes
 * webhook callbacks.  Multiple partial refunds against the same
 * booking are allowed (e.g. one extra refunded, then full cancellation).
 */
class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'status',
        'amount_requested', 'amount_approved', 'amount_refunded',
        'reason_code', 'reason_text',
        'gateway_request_id', 'gateway_response_code', 'gateway_payload',
        'requested_by_admin_id', 'processed_by_admin_id',
        'requested_at', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'status'           => RefundStatus::class,
            'amount_requested' => 'integer',
            'amount_approved'  => 'integer',
            'amount_refunded'  => 'integer',
            'gateway_payload'  => 'array',
            'requested_at'     => 'datetime',
            'processed_at'     => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function isCustomerInitiated(): bool
    {
        return $this->requested_by_admin_id === null;
    }
}
