<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Tours;

use App\Modules\Tours\Enums\BookingStatus;
use App\Modules\Tours\StateMachines\BookingStateMachine;
use PHPUnit\Framework\TestCase;

/**
 * Verifies the transition table in BookingStateMachine matches the
 * state diagram in the class docblock.  Pure unit test (no DB).
 *
 * canTransition() is the only method we touch — transition() needs
 * a real Booking model and lands in a Feature test.
 */
class BookingStateMachineTest extends TestCase
{
    private BookingStateMachine $sm;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sm = new BookingStateMachine();
    }

    /**
     * @dataProvider validTransitions
     */
    public function test_valid_transitions_are_allowed(BookingStatus $from, BookingStatus $to): void
    {
        $this->assertTrue(
            $this->sm->canTransition($from, $to),
            "Expected {$from->value} → {$to->value} to be allowed",
        );
    }

    /**
     * @dataProvider invalidTransitions
     */
    public function test_invalid_transitions_are_rejected(BookingStatus $from, BookingStatus $to): void
    {
        $this->assertFalse(
            $this->sm->canTransition($from, $to),
            "Expected {$from->value} → {$to->value} to be rejected",
        );
    }

    public static function validTransitions(): array
    {
        return [
            // pending →
            'pending → reserved'      => [BookingStatus::Pending,   BookingStatus::Reserved],
            'pending → cancelled'     => [BookingStatus::Pending,   BookingStatus::Cancelled],

            // reserved →
            'reserved → confirmed'    => [BookingStatus::Reserved,  BookingStatus::Confirmed],
            'reserved → expired'      => [BookingStatus::Reserved,  BookingStatus::Expired],
            'reserved → cancelled'    => [BookingStatus::Reserved,  BookingStatus::Cancelled],

            // confirmed →
            'confirmed → completed'   => [BookingStatus::Confirmed, BookingStatus::Completed],
            'confirmed → cancelled'   => [BookingStatus::Confirmed, BookingStatus::Cancelled],
            'confirmed → refunded'    => [BookingStatus::Confirmed, BookingStatus::Refunded],

            // completed → refunded (post-trip refund window)
            'completed → refunded'    => [BookingStatus::Completed, BookingStatus::Refunded],
        ];
    }

    public static function invalidTransitions(): array
    {
        return [
            // pending cannot skip the queue
            'pending → confirmed'     => [BookingStatus::Pending,    BookingStatus::Confirmed],
            'pending → completed'     => [BookingStatus::Pending,    BookingStatus::Completed],
            'pending → refunded'      => [BookingStatus::Pending,    BookingStatus::Refunded],
            'pending → expired'       => [BookingStatus::Pending,    BookingStatus::Expired],

            // reserved cannot complete without paying
            'reserved → completed'    => [BookingStatus::Reserved,   BookingStatus::Completed],
            'reserved → refunded'     => [BookingStatus::Reserved,   BookingStatus::Refunded],

            // confirmed cannot regress to reserved / pending
            'confirmed → pending'     => [BookingStatus::Confirmed,  BookingStatus::Pending],
            'confirmed → reserved'    => [BookingStatus::Confirmed,  BookingStatus::Reserved],
            'confirmed → expired'     => [BookingStatus::Confirmed,  BookingStatus::Expired],

            // terminal states are dead-ends
            'cancelled → reserved'    => [BookingStatus::Cancelled,  BookingStatus::Reserved],
            'cancelled → confirmed'   => [BookingStatus::Cancelled,  BookingStatus::Confirmed],
            'refunded → confirmed'    => [BookingStatus::Refunded,   BookingStatus::Confirmed],
            'expired → reserved'      => [BookingStatus::Expired,    BookingStatus::Reserved],
            'expired → confirmed'     => [BookingStatus::Expired,    BookingStatus::Confirmed],
        ];
    }
}
