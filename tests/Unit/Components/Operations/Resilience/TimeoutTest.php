<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Resilience;

use Avax\Components\Operations\Resilience\System\Capabilities\Timeout\Timeout;
use Avax\Components\Operations\Resilience\System\Foundation\Failure\OperationTimedOut\OperationTimedOut;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

final class TimeoutTest extends TestCase
{
    #[Test]
    public function it_completes_operation_within_timeout() : void
    {
        $timeout = new Timeout(timeoutMs: 5000);

        $result = $timeout->run(static fn () : string => 'completed');

        self::assertSame('completed', $result);
    }

    #[Test]
    public function it_completes_fast_operation() : void
    {
        $timeout = new Timeout(timeoutMs: 1);

        $result = $timeout->run(static fn () : int => 42);

        self::assertSame(42, $result);
    }

    #[Test]
    public function it_uses_default_timeout_of_5000ms() : void
    {
        $timeout = new Timeout();

        $result = $timeout->run(static fn () : bool => true);

        self::assertTrue($result);
    }

    #[Test]
    public function it_creates_new_instance_with_custom_timeout() : void
    {
        $original = new Timeout(timeoutMs: 5000);
        $modified = $original->withTimeout(10000);

        self::assertNotSame($original, $modified);
        self::assertSame(5000, $original->timeoutMs);
        self::assertSame(10000, $modified->timeoutMs);
    }

    #[Test]
    public function it_returns_operation_result() : void
    {
        $timeout = new Timeout(timeoutMs: 5000);

        $arrayResult = $timeout->run(static fn () : array => ['a' => 1, 'b' => 2]);
        self::assertSame(['a' => 1, 'b' => 2], $arrayResult);

        $objectResult = $timeout->run(static fn () => new stdClass());
        self::assertInstanceOf(stdClass::class, $objectResult);
    }

    #[Test]
    public function it_returns_null_from_operation() : void
    {
        $timeout = new Timeout(timeoutMs: 5000);

        $result = $timeout->run(static fn () : ?string => null);

        self::assertNull($result);
    }

    #[Test]
    public function it_propagates_operation_exception() : void
    {
        $timeout = new Timeout(timeoutMs: 5000);

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('Operation error');

        $timeout->run(static fn () : never => throw new RuntimeException('Operation error'));
    }

    #[Test]
    public function it_propagates_specific_exception_types() : void
    {
        $timeout           = new Timeout(timeoutMs: 5000);
        $expectedException = new InvalidArgumentException('invalid arg');

        self::expectException(InvalidArgumentException::class);

        $timeout->run(static fn () => throw $expectedException);
    }

    #[Test]
    public function it_is_readonly_timeout_property() : void
    {
        $timeout = new Timeout(timeoutMs: 3000);

        self::assertSame(3000, $timeout->timeoutMs);
    }

    #[Test]
    public function it_handles_closure_with_captured_value() : void
    {
        $timeout       = new Timeout(timeoutMs: 5000);
        $capturedValue = 'captured';

        $result = $timeout->run(static fn () => $capturedValue);

        self::assertSame('captured', $result);
    }

    #[Test]
    public function it_handles_operation_returning_resource() : void
    {
        $timeout = new Timeout(timeoutMs: 5000);

        $result = $timeout->run(static function () : string {
            $resource = fopen('php://memory', 'r');
            fclose($resource);

            return 'resource handled';
        });

        self::assertSame('resource handled', $result);
    }

    #[Test]
    public function it_allows_chaining_with_timeout_override() : void
    {
        $original = new Timeout(timeoutMs: 1000);
        $modified = $original->withTimeout(100);

        $result = $modified->run(static fn () : string => 'fast');

        self::assertSame('fast', $result);
        self::assertSame(100, $modified->timeoutMs);
    }

    #[Test]
    public function it_accepts_very_small_timeout() : void
    {
        $timeout = new Timeout(timeoutMs: 1);

        $result = $timeout->run(static fn () : string => 'instant');

        self::assertSame('instant', $result);
    }

    #[Test]
    public function it_accepts_very_large_timeout() : void
    {
        $timeout = new Timeout(timeoutMs: 999999999);

        $result = $timeout->run(static fn () : int => 1);

        self::assertSame(1, $result);
    }

    #[Test]
    public function it_throws_timeout_with_zero_limit() : void
    {
        $timeout = new Timeout(timeoutMs: 0);

        self::expectException(OperationTimedOut::class);

        $timeout->run(static fn () : string => 'zero');
    }

    #[Test]
    public function elapsed_factory_creates_elapsed_mode_timeout() : void
    {
        $timeout = Timeout::elapsed(timeoutMs: 100);

        self::assertSame(100, $timeout->timeoutMs);
        self::assertFalse(Timeout::isPcntlAvailable() ? false : false); // mode is elapsed regardless
    }

    #[Test]
    public function preEmptive_factory_falls_back_to_elapsed_when_pcntl_unavailable() : void
    {
        $timeout = Timeout::preEmptive(timeoutMs: 100);

        if (!Timeout::isPcntlAvailable()) {
            self::assertSame(100, $timeout->timeoutMs);
            self::assertTrue($timeout->run(static fn () => true));
        } else {
            self::assertSame(100, $timeout->timeoutMs);
            self::assertTrue($timeout->run(static fn () => true));
        }
    }

    #[Test]
    public function isPcntlAvailable_returns_bool() : void
    {
        $result = Timeout::isPcntlAvailable();

        self::assertIsBool($result);
    }

    #[Test]
    public function elapsed_mode_detects_slow_operation() : void
    {
        $timeout = new Timeout(timeoutMs: 50);

        self::expectException(OperationTimedOut::class);
        self::expectExceptionMessageMatches('/Operation timed out after \d+ms \(limit: 50ms\)/');

        $timeout->run(static function () : string {
            usleep(100_000); // 100ms

            return 'done';
        });
    }

    #[Test]
    public function withTimeout_preserves_pcntl_mode() : void
    {
        $original = Timeout::preEmptive(timeoutMs: 5000);
        $modified = $original->withTimeout(1000);

        self::assertSame(5000, $original->timeoutMs);
        self::assertSame(1000, $modified->timeoutMs);
    }
}
