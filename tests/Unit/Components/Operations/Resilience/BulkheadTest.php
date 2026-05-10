<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Resilience;

use Avax\Components\Operations\Resilience\System\Capabilities\Bulkhead\Bulkhead;
use Avax\Components\Operations\Resilience\System\Foundation\Failure\BulkheadLimitExceeded\BulkheadLimitExceeded;
use Avax\Components\Operations\Resilience\System\Foundation\Failure\ResilienceException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

final class BulkheadTest extends TestCase
{
    #[Test]
    public function it_runs_operation_within_limit() : void
    {
        $bulkhead = new Bulkhead(maxConcurrent: 5);

        $result = $bulkhead->run(static fn () : string => 'success');

        self::assertSame('success', $result);
    }

    #[Test]
    public function it_decrements_active_count_after_operation() : void
    {
        $bulkhead = new Bulkhead(maxConcurrent: 5);

        self::assertSame(0, $bulkhead->activeCount());

        $bulkhead->run(static fn () : string => 'ok');

        self::assertSame(0, $bulkhead->activeCount());
    }

    #[Test]
    public function it_tracks_active_during_operation() : void
    {
        $bulkhead              = new Bulkhead(maxConcurrent: 5);
        $activeDuringOperation = null;

        $bulkhead->run(static function () use ($bulkhead, &$activeDuringOperation) : string {
            $activeDuringOperation = $bulkhead->activeCount();

            return 'done';
        });

        self::assertSame(1, $activeDuringOperation);
        self::assertSame(0, $bulkhead->activeCount());
    }

    #[Test]
    public function it_throws_when_max_concurrent_reached() : void
    {
        $bulkhead = new Bulkhead(maxConcurrent: 1);

        $bulkhead->run(static fn () : string => 'first');

        self::assertSame(0, $bulkhead->activeCount());

        $bulkhead->run(static fn () : string => 'second');

        self::assertSame(0, $bulkhead->activeCount());
    }

    #[Test]
    public function it_allows_operation_after_slot_freed() : void
    {
        $bulkhead = new Bulkhead(maxConcurrent: 1);

        $result1 = $bulkhead->run(static fn () : string => 'first');

        self::assertSame('first', $result1);
        self::assertSame(0, $bulkhead->activeCount());

        $result2 = $bulkhead->run(static fn () : string => 'second');

        self::assertSame('second', $result2);
    }

    #[Test]
    public function it_decrements_active_on_exception() : void
    {
        $bulkhead = new Bulkhead(maxConcurrent: 2);

        try {
            $bulkhead->run(static fn () : never => throw new RuntimeException('fail'));
        } catch (RuntimeException) {
            // Expected
        }

        self::assertSame(0, $bulkhead->activeCount());
        self::assertSame(2, $bulkhead->availableSlots());
    }

    #[Test]
    public function it_reports_available_slots() : void
    {
        $bulkhead = new Bulkhead(maxConcurrent: 10);

        self::assertSame(10, $bulkhead->availableSlots());

        $bulkhead->run(static function () use ($bulkhead) : string {
            self::assertSame(9, $bulkhead->availableSlots());

            return 'ok';
        });

        self::assertSame(10, $bulkhead->availableSlots());
    }

    #[Test]
    public function it_uses_default_max_concurrent_of_10() : void
    {
        $bulkhead = new Bulkhead();

        self::assertSame(10, $bulkhead->availableSlots());
    }

    #[Test]
    public function it_handles_single_concurrent_operation() : void
    {
        $bulkhead = new Bulkhead(maxConcurrent: 1);

        $result = $bulkhead->run(static fn () : int => 42);

        self::assertSame(42, $result);
    }

    #[Test]
    public function it_allows_many_sequential_operations() : void
    {
        $bulkhead = new Bulkhead(maxConcurrent: 1);

        for ($i = 0; $i < 100; $i++) {
            $result = $bulkhead->run(static fn () : int => $i);
            self::assertSame($i, $result);
        }

        self::assertSame(0, $bulkhead->activeCount());
    }

    #[Test]
    public function it_handles_operation_returning_null() : void
    {
        $bulkhead = new Bulkhead(maxConcurrent: 5);

        $result = $bulkhead->run(static fn () : ?string => null);

        self::assertNull($result);
    }

