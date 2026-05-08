<?php

declare(strict_types=1);

namespace Avax\Tests\GoldenPathRuntime;

use Avax\Components\Operations\Resilience\System\Capabilities\CircuitBreaker\CircuitBreaker;
use Avax\Components\Operations\Resilience\System\Capabilities\CircuitBreaker\CircuitBreakerState;
use Avax\Components\Operations\Resilience\System\Capabilities\Fallback\Fallback;
use Avax\Components\Operations\Resilience\System\Capabilities\Timeout\Timeout;
use Avax\Components\Operations\Resilience\System\Foundation\Failure\OperationTimedOut\OperationTimedOut;
use Avax\Examples\GoldenPathRuntimeApp\Resilience\FallbackStrategies;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Proves: CircuitBreaker state transitions, Timeout enforcement, Fallback chain execution.
 */
final class ResilienceBehaviorTest extends TestCase
{
    #[Test]
    public function circuitBreakerStartsClosed() : void
    {
        $cb = new CircuitBreaker(failureThreshold: 3, cooldownSeconds: 30);

        self::assertSame(CircuitBreakerState::Closed, $cb->state());
    }

    #[Test]
    public function circuitBreakerOpensAfterThreshold() : void
    {
        $cb = new CircuitBreaker(failureThreshold: 3, cooldownSeconds: 30);

        for ($i = 0; $i < 3; $i++) {
            try {
                $cb->run(static fn () => throw new RuntimeException('fail'));
            } catch (RuntimeException) {
                // Expected
            }
        }

        self::assertSame(CircuitBreakerState::Open, $cb->state());
    }

    #[Test]
    public function circuitBreakerRejectsWhenOpen() : void
    {
        $cb = new CircuitBreaker(failureThreshold: 2, cooldownSeconds: 30);

        // Trigger failures to open the circuit
        for ($i = 0; $i < 2; $i++) {
            try {
                $cb->run(static fn () => throw new RuntimeException('fail'));
            } catch (RuntimeException) {
                // Expected
            }
        }

        self::assertSame(CircuitBreakerState::Open, $cb->state());

        // Should reject when open
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Circuit breaker is open');
        $cb->run(static fn () => 'should not run');
    }

    #[Test]
    public function circuitBreakerTransitionsToHalfOpenAfterCooldown() : void
    {
        $cb = new CircuitBreaker(failureThreshold: 2, cooldownSeconds: 1);

        // Open the circuit
        for ($i = 0; $i < 2; $i++) {
            try {
                $cb->run(static fn () => throw new RuntimeException('fail'));
            } catch (RuntimeException) {
                // Expected
            }
        }

        self::assertSame(CircuitBreakerState::Open, $cb->state());

        // Wait for cooldown
        sleep(1);

        // Check state — should transition to HalfOpen
        self::assertSame(CircuitBreakerState::HalfOpen, $cb->state());
    }

    #[Test]
    public function circuitBreakerClosesOnSuccessInHalfOpen() : void
    {
        $cb = new CircuitBreaker(failureThreshold: 2, cooldownSeconds: 1);

        // Open the circuit
        for ($i = 0; $i < 2; $i++) {
            try {
                $cb->run(static fn () => throw new RuntimeException('fail'));
            } catch (RuntimeException) {
                // Expected
            }
        }

        sleep(1);

        // Should be HalfOpen
        self::assertSame(CircuitBreakerState::HalfOpen, $cb->state());

        // Successful operation should close it
        $result = $cb->run(static fn () => 'success');

        self::assertSame('success', $result);
        self::assertSame(CircuitBreakerState::Closed, $cb->state());
    }

    #[Test]
    public function circuitBreakerReopensOnFailureInHalfOpen() : void
    {
        $cb = new CircuitBreaker(failureThreshold: 2, cooldownSeconds: 1);

        // Open the circuit
        for ($i = 0; $i < 2; $i++) {
            try {
                $cb->run(static fn () => throw new RuntimeException('fail'));
            } catch (RuntimeException) {
                // Expected
            }
        }

        sleep(1);
        self::assertSame(CircuitBreakerState::HalfOpen, $cb->state());

        // Failure in HalfOpen should reopen
        try {
            $cb->run(static fn () => throw new RuntimeException('fail again'));
        } catch (RuntimeException) {
            // Expected
        }

        self::assertSame(CircuitBreakerState::Open, $cb->state());
    }

    #[Test]
    public function timeoutAllowsFastOperation() : void
    {
        $timeout = new Timeout(timeoutMs: 1000);

        $result = $timeout->run(static fn () => 'fast');

        self::assertSame('fast', $result);
    }

    #[Test]
    public function timeoutThrowsOnSlowOperation() : void
    {
        $timeout = new Timeout(timeoutMs: 10);

        $this->expectException(OperationTimedOut::class);

        $timeout->run(static function () : string {
            usleep(20_000); // 20ms — exceeds 10ms timeout

            return 'slow';
        });
    }

    #[Test]
    public function fallbackChainExecutesOnFailure() : void
    {
        $callCount = 0;

        $result = Fallback::execute([
                                        static function () use (&$callCount) {
                                            $callCount++;
                                            throw new RuntimeException('primary failed');
                                        },
                                        static function () use (&$callCount) {
                                            $callCount++;

                                            return 'fallback_result';
                                        },
                                    ]);

        self::assertSame('fallback_result', $result);
        self::assertSame(2, $callCount);
    }

    #[Test]
    public function fallbackReturnsFirstSuccess() : void
    {
        $callCount = 0;

        $result = Fallback::execute([
                                        static function () use (&$callCount) {
                                            $callCount++;

                                            return 'primary_success';
                                        },
                                        static function () use (&$callCount) {
                                            $callCount++;

                                            return 'should_not_reach';
                                        },
                                    ]);

        self::assertSame('primary_success', $result);
        self::assertSame(1, $callCount);
    }

    #[Test]
    public function fallbackThrowsLastExceptionOnAllFailures() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('all failed');

        Fallback::execute([
                              static fn () => throw new RuntimeException('first'),
                              static fn () => throw new RuntimeException('second'),
                              static fn () => throw new RuntimeException('all failed'),
                          ]);
    }

    #[Test]
    public function fallbackStrategiesReturnExpectedShape() : void
    {
        $degraded = (FallbackStrategies::externalServiceDegraded())();

        self::assertIsArray($degraded);
        self::assertSame('degraded', $degraded['status']);

        $cached = (FallbackStrategies::cachedWebhookStatus('wh_001'))();

        self::assertIsArray($cached);
        self::assertSame('wh_001', $cached['webhook_id']);
        self::assertSame('cached', $cached['status']);
    }
}
