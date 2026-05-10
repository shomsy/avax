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
    public function it_is_readonly() : void
    {
        $timeout = new Timeout(timeoutMs: 3000);

        self::assertSame(3000, $timeout->timeoutMs);
    }

    #[Test]
    public function it_handles_operation_with_closure_capture() : void
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
}