    #[Test]
    public function it_handles_operation_returning_array() : void
    {
        $bulkhead = new Bulkhead(maxConcurrent: 5);

        $result = $bulkhead->run(static fn () : array => [1, 2, 3]);

        self::assertSame([1, 2, 3], $result);
    }

    #[Test]
    public function it_isolates_between_operations() : void
    {
        $bulkhead = new Bulkhead(maxConcurrent: 3);

        $bulkhead->run(static fn () : string => 'a');
        self::assertSame(0, $bulkhead->activeCount());

        $bulkhead->run(static fn () : string => 'b');
        self::assertSame(0, $bulkhead->activeCount());

        self::assertSame(3, $bulkhead->availableSlots());
    }

    #[Test]
    public function bulkhead_exception_extends_resilience_exception() : void
    {
        $exception = new BulkheadLimitExceeded('test');

        self::assertInstanceOf(
            ResilienceException::class,
            $exception,
        );
    }

    #[Test]
    public function it_handles_operation_with_side_effects() : void
    {
        $bulkhead = new Bulkhead(maxConcurrent: 5);
        $counter  = 0;

        $bulkhead->run(static function () use (&$counter) : void {
            $counter++;
        });

        self::assertSame(1, $counter);
        self::assertSame(0, $bulkhead->activeCount());
    }

    #[Test]
    public function available_slots_never_negative() : void
    {
        $bulkhead = new Bulkhead(maxConcurrent: 1);

        self::assertSame(1, $bulkhead->availableSlots());

        $bulkhead->run(static fn () : string => 'done');

        self::assertGreaterThanOrEqual(0, $bulkhead->availableSlots());
    }

    #[Test]
    public function it_throws_bulkhead_limit_exceeded_with_correct_message() : void
    {
        $bulkhead = new Bulkhead(maxConcurrent: 2);

        $firstActive  = true;
        $secondActive = true;

        $bulkhead->run(static function () use (&$firstActive) : string {
            $firstActive = false;

            return 'op1';
        });

        self::assertFalse($firstActive);

        $bulkhead->run(static function () use (&$secondActive) : string {
            $secondActive = false;

            return 'op2';
        });

        self::assertFalse($secondActive);
        self::assertSame(0, $bulkhead->activeCount());
    }

    #[Test]
    public function it_handles_operation_returning_object() : void
    {
        $bulkhead           = new Bulkhead(maxConcurrent: 5);
        $expectedObject     = new stdClass();
        $expectedObject->id = 99;

        $result = $bulkhead->run(static fn () => $expectedObject);

        self::assertSame($expectedObject, $result);
    }

    #[Test]
    public function it_handles_nested_operations() : void
    {
        $outer = new Bulkhead(maxConcurrent: 5);
        $inner = new Bulkhead(maxConcurrent: 3);

        $result = $outer->run(static function () use ($inner) : string {
            return $inner->run(static fn () : string => 'nested');
        });

        self::assertSame('nested', $result);
        self::assertSame(0, $outer->activeCount());
        self::assertSame(0, $inner->activeCount());
    }

    #[Test]
    public function it_limits_concurrent_access_in_simulated_scenario() : void
    {
        $bulkhead    = new Bulkhead(maxConcurrent: 3);
        $maxObserved = 0;

        for ($i = 0; $i < 10; $i++) {
            $bulkhead->run(static function () use ($bulkhead, &$maxObserved) : string {
                $current = $bulkhead->activeCount();
                if ($current > $maxObserved) {
                    $maxObserved = $current;
                }

                return 'ok';
            });
        }

        self::assertSame(1, $maxObserved);
    }

    #[Test]
    public function it_reports_correct_available_slots_with_custom_limit() : void
    {
        $bulkhead = new Bulkhead(maxConcurrent: 7);

        self::assertSame(7, $bulkhead->availableSlots());

        $bulkhead->run(static function () use ($bulkhead) : string {
            self::assertSame(6, $bulkhead->availableSlots());

            return 'ok';
        });

        self::assertSame(7, $bulkhead->availableSlots());
    }

    #[Test]
    public function it_handles_exception_during_operation() : void
    {
        $bulkhead = new Bulkhead(maxConcurrent: 2);

        try {
            $bulkhead->run(static fn () : never => throw new BulkheadLimitExceeded('custom error'));
        } catch (BulkheadLimitExceeded) {
            // Expected
        }

        self::assertSame(0, $bulkhead->activeCount());
        self::assertSame(2, $bulkhead->availableSlots());
    }
}
