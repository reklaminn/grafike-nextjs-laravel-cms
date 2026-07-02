<?php

declare(strict_types=1);

namespace Tests\Feature\Lodging;

use App\Modules\Lodging\Enums\ReservationStatus;
use App\Modules\Lodging\Models\LodgingSetting;
use App\Modules\Lodging\Models\Reservation;
use App\Modules\Lodging\Models\RoomType;
use App\Modules\Lodging\Services\AvailabilityService;
use App\Modules\Lodging\Services\PricingService;
use App\Modules\Lodging\Services\ReservationService;
use App\Services\Modules\ModuleRegistry;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Lodging module engine test.
 *
 * Like the Tours CapacityRaceTest, this hand-rolls the module's tables on
 * the default (test SQLite) connection instead of running the full tenant
 * migration chain — the module tables carry no tenant_id and no hardcoded
 * tenant, so exercising them on a plain connection also demonstrates the
 * "works on any tenant, zero code change" property.
 */
class ReservationEngineTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        // Run the module migrations (in filename order) on the default conn.
        foreach ($this->migrationFiles() as $file) {
            (require $file)->up();
        }
    }

    protected function tearDown(): void
    {
        foreach (['room_availabilities', 'reservations', 'room_types', 'lodging_settings'] as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    /** @return array<int, string> */
    private function migrationFiles(): array
    {
        $dir = app_path('Modules/Lodging/Database/migrations');
        $files = glob($dir . '/*.php') ?: [];
        sort($files);

        return $files;
    }

    private function roomType(array $overrides = []): RoomType
    {
        return RoomType::create(array_merge([
            'slug'         => 'test-suit',
            'name'         => 'Test Suit',
            'capacity_min' => 2,
            'capacity_max' => 3,
            'base_price'   => 2500,
            'currency'     => 'TRY',
            'unit_count'   => 1,
            'is_active'    => true,
        ], $overrides));
    }

    public function test_module_is_registered_with_migration_path(): void
    {
        $registry = app(ModuleRegistry::class);

        $this->assertTrue($registry->exists('lodging'));
        $this->assertSame('app/Modules/Lodging/Database/migrations', $registry->migrationsPath('lodging')
            ? str_replace(base_path() . '/', '', $registry->migrationsPath('lodging'))
            : null);
    }

    public function test_pricing_computes_nights_and_total(): void
    {
        $room = $this->roomType(['base_price' => 1000]);
        $pricing = app(PricingService::class);

        $this->assertSame(3, $pricing->nights('2026-08-10', '2026-08-13'));

        $estimate = $pricing->estimate($room, '2026-08-10', '2026-08-13');
        $this->assertSame(3, $estimate['nights']);
        $this->assertEqualsWithDelta(3000.0, $estimate['total'], 0.001);
    }

    public function test_create_request_generates_code_and_estimate(): void
    {
        LodgingSetting::current()->update(['reservation_code_prefix' => 'VS']);

        $room = $this->roomType(['base_price' => 2000]);

        $reservation = app(ReservationService::class)->createRequest([
            'room_type_id' => $room->id,
            'guest_name'   => 'Ali Veli',
            'guest_phone'  => '05551112233',
            'checkin'      => '2026-09-01',
            'checkout'     => '2026-09-04',
            'adults'       => 2,
        ]);

        $this->assertStringStartsWith('VS-', $reservation->code);
        $this->assertSame(3, $reservation->nights);
        $this->assertEqualsWithDelta(6000.0, (float) $reservation->est_total, 0.001);
        $this->assertSame(ReservationStatus::Pending, $reservation->status);
    }

    public function test_reservation_mail_renders(): void
    {
        $room = $this->roomType();

        $reservation = app(ReservationService::class)->createRequest([
            'room_type_id' => $room->id,
            'guest_name'   => 'Render Test',
            'guest_phone'  => '05551112233',
            'checkin'      => '2026-09-01',
            'checkout'     => '2026-09-03',
        ]);

        $html = (new \App\Modules\Lodging\Mail\ReservationRequestMail($reservation, $room->name))->render();

        $this->assertStringContainsString($reservation->code, $html);
        $this->assertStringContainsString('Render Test', $html);
    }

    public function test_confirm_blocks_dates_and_cancel_frees_them(): void
    {
        $room = $this->roomType(['unit_count' => 1]);
        $service = app(ReservationService::class);
        $availability = app(AvailabilityService::class);

        $reservation = $service->createRequest([
            'room_type_id' => $room->id,
            'guest_name'   => 'Ayşe',
            'guest_phone'  => '05559998877',
            'checkin'      => '2026-10-10',
            'checkout'     => '2026-10-13',
        ]);

        // Pending → still available.
        $this->assertTrue($availability->isRangeAvailable($room, '2026-10-10', '2026-10-13'));

        $service->confirm($reservation);

        // Nights 10,11,12 booked (checkout-exclusive); 13 is free again.
        $booked = $availability->bookedDates($room, '2026-10-01', '2026-10-31');
        $this->assertSame(['2026-10-10', '2026-10-11', '2026-10-12'], $booked);
        $this->assertFalse($availability->isRangeAvailable($room, '2026-10-11', '2026-10-12'));
        $this->assertTrue($availability->isRangeAvailable($room, '2026-10-13', '2026-10-15'));

        // Cancel frees the dates.
        $service->cancel($reservation->fresh());
        $this->assertSame([], $availability->bookedDates($room, '2026-10-01', '2026-10-31'));
    }

    public function test_multi_unit_room_only_full_when_all_units_consumed(): void
    {
        $room = $this->roomType(['slug' => 'deluxe', 'unit_count' => 2]);
        $service = app(ReservationService::class);
        $availability = app(AvailabilityService::class);

        // One confirmed reservation on a 2-unit room → still 1 free.
        $r1 = $service->createRequest([
            'room_type_id' => $room->id,
            'guest_name'   => 'Guest 1',
            'guest_phone'  => '0555',
            'checkin'      => '2026-11-05',
            'checkout'     => '2026-11-07',
        ]);
        $service->confirm($r1);

        $this->assertSame([], $availability->bookedDates($room, '2026-11-01', '2026-11-30'));
        $this->assertTrue($availability->isRangeAvailable($room, '2026-11-05', '2026-11-07'));

        // Second reservation consumes the last unit → now full those nights.
        $r2 = $service->createRequest([
            'room_type_id' => $room->id,
            'guest_name'   => 'Guest 2',
            'guest_phone'  => '0556',
            'checkin'      => '2026-11-05',
            'checkout'     => '2026-11-07',
        ]);
        $service->confirm($r2);

        $this->assertSame(['2026-11-05', '2026-11-06'], $availability->bookedDates($room, '2026-11-01', '2026-11-30'));
    }
}
