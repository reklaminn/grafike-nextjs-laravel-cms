<?php

declare(strict_types=1);

namespace App\Modules\Tours\Enums;

/**
 * Discriminator for the `tours.type` column.
 *
 * All three types share the same parent table — see the rationale in
 * docs/tours-module-plan.md §1.D1 (polymorphic single-table over STI).
 * Type-specific fields live in `tours.type_config` JSON and, where they
 * need real relations (cabin types on cruises, time slots on dailies),
 * in dedicated sub-tables that FK back to `tours.id`.
 *
 *   cruise  — Multi-day boat with cabin selection + per-port itinerary.
 *             Owns the `tour_cabin_types` table.
 *   package — Multi-day land tour with fixed departure dates and a
 *             day-by-day itinerary (e.g. 7-day Cappadocia + Pamukkale).
 *   daily   — Single-day excursion with intra-day time slots and a
 *             pickup point (e.g. half-day Bosphorus cruise).
 */
enum TourType: string
{
    case Cruise  = 'cruise';
    case Package = 'package';
    case Daily   = 'daily';

    public function label(): string
    {
        return match ($this) {
            self::Cruise  => 'Cruise (Gemi Turu)',
            self::Package => 'Paket Tur (Çoklu Gün)',
            self::Daily   => 'Günlük Tur',
        };
    }

    /**
     * Does this type model cabins (i.e. discrete capacity buckets that
     * passengers are assigned to)?  Currently only cruises do.
     */
    public function hasCabins(): bool
    {
        return $this === self::Cruise;
    }

    /**
     * Does this type span more than one calendar day per departure?
     */
    public function isMultiDay(): bool
    {
        return $this !== self::Daily;
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $c) => $c->value, self::cases());
    }
}
