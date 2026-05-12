<?php

namespace App\Services\Ai\Exceptions;

use RuntimeException;

/**
 * Thrown by AiQuotaService when a tenant has hit one of its monthly limits.
 *
 * Callers (HTTP controllers, queue jobs) should translate this into a
 * user-facing message: typically a 402 Payment Required response with a
 * link to the plan upgrade page.
 */
class AiQuotaExceededException extends RuntimeException
{
    public const LIMIT_REQUESTS = 'requests';
    public const LIMIT_TOKENS   = 'tokens';
    public const LIMIT_COST     = 'cost';

    public function __construct(
        string $message,
        public readonly ?string $tenantId,
        public readonly string $plan,
        public readonly string $limitType,
        public readonly int|float $used,
        public readonly int|float $limit,
    ) {
        parent::__construct($message);
    }

    public static function requests(?string $tenantId, string $plan, int $used, int $limit): self
    {
        return new self(
            message:   "Aylık AI istek kotası doldu ({$used}/{$limit}). Plan: {$plan}. Yükseltme yapın veya bir sonraki aya kadar bekleyin.",
            tenantId:  $tenantId,
            plan:      $plan,
            limitType: self::LIMIT_REQUESTS,
            used:      $used,
            limit:     $limit,
        );
    }

    public static function tokens(?string $tenantId, string $plan, int $used, int $limit): self
    {
        return new self(
            message:   "Aylık AI token kotası doldu ({$used}/{$limit}). Plan: {$plan}.",
            tenantId:  $tenantId,
            plan:      $plan,
            limitType: self::LIMIT_TOKENS,
            used:      $used,
            limit:     $limit,
        );
    }

    public static function cost(?string $tenantId, string $plan, float $usedUsd, float $limitUsd): self
    {
        return new self(
            message:   sprintf('Aylık AI maliyet limitine ulaşıldı ($%.2f/$%.2f). Plan: %s.', $usedUsd, $limitUsd, $plan),
            tenantId:  $tenantId,
            plan:      $plan,
            limitType: self::LIMIT_COST,
            used:      $usedUsd,
            limit:     $limitUsd,
        );
    }
}
