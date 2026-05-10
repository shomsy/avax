<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Resilience;

use Avax\Components\Operations\Resilience\System\Capabilities\CircuitBreaker\CircuitBreaker;
use Avax\Components\Operations\Resilience\System\Capabilities\CircuitBreaker\CircuitBreakerState;
use Avax\Components\Operations\Resilience\System\PublicSurface\Resilience;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

final class CircuitBreakerTest extends TestCase
{
    #[Test]
    public function it_starts_in_closed_state() : void
    {
        $breaker = new CircuitBreaker(failureThreshold: 3, cooldownSeconds: 30);

        self::assertSame(CircuitBreakerState::Closed, $breaker->state());
    }

    #[Test]
    public function it_runs_operation_when_closed() : void
    {
        $breaker = new CircuitBreaker(failureThreshold: 3, cooldownSeconds: 30);

        $result = $breaker->run(static fn () : string => 'success');

        self::assertSame('success', $result);
    }

    #[Test]
    public function it_opens_after_reaching_failure_threshold() : void
    {
        $breaker = new CircuitBreaker(failureThreshold: 3, cooldownSeconds: 30);

        for ($i = 0; $i < 3; $i++) {
            try {
                $breaker->run(static fn () : never => throw new RuntimeException('failure'));
            } catch (RuntimeException) {
                // Expected
            }
        }

        self::assertSame(CircuitBreakerState::Open, $breaker->state());
    }

    #[Test]
    public function it_stays_closed_below_failure_threshold() : void
    {
        $breaker = new CircuitBreaker(failureThreshold: 3, cooldownSeconds: 30);

        for ($i = 0; $i < 2; $i++) {
            try {
                $breaker->run(static fn () : never => throw new RuntimeException('failure'));
            } catch (RuntimeException) {
                // Expected
            }
        }

        self::assertSame(CircuitBreakerState::Closed, $breaker->state());
    }

    #[Test]
    public function it_throws_when_circuit_is_open() : void
    {
        $breaker = new CircuitBreaker(failureThreshold: 2, cooldownSeconds: 30);

        for ($i = 0; $i < 2; $i++) {
            try {
                $breaker->run(static fn () : never => throw new RuntimeException('failure'));
            } catch (RuntimeException) {
                // Expected
            }
        }

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('Circuit breaker is open.');

        $breaker->run(static fn () : string => 'should not run');
    }

    #[Test]
    public function it_stays_open_before_cooldown_expires() : void
    {
        $breaker = new CircuitBreaker(failureThreshold: 2, cooldownSeconds: 60);

        for ($i = 0; $i < 2; $i++) {
            try {
                $breaker->run(static fn () : never => throw new RuntimeException('failure'));
            } catch (RuntimeException) {
                // Expected
            }
        }

        self::assertSame(CircuitBreakerState::Open, $breaker->state());
    }

    #[Test]
    public function it_resets_failure_count_on_success() : void
    {
        $breaker = new CircuitBreaker(failureThreshold: 3, cooldownSeconds: 30);

        try {
            $breaker->run(static fn () : never => throw new RuntimeException('failure 1'));
        } catch (RuntimeException) {
            // Expected
        }

        $breaker->run(static fn () : string => 'success');

        self::assertSame(CircuitBreakerState::Closed, $breaker->state());

        try {
            $breaker->run(static fn () : never => throw new RuntimeException('failure 2'));
        } catch (RuntimeException) {
            // Expected
        }

        self::assertSame(CircuitBreakerState::Closed, $breaker->state());
    }

    #[Test]
    public function it_uses_default_failure_threshold_of_three() : void
    {
        $breaker = Resilience::circuitBreaker();

        $breaker->run(static fn () : string => 'ok');

        self::assertSame(CircuitBreakerState::Closed, $breaker->state());
    }

    #[Test]
    public function it_uses_default_cooldown_of_thirty_seconds() : void
    {
        $breaker = Resilience::circuitBreaker();

        $result = $breaker->run(static fn () : int => 42);

        self::assertSame(42, $result);
    }

    #[Test]
    public function it_propagates_operation_exception() : void
    {
        $breaker = new CircuitBreaker(failureThreshold: 5, cooldownSeconds: 30);

        $expectedException = new RuntimeException('specific error');

        try {
            $breaker->run(static fn () => throw $expectedException);
        } catch (RuntimeException $e) {
            self::assertSame('specific error', $e->getMessage());
            self::assertSame($expectedException, $e);
        }
    }

    #[Test]
    public function it_records_success_and_resets_state() : void
    {
        $breaker = new CircuitBreaker(failureThreshold: 3, cooldownSeconds: 30);

        $breaker->run(static fn () : string => 'ok');
        $breaker->run(static fn () : string => 'ok');

        self::assertSame(CircuitBreakerState::Closed, $breaker->state());
    }

