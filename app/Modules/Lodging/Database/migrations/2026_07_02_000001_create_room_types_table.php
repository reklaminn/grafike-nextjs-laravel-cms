<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB — Lodging module.
     *
     * `room_types` — the sellable unit category of a property (e.g. "1+1
     * Suit", "3+1 Deluxe").  Availability is tracked at the *unit_count*
     * (adet) level, not per physical room — matches the MVP scope
     * (docs/rezervasyon-modulu-proje-dosyasi.md §3.1).
     *
     * Single-language MVP: name/summary/description live directly on the
     * row.  A `room_type_translations` table can be added later following
     * the Tours module's `*_translations` pattern without touching this.
     */
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();

            // Slug is global within the tenant DB; used by the public API
            // (?room_type=1-1-suit) and the frontend block.
            $table->string('slug', 200)->unique();

            $table->string('name', 255);
            $table->string('summary', 500)->nullable();
            $table->longText('description')->nullable();

            // Occupancy bounds shown on the card and used for guest-count
            // validation on the request form.
            $table->unsignedSmallInteger('capacity_min')->default(1);
            $table->unsignedSmallInteger('capacity_max')->default(2);

            $table->unsignedSmallInteger('size_m2')->nullable();
            $table->unsignedSmallInteger('bedrooms')->default(1);
            $table->unsignedSmallInteger('bathrooms')->default(1);

            // Nightly representative price.  Decimal (major units) per spec
            // §3.1 — simpler for site seeders than integer minor units.
            $table->decimal('base_price', 10, 2)->default(0);
            $table->string('currency', 3)->default('TRY');

            // Inventory count — how many physical units of this type exist.
            // A date is "dolu" for this type when Σ(booked+blocked qty)
            // reaches unit_count (AvailabilityService).
            $table->unsignedSmallInteger('unit_count')->default(1);

            // ["Wi-Fi", "Mutfak", "Balkon", …]
            $table->json('amenities')->nullable();

            // Seeder-provided image paths (tenant-asset URLs).  Admin uploads
            // use the Spatie `gallery` collection instead; the API merges
            // both (Spatie first, then this).
            $table->json('images')->nullable();

            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
