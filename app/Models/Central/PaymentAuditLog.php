<?php

declare(strict_types=1);

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Central DB append-only payment audit log.
 *
 * Why central?  Because tenant DBs are destroyed when a customer
 * leaves the platform (or is suspended for non-payment), but we still
 * need to defend chargebacks and answer KVKK / tax queries for years
 * afterwards.  Keeping a minimal payment snapshot here preserves the
 * paper trail even if the tenant table is dropped.
 *
 * Never UPDATE rows here — every state change appends a new row tagged
 * with a different `event`.  Indexing on conversation_id + gateway_payment_id
 * lets reconciliation match against gateway statements regardless of
 * which event fired last.
 */
class PaymentAuditLog extends Model
{
    use HasFactory;

    protected $connection = 'central';
    protected $table      = 'payment_audit_log';

    public $timestamps = false;  // only created_at via DB default

    protected $fillable = [
        'tenant_id', 'gateway', 'event',
        'conversation_id', 'gateway_payment_id',
        'payable_type', 'payable_id', 'payable_ref',
        'currency', 'amount', 'status',
        'payload', 'source_ip',
    ];

    protected function casts(): array
    {
        return [
            'payload'    => 'array',
            'amount'     => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Convenience writer — sanitises payload (strips card-like fields)
     * before persisting so a stray paymentCard.cardNumber never sticks
     * around in audit logs.
     */
    public static function record(array $attributes): self
    {
        if (isset($attributes['payload']) && is_array($attributes['payload'])) {
            $attributes['payload'] = self::sanitisePayload($attributes['payload']);
        }

        return self::create($attributes);
    }

    /**
     * Recursively scrub well-known PCI fields out of an array so we
     * never persist raw card data even if the gateway echoes it back.
     */
    private static function sanitisePayload(array $payload): array
    {
        $forbidden = ['cardNumber', 'cardholderName', 'cvc', 'cvv', 'expireMonth', 'expireYear'];

        $walker = function (&$value) use (&$walker, $forbidden) {
            if (! is_array($value)) {
                return;
            }
            foreach ($value as $k => &$v) {
                if (is_string($k) && in_array($k, $forbidden, true)) {
                    $v = '[REDACTED]';
                    continue;
                }
                if (is_array($v)) {
                    $walker($v);
                }
            }
        };

        $walker($payload);

        return $payload;
    }
}