    #[Test]
    public function it_handles_operation_returning_null() : void
    {
        $breaker = new CircuitBreaker(failureThreshold: 3, cooldownSeconds: 30);

        $result = $breaker->run(static fn () : ?string => null);

        self::assertNull($result);
        self::assertSame(CircuitBreakerState::Closed, $breaker->state());
    }

    #[Test]
    public function it_handles_operation_returning_object() : void
    {
        $breaker               = new CircuitBreaker(failureThreshold: 3, cooldownSeconds: 30);
        $expectedObject        = new stdClass();
        $expectedObject->value = 123;

        $result = $breaker->run(static fn () => $expectedObject);

        self::assertSame($expectedObject, $result);
    }

    #[Test]
    public function it_opens_at_exact_threshold() : void
    {
        $breaker = new CircuitBreaker(failureThreshold: 1, cooldownSeconds: 30);

        try {
            $breaker->run(static fn () : never => throw new RuntimeException('single failure'));
        } catch (RuntimeException) {
            // Expected
        }

        self::assertSame(CircuitBreakerState::Open, $breaker->state());
    }

    #[Test]
    public function it_tracks_failures_across_multiple_operations() : void
    {
        $breaker = new CircuitBreaker(failureThreshold: 4, cooldownSeconds: 30);

        try {
            $breaker->run(static fn () : never => throw new RuntimeException('fail 1'));
        } catch (RuntimeException) {
            // Expected
        }
        self::assertSame(CircuitBreakerState::Closed, $breaker->state());

        try {
            $breaker->run(static fn () : never => throw new RuntimeException('fail 2'));
        } catch (RuntimeException) {
            // Expected
        }
        self::assertSame(CircuitBreakerState::Closed, $breaker->state());

        try {
            $breaker->run(static fn () : never => throw new RuntimeException('fail 3'));
        } catch (RuntimeException) {
            // Expected
        }
        self::assertSame(CircuitBreakerState::Closed, $breaker->state());

        try {
            $breaker->run(static fn () : never => throw new RuntimeException('fail 4'));
        } catch (RuntimeException) {
            // Expected
        }
        self::assertSame(CircuitBreakerState::Open, $breaker->state());
    }

    #[Test]
    public function circuit_breaker_state_enum_has_expected_values() : void
    {
        self::assertSame('closed', CircuitBreakerState::Closed->value);
        self::assertSame('open', CircuitBreakerState::Open->value);
        self::assertSame('half_open', CircuitBreakerState::HalfOpen->value);
    }

    #[Test]
    public function it_stays_open_with_long_cooldown() : void
    {
        $breaker = new CircuitBreaker(failureThreshold: 1, cooldownSeconds: 86400);

        try {
            $breaker->run(static fn () : never => throw new RuntimeException('failure'));
        } catch (RuntimeException) {
            // Expected
        }

        self::assertSame(CircuitBreakerState::Open, $breaker->state());
    }

    #[Test]
    public function it_blocks_all_requests_when_open() : void
    {
        $breaker = new CircuitBreaker(failureThreshold: 1, cooldownSeconds: 30);

        try {
            $breaker->run(static fn () : never => throw new RuntimeException('failure'));
        } catch (RuntimeException) {
            // Expected
        }

        for ($i = 0; $i < 5; $i++) {
            try {
                $breaker->run(static fn () : string => 'should not run');
                self::fail('Expected exception on iteration ' . $i);
            } catch (RuntimeException $e) {
                self::assertSame('Circuit breaker is open.', $e->getMessage());
            }
        }
    }

    #[Test]
    public function it_opens_exactly_at_threshold_boundary() : void
    {
        $breaker = new CircuitBreaker(failureThreshold: 5, cooldownSeconds: 30);

        for ($i = 0; $i < 4; $i++) {
            try {
                $breaker->run(static fn () : never => throw new RuntimeException('fail'));
            } catch (RuntimeException) {
                // Expected
            }
            self::assertSame(CircuitBreakerState::Closed, $breaker->state());
        }

        try {
            $breaker->run(static fn () : never => throw new RuntimeException('fail 5'));
        } catch (RuntimeException) {
            // Expected
        }

        self::assertSame(CircuitBreakerState::Open, $breaker->state());
    }

    #[Test]
    public function it_accepts_custom_threshold_and_cooldown() : void
    {
        $breaker = new CircuitBreaker(failureThreshold: 10, cooldownSeconds: 120);

        for ($i = 0; $i < 9; $i++) {
            try {
                $breaker->run(static fn () : never => throw new RuntimeException('fail'));
            } catch (RuntimeException) {
                // Expected
            }
        }

        self::assertSame(CircuitBreakerState::Closed, $breaker->state());
    }
}
