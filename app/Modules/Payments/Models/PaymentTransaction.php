<?php

declare(strict_types=1);

namespace App\Modules\Payments\Models;

use App\Modules\Payments\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One attempt to charge a customer.
 *
 * Polymorphic — the same table serves Tours bookings (payable_type =
 * App\Modules\Tours\Models\Booking::class) and any future Commerce
 * order (App\Modules\Commerce\Models\Order::class).  The payable
 * resolves via Eloquent's morphTo so callers can do
 * `$txn->payable->status` without knowing which module owns it.
 */
class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payable_type', 'payable_id',
        'gateway', 'conversation_id',
        'gateway_payment_id', 'gateway_payment_txn_id',
        'status', 'currency', 'amount',
        'amount_captured', 'amount_refunded',
        'three_ds_mode', 'fraud_status', 'sandbox',
        'error_code', 'error_message', 'raw_response',
        'initialized_at', 'captured_at', 'failed_at',
    ];

    protected function casts(): array
    {
        return [
            'status'           => PaymentStatus::class,
            'amount'           => 'integer',
            'amount_captured'  => 'integer',
            'amount_refunded'  => 'integer',
            'sandbox'          => 'boolean',
            'raw_response'     => 'array',
            'initialized_at'   => 'datetime',
            'captured_at'      => 'datetime',
            'failed_at'        => 'datetime',
        ];
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(PaymentRefund::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeCaptured(Builder $q): Builder
    {
        return $q->where('status', PaymentStatus::Captured->value);
    }

    public function scopeForGateway(Builder $q, string $gateway): Builder
    {
        return $q->where('gateway', $gateway);
    }

    public function scopeForPayable(Builder $q, string $type, int|string $id): Builder
    {
        return $q->where('payable_type', $type)->where('payable_id', $id);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isFullyRefunded(): bool
    {
        return $this->amount_refunded >= $this->amount_captured && $this->amount_captured > 0;
    }

    public function refundableAmount(): int
    {
        return max(0, $this->amount_captured - $this->amount_refunded);
    }
}
