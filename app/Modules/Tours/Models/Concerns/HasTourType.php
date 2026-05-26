<?php

declare(strict_types=1);

namespace App\Modules\Tours\Models\Concerns;

use App\Modules\Tours\Enums\TourType;

/**
 * Adds type-discriminator local scopes to Tour-like models.
 *
 * Usage:
 *   Tour::cruise()->published()->get()
 *   Tour::package()->whereHas('dates', fn ($q) => $q->whereDate('starts_at', '>=', now()))
 */
trait HasTourType
{
    /** @phpstan-ignore-next-line — Eloquent scope */
    public function scopeOfType($query, TourType|string $type)
    {
        $value = $type instanceof TourType ? $type->value : strtolower(trim($type));

        return $query->where('type', $value);
    }

    /** @phpstan-ignore-next-line */
    public function scopeCruise($query)
    {
        return $query->where('type', TourType::Cruise->value);
    }

    /** @phpstan-ignore-next-line */
    public function scopePackage($query)
    {
        return $query->where('type', TourType::Package->value);
    }

    /** @phpstan-ignore-next-line */
    public function scopeDaily($query)
    {
        return $query->where('type', TourType::Daily->value);
    }

    public function isCruise(): bool
    {
        return $this->matchesType(TourType::Cruise);
    }

    public function isPackage(): bool
    {
        return $this->matchesType(TourType::Package);
    }

    public function isDaily(): bool
    {
        return $this->matchesType(TourType::Daily);
    }

    /**
     * Normalised comparison: handles both the casted enum (after a
     * proper retrieve / save cycle) and the raw string (when a model
     * is hydrated via forceFill / setAttribute before the cast kicks in).
     */
    private function matchesType(TourType $target): bool
    {
        $current = $this->getAttribute('type');

        if ($current instanceof TourType) {
            return $current === $target;
        }

        return is_string($current) && strtolower($current) === $target->value;
    }
}
