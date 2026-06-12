<?php

declare(strict_types=1);

namespace App\Modules\Tours\Exceptions;

use RuntimeException;

/**
 * Belirli bir tour/date/cabin/passenger kombinasyonu için TourCabinPrice
 * matrix'inde fiyat tanımlanmamış (NULL = "Sorunuz") veya hiç matrix
 * satırı yok.
 *
 * QuoteService bu exception'ı atar; BookingController 422 ile yakalar
 * ve "Bu kombinasyon için lütfen teklif talep edin" mesajı gösterir
 * — sales_status'e bağlı olarak quote form'a yönlendirebilir.
 */
class PriceNotAvailableException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $tourId = null,
        public readonly ?int $tourDateId = null,
        public readonly ?int $cabinId = null,
        public readonly ?string $reason = null,
    ) {
        parent::__construct($message);
    }
}
