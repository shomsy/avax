<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Resilience;

use Avax\Components\Operations\Resilience\System\Capabilities\Retry\RetryBuilder;
use Avax\Components\Operations\Resilience\System\Capabilities\Retry\RetryExecutor;
use Avax\Components\Operations\Resilience\System\Capabilities\Retry\RetryOptions;
use Avax\Components\Operations\Resilience\System\Capabilities\Retry\RetryResult;
use Avax\Components\Operations\Resilience\System\PublicSurface\Resilience;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use stdClass;
use Throwable;

final class RetryTest extends TestCase
{
    #[Test]
    public function it_succeeds_on_first_attempt() : void
    {
        $result = Resilience::retry(static fn () : string => 'success')
            ->times(3)
            ->backoff(0)
            ->run();

        self::assertInstanceOf(RetryResult::class, $result);
        self::assertTrue($result->success);
        self::assertSame('success', $result->result);
        self::assertSame(1, $result->attempts);
        self::assertNull($result->lastException);
    }

    #[Test]
    public function it_succeeds_after_retry() : void
    {
        $attemptCount = 0;
        $operation    = static function () use (&$attemptCount) : string {
            $attemptCount++;
            if ($attemptCount < 3) {
                throw new RuntimeException('Transient failure');
            }

            return 'recovered';
        };

        $result = Resilience::retry($operation)
            ->times(5)
            ->backoff(0)
            ->run();

        self::assertTrue($result->success);
        self::assertSame('recovered', $result->result);
        self::assertSame(3, $result->attempts);
        self::assertNull($result->lastException);
    }

    #[Test]
    public function it_fails_after_exhausting_all_attempts() : void
    {
        $attemptCount = 0;
        $operation    = static function () use (&$attemptCount) : never {
            $attemptCount++;
            throw new RuntimeException('Persistent failure #' . $attemptCount);
        };

        $result = Resilience::retry($operation)
            ->times(3)
            ->backoff(0)
            ->run();

        self::assertFalse($result->success);
        self::assertNull($result->result);
        self::assertSame(3, $result->attempts);
        self::assertInstanceOf(Throwable::class, $result->lastException);
        self::assertSame('Persistent failure #3', $result->lastException->getMessage());
    }

    #[Test]
    public function it_uses_default_attempts_of_three() : void
    {
        $attemptCount = 0;
        $operation    = static function () use (&$attemptCount) : never {
            $attemptCount++;
            throw new RuntimeException('fail');
        };

        Resilience::retry($operation)
            ->backoff(0)
            ->run();

        self::assertSame(3, $attemptCount);
    }

    #[Test]
    public function it_uses_default_backoff_of_200ms() : void
    {
        $builder = new RetryBuilder(static fn () : string => 'ok');

        $reflection = new ReflectionClass($builder);
        $property   = $reflection->getProperty('retryOptions');
        $options    = $property->getValue($builder);

        self::assertInstanceOf(RetryOptions::class, $options);
        self::assertSame(3, $options->attempts);
        self::assertSame(200, $options->backoffMs);
    }

    #[Test]
    public function it_accepts_custom_attempt_count() : void
    {
        $attemptCount = 0;
        $operation    = static function () use (&$attemptCount) : never {
            $attemptCount++;
            throw new RuntimeException('fail');
        };

        Resilience::retry($operation)
            ->times(5)
            ->backoff(0)
            ->run();

        self::assertSame(5, $attemptCount);
    }

    #[Test]
    public function it_accepts_custom_backoff() : void
    {
        $builder = new RetryBuilder(static fn () : string => 'ok');
        $builder->backoff(500);

        $reflection = new ReflectionClass($builder);
        $property   = $reflection->getProperty('retryOptions');
        $options    = $property->getValue($builder);

        self::assertSame(500, $options->backoffMs);
    }

    #[Test]
    public function it_executes_operation_directly_via_retry_executor() : void
    {
        $options  = new RetryOptions(attempts: 1, backoffMs: 0);
        $executor = new RetryExecutor(
            operation   : static fn () : int => 42,
            retryOptions: $options,
        );

        $result = $executor->execute();

        self::assertTrue($result->success);
        self::assertSame(42, $result->result);
        self::assertSame(1, $result->attempts);
    }

    #[Test]
    public function it_retries_with_retry_executor() : void
    {
        $callCount = 0;
        $options   = new RetryOptions(attempts: 3, backoffMs: 0);
        $executor  = new RetryExecutor(
            operation   : static function () use (&$callCount) : string {
                $callCount++;
                if ($callCount < 2) {
                    throw new RuntimeException('transient');
                }

                return 'ok';
            },
            retryOptions: $options,
        );

        $result = $executor->execute();

        self::assertTrue($result->success);
        self::assertSame(2, $result->attempts);
    }

