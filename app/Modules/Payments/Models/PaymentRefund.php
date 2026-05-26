<?php

declare(strict_types=1);

namespace App\Modules\Payments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One refund attempt against a PaymentTransaction.
 *
 * Multiple refunds against the same transaction are allowed (a partial
 * refund of one extra today, the rest tomorrow on full cancellation).
 * The transaction's `amount_refunded` is the running total derived
 * from the sum of `amount_refunded` here.
 */
class PaymentRefund extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_transaction_id',
        'conversation_id', 'gateway_refund_id',
        'status', 'currency',
        'amount_requested', 'amount_refunded',
        'reason_code', 'reason_text',
        'error_code', 'error_message', 'raw_response',
        'requested_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_requested' => 'integer',
            'amount_refunded'  => 'integer',
            'raw_response'     => 'array',
            'requested_at'     => 'datetime',
            'completed_at'     => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }
}
