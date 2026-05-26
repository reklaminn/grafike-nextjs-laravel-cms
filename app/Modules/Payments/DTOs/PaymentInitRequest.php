<?php

declare(strict_types=1);

namespace App\Modules\Payments\DTOs;

/**
 * Input to PaymentGateway::initialize3DS().
 *
 * The DTO is gateway-agnostic — IyzicoGateway maps this into the
 * specific Iyzico request shape; a future StripeGateway would map it
 * into Stripe's PaymentIntent shape, etc.
 *
 * Money is in minor units (kuruş) so the gateway never has to deal
 * with floating point.
 */
final class PaymentInitRequest
{
    /**
     * @param string                 $payableType      Polymorphic — e.g. Booking::class
     * @param int|string             $payableId        ID of the payable
     * @param string                 $tenantId         Tenant slug (audit log)
     * @param int                    $amount           Minor units
     * @param string                 $currency         ISO-4217 (TRY, EUR, USD)
     * @param array<string, mixed>   $card             Card data (number, holder, expireMonth, expireYear, cvc)
     * @param array<string, mixed>   $buyer            Buyer (id, name, surname, email, gsmNumber, identityNumber, …)
     * @param array<string, mixed>   $billingAddress   Billing address (contactName, city, country, address, zipCode)
     * @param array<string, mixed>   $shippingAddress  Shipping address (same shape as billingAddress)
     * @param array<int, array<string, mixed>> $items  Cart items (id, name, category1, itemType, price)
     * @param string                 $callbackUrl      Where gateway redirects after 3DS
     * @param string                 $conversationId   Idempotency / correlation key
     * @param string|null            $installment      Number of instalments (Iyzico: 1, 2, 3, 6, 9, 12)
     * @param string|null            $locale           tr | en (gateway UI language)
     */
    public function __construct(
        public readonly string $payableType,
        public readonly int|string $payableId,
        public readonly string $tenantId,
        public readonly int $amount,
        public readonly string $currency,
        public readonly array $card,
        public readonly array $buyer,
        public readonly array $billingAddress,
        public readonly array $shippingAddress,
        public readonly array $items,
        public readonly string $callbackUrl,
        public readonly string $conversationId,
        public readonly ?string $installment = null,
        public readonly ?string $locale = 'tr',
    ) {
    }
}
