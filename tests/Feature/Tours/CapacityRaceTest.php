<?php

declare(strict_types=1);

namespace Tests\Feature\Tours;

use App\Modules\Tours\Exceptions\CapacityExhaustedException;
use App\Modules\Tours\Services\Booking\CapacityLockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Concurrency stress test for CapacityLockService.
 *
 * Note: this test uses a single throw-away SQLite in-memory connection
 * + a hand-crafted `tour_dates` table because a tenant-aware setup
 * adds significant boilerplate that does not change what's under test
 * — the lockForUpdate transaction semantics in CapacityLockService.
 *
 * Real-world race verification (two HTTP requests hitting MariaDB
 * simultaneously) is part of the Phase 2C smoke test on staging and
 * documented in docs/tours-module-plan.md §2 / Phase 2 Verification 1.
 *
 * SKIP CONDITION:
 *   When the test connection is SQLite this only verifies SEQUENTIAL
 *   correctness because SQLite serialises writes.  The actual race
 *   would require parallel processes against MariaDB; this Feature
 *   test asserts the deterministic side: "first call wins, second
 *   throws CapacityExhaustedException".
 */
class CapacityRaceTest extends TestCase
{
    // NOTE: deliberately no RefreshDatabase — we hand-roll just the
    // `tour_dates` schema we need against the default (test SQLite)
    // connection so the test runs without the full Phase 0 / 1
    // migration chain (which targets the central MariaDB connection
    // configured for Docker, not locally available).

    private CapacityLockService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Use the default connection (SQLite in tests, configured by
        // phpunit.xml) — drop+recreate so a previous test failure
        // doesn't leave a stale table.
        Schema::dropIfExists('tour_dates');
        Schema::create('tour_dates', function ($t): void {
            $t->id();
            $t->unsignedBigInteger('tour_id')->default(0);
            $t->dateTime('starts_at')->nullable();
            $t->dateTime('ends_at')->nullable();
            $t->unsignedInteger('capacity_total')->default(0);
            $t->unsignedInteger('capacity_left')->default(0);
            $t->unsignedInteger('price_override')->nullable();
            $t->string('status', 30)->default('open');
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        $this->service = app(CapacityLockService::class);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('tour_dates');
        parent::tearDown();
    }

    public function test_reserve_decrements_capacity(): void
    {
        $id = DB::table('tour_dates')->insertGetId([
            'capacity_total' => 5,
            'capacity_left'  => 5,
            'status'         => 'open',
        ]);

        $date = $this->service->reserve($id, 2);

        $this->assertSame(3, (int) $date->capacity_left);
        $this->assertSame('open', (string) $date->status);
    }

    public function test_reserve_marks_sold_out_when_capacity_hits_zero(): void
    {
        $id = DB::table('tour_dates')->insertGetId([
            'capacity_total' => 1,
            'capacity_left'  => 1,
            'status'         => 'open',
        ]);

        $date = $this->service->reserve($id, 1);

        $this->assertSame(0, (int) $date->capacity_left);
        $this->assertSame('sold_out', (string) $date->status);
    }

    public function test_sequential_reserve_on_last_seat_throws_on_second_call(): void
    {
        $id = DB::table('tour_dates')->insertGetId([
            'capacity_total' => 1,
            'capacity_left'  => 1,
            'status'         => 'open',
        ]);

        // First call wins.
        $this->service->reserve($id, 1);

        // Second call must fail with our typed exception (this is the
        // exact UX the BookingController exposes as 409 Conflict).
        $this->expectException(CapacityExhaustedException::class);
        $this->service->reserve($id, 1);
    }

    public function test_release_replenishes_capacity_and_reopens(): void
    {
        $id = DB::table('tour_dates')->insertGetId([
            'capacity_total' => 2,
            'capacity_left'  => 0,
            'status'         => 'sold_out',
        ]);

        $date = $this->service->release($id, 2);

        $this->assertSame(2, (int) $date->capacity_left);
        $this->assertSame('open', (string) $date->status);
    }

    public function test_release_caps_at_capacity_total(): void
    {
        $id = DB::table('tour_dates')->insertGetId([
            'capacity_total' => 3,
            'capacity_left'  => 2,
            'status'         => 'open',
        ]);

        // Defensive double-release shouldn't push above the ceiling.
        $date = $this->service->release($id, 10);

        $this->assertSame(3, (int) $date->capacity_left);
    }
}
