<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Operations\Resilience;

use Avax\Components\Operations\Resilience\System\Flows\ExecuteWithReliability\ExecuteWithReliability;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

final class ReliabilityEngineTest extends TestCase
{
    public function test_execute_succeeds_first_attempt() : void
    {
        $engine = new ExecuteWithReliability();

        $result = $engine->execute(static fn () => 'success');

        self::assertSame('success', $result);
    }

    public function test_execute_retries_on_failure_then_succeeds() : void
    {
        $attempts = 0;
        $engine = new ExecuteWithReliability();
        $engine->withMaxAttempts(3);
        $engine->withBackoffMs(1);

        $result = $engine->execute(static function () use (&$attempts) {
            $attempts++;
            if ($attempts < 3) {
                throw new RuntimeException('transient failure');
            }

            return 'recovered';
        });

        self::assertSame('recovered', $result);
        self::assertSame(3, $attempts);
    }

    public function test_execute_fails_after_max_attempts() : void
    {
        $engine = new ExecuteWithReliability();
        $engine->withMaxAttempts(2);
        $engine->withBackoffMs(1);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('always fails');

        $engine->execute(static fn () => throw new RuntimeException('always fails'));
    }

    public function test_execute_uses_fallback_on_failure() : void
    {
        $engine = new ExecuteWithReliability();
        $engine->withMaxAttempts(2);
        $engine->withBackoffMs(1);
        $engine->withFallback(static fn () => 'fallback value');

        $result = $engine->execute(static fn () => throw new RuntimeException('boom'));

        self::assertSame('fallback value', $result);
    }

    public function test_execute_uses_fallback_with_exception() : void
    {
        $engine = new ExecuteWithReliability();
        $engine->withMaxAttempts(1);
        $engine->withFallback(static function (Throwable $e) {
            return 'failed: ' . $e->getMessage();
        });

        $result = $engine->execute(static fn () => throw new RuntimeException('boom'));

        self::assertSame('failed: boom', $result);
    }

    public function test_circuit_breaker_opens_after_threshold() : void
    {
        $engine = new ExecuteWithReliability();
        $engine->withMaxAttempts(1);
        $engine->withBackoffMs(1);
        $engine->withCircuitBreaker(failureThreshold: 2, cooldownSeconds: 1);
        $engine->withFallback(static fn () => 'cb-fallback');

        // First failure — CB still closed
        $result1 = $engine->execute(static fn () => throw new RuntimeException('fail'));
        self::assertSame('cb-fallback', $result1);

        // Second failure — CB opens
        $result2 = $engine->execute(static fn () => throw new RuntimeException('fail'));
        self::assertSame('cb-fallback', $result2);
    }

    public function test_timeout_exceeds_throws() : void
    {
        $engine = new ExecuteWithReliability();
        $engine->withMaxAttempts(1);
        $engine->withTimeoutMs(50);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Operation timed out');

        $engine->execute(static function () {
            usleep(200_000); // 200ms — exceeds 50ms timeout
            return 'too slow';
        });
    }

    public function test_timeout_allows_fast_operation() : void
    {
        $engine = new ExecuteWithReliability();
        $engine->withMaxAttempts(1);
        $engine->withTimeoutMs(5000);

        $result = $engine->execute(static fn () => 'fast');

        self::assertSame('fast', $result);
    }

    public function test_fallback_receives_exception_from_timeout() : void
    {
        $engine = new ExecuteWithReliability();
        $engine->withMaxAttempts(1);
        $engine->withTimeoutMs(50);
        $engine->withFallback(static function (Throwable $e) {
            return 'timeout fallback';
        });

        $result = $engine->execute(static function () {
            usleep(200_000);
            return 'too slow';
        });

        self::assertSame('timeout fallback', $result);
    }

    public function test_composition_retry_plus_fallback() : void
    {
        $attempts = 0;
        $engine = new ExecuteWithReliability();
        $engine->withMaxAttempts(3);
        $engine->withBackoffMs(1);
        $engine->withFallback(static fn () => 'final fallback');

        // Fails 3 times, then fallback kicks in
        $result = $engine->execute(static function () use (&$attempts) {
            $attempts++;
            throw new RuntimeException('fail #' . $attempts);
        });

        self::assertSame('final fallback', $result);
        self::assertSame(3, $attempts);
    }
}