    #[Test]
    public function it_returns_null_result_on_failure() : void
    {
        $options  = new RetryOptions(attempts: 2, backoffMs: 0);
        $executor = new RetryExecutor(
            operation   : static fn () : never => throw new RuntimeException('always fails'),
            retryOptions: $options,
        );

        $result = $executor->execute();

        self::assertFalse($result->success);
        self::assertNull($result->result);
        self::assertSame(2, $result->attempts);
    }

    #[Test]
    public function it_stores_last_exception_on_failure() : void
    {
        $options  = new RetryOptions(attempts: 3, backoffMs: 0);
        $executor = new RetryExecutor(
            operation   : static fn () : never => throw new RuntimeException('error-123'),
            retryOptions: $options,
        );

        $result = $executor->execute();

        self::assertNotNull($result->lastException);
        self::assertSame('error-123', $result->lastException->getMessage());
        self::assertInstanceOf(RuntimeException::class, $result->lastException);
    }

    #[Test]
    public function it_returns_immediate_success_without_retry() : void
    {
        $result = Resilience::retry(static fn () : bool => true)
            ->times(1)
            ->backoff(0)
            ->run();

        self::assertTrue($result->success);
        self::assertTrue($result->result);
        self::assertSame(1, $result->attempts);
    }

    #[Test]
    public function it_fails_immediately_with_single_attempt() : void
    {
        $result = Resilience::retry(static fn () : never => throw new RuntimeException('single'))
            ->times(1)
            ->backoff(0)
            ->run();

        self::assertFalse($result->success);
        self::assertSame(1, $result->attempts);
        self::assertSame('single', $result->lastException->getMessage());
    }

    #[Test]
    public function it_handles_operation_returning_null() : void
    {
        $result = Resilience::retry(static fn () : ?string => null)
            ->times(3)
            ->backoff(0)
            ->run();

        self::assertTrue($result->success);
        self::assertNull($result->result);
        self::assertSame(1, $result->attempts);
    }

    #[Test]
    public function it_handles_operation_returning_array() : void
    {
        $result = Resilience::retry(static fn () : array => ['a' => 1, 'b' => 2])
            ->times(3)
            ->backoff(0)
            ->run();

        self::assertTrue($result->success);
        self::assertSame(['a' => 1, 'b' => 2], $result->result);
    }

    #[Test]
    public function it_handles_operation_returning_object() : void
    {
        $expectedObject     = new stdClass();
        $expectedObject->id = 42;

        $result = Resilience::retry(static fn () => $expectedObject)
            ->times(3)
            ->backoff(0)
            ->run();

        self::assertTrue($result->success);
        self::assertSame($expectedObject, $result->result);
    }

    #[Test]
    public function it_handles_different_exception_types() : void
    {
        $attemptCount = 0;
        $operation    = static function () use (&$attemptCount) : never {
            $attemptCount++;
            if ($attemptCount === 1) {
                throw new RuntimeException('runtime');
            }
            throw new InvalidArgumentException('invalid');
        };

        $result = Resilience::retry($operation)
            ->times(3)
            ->backoff(0)
            ->run();

        self::assertFalse($result->success);
        self::assertNotNull($result->lastException);
        self::assertInstanceOf(InvalidArgumentException::class, $result->lastException);
        self::assertSame('invalid', $result->lastException->getMessage());
    }

    #[Test]
    public function retry_options_are_immutable() : void
    {
        $original = new RetryOptions(attempts: 3, backoffMs: 100);
        $modified = $original->withAttempts(5);

        self::assertNotSame($original, $modified);
        self::assertSame(3, $original->attempts);
        self::assertSame(5, $modified->attempts);
        self::assertSame(100, $modified->backoffMs);
    }

    #[Test]
    public function retry_options_preserves_timeout_on_modification() : void
    {
        $original = new RetryOptions(attempts: 3, backoffMs: 100, timeoutMs: 5000);
        $modified = $original->withAttempts(5);

        self::assertSame(5000, $modified->timeoutMs);
    }

    #[Test]
    public function retry_builder_returns_new_instance_on_times() : void
    {
        $builder  = new RetryBuilder(static fn () : string => 'ok');
        $modified = $builder->times(5);

        self::assertInstanceOf(RetryBuilder::class, $modified);
    }

    #[Test]
    public function retry_builder_returns_new_instance_on_backoff() : void
    {
        $builder  = new RetryBuilder(static fn () : string => 'ok');
        $modified = $builder->backoff(1000);

        self::assertInstanceOf(RetryBuilder::class, $modified);
    }

    #[Test]
    public function it_recovers_on_final_attempt() : void
    {
        $attemptCount = 0;
        $operation    = static function () use (&$attemptCount) : string {
            $attemptCount++;
            if ($attemptCount < 5) {
                throw new RuntimeException('fail-' . $attemptCount);
            }

            return 'finally';
        };

        $result = Resilience::retry($operation)
            ->times(5)
            ->backoff(0)
            ->run();

        self::assertTrue($result->success);
        self::assertSame('finally', $result->result);
        self::assertSame(5, $result->attempts);
    }
}
