<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB — Tours module
     *
     * Master `tours` table — polymorphic across cruise / package / daily
     * (see docs/tours-module-plan.md §1.D1).  Type-specific structured
     * data lives in:
     *   • `tours.type_config` JSON  — small / unstructured config
     *   • `tour_cabin_types` table   — cruise cabin definitions
     *   • `tour_itineraries` table   — day-by-day rota (all types)
     */
    public function up(): void
    {
        Schema::create('tours', function (Blueprint $table) {
            $table->id();

            // Polymorphic discriminator. Enforced at app level (see
            // App\Modules\Tours\Enums\TourType + casts on Tour model).
            // String column rather than ENUM so adding a new sub-type
            // later does not require a migration.
            $table->string('type', 20)->index();

            // Slug is global within the tenant DB; URLs are language-
            // agnostic per tenant policy (matches Page convention).
            $table->string('slug', 200)->unique();

            // Optional category link; categories themselves are
            // hierarchical, so we only store the leaf-most.
            $table->unsignedBigInteger('tour_category_id')->nullable();

            // Lifecycle state. `draft` for in-progress edits, `published`
            // for visible-on-frontend, `archived` for hidden but kept.
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');

            // Pricing baseline.  Real price per booking is computed by
            // QuoteService = base_price + tier modifiers + extras +
            // optional date-override (see tour_dates.price_override).
            $table->string('currency', 3)->default('TRY');
            $table->unsignedInteger('base_price')->default(0); // stored in minor units (kuruş)

            // Capacity baseline — overridable per departure date.
            // Cruise tours typically leave this 0 and rely on the sum
            // of cabin_type capacities instead.
            $table->unsignedInteger('capacity_default')->default(0);

            // Type-specific config (key/value pairs that don't justify
            // their own column). Examples:
            //   cruise:  { ship_name, departure_port, return_port }
            //   package: { transport_type, accommodation_type, group_size_max }
            //   daily:   { meeting_point, time_slots, pickup_radius_km }
            $table->json('type_config')->nullable();

            // Denormalised full-text search bucket. Filled by Tour::saving()
            // observer with title + description + destination words from
            // every active translation. Cheaper than joining tour_translations
            // on every list query.
            $table->text('search_index')->nullable();

            // Optional sort priority on listings (lower = first).
            $table->integer('sort_order')->default(0);

            // Mark as "öne çıkan" (featured) for homepage highlights.
            $table->boolean('is_featured')->default(false);

            // Pre-built JSON-LD or schema hints. Frontend can override.
            $table->json('structured_data_json')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tour_category_id')
                ->references('id')->on('tour_categories')
                ->nullOnDelete();

            $table->index(['type', 'status']);
            $table->index(['status', 'is_featured']);
            $table->fullText('search_index');
        });

        Schema::create('tour_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('language_id'); // central DB ref

            // Display fields
            $table->string('title', 500);
            $table->string('subtitle', 500)->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->longText('highlights')->nullable();          // markdown / html
            $table->longText('important_info')->nullable();      // disclaimers, age limits

            // SEO
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_image_url', 1000)->nullable();

            $table->timestamps();

            $table->unique(['tour_id', 'language_id'], 'uniq_tour_language');
            $table->index('language_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_translations');
        Schema::dropIfExists('tours');
    }
};
