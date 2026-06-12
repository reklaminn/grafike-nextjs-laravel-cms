<?php

declare(strict_types=1);

namespace App\Modules\Payments\Exceptions;

/**
 * Tenant has not configured Iyzico API keys yet (Tenant.data.iyzico_keys
 * is empty or missing for the requested gateway).
 *
 * Controllers translate this into a friendly admin-facing message:
 * "Bu site henüz Iyzico API anahtarlarını girmemiş.
 *  Lütfen Yönetim → Tenant → Ödeme Ayarları'ndan ekleyin."
 */
class MissingBYOKKeysException extends PaymentGatewayException
{
}
