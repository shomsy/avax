<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Resilience;

use Avax\Components\Operations\Resilience\System\Capabilities\Retry\RetryExecutor;
use Avax\Components\Operations\Resilience\System\Capabilities\Retry\RetryOptions;
use Exception;
use PHPUnit\Framework\TestCase;

final class ResilienceCapabilitiesTest extends TestCase
{
    public function test_retry_executor_succeeds_immediately() : void
    {
        $options  = new RetryOptions(attempts: 3, backoffMs: 0);
        $executor = new RetryExecutor(fn () => 'ok', $options);

        $result = $executor->execute();

        $this->assertTrue($result->success);
        $this->assertSame('ok', $result->result);
        $this->assertSame(1, $result->attempts);
    }

    public function test_retry_executor_retries_and_succeeds() : void
    {
        $options  = new RetryOptions(attempts: 3, backoffMs: 0);
        $calls    = 0;
        $executor = new RetryExecutor(function () use (&$calls) {
            $calls++;
            if ($calls < 2) {
                throw new Exception('fail');
            }

            return 'ok';
        }, $options);

        $result = $executor->execute();

        $this->assertTrue($result->success);
        $this->assertSame(2, $result->attempts);
        $this->assertSame('ok', $result->result);
    }

    public function test_retry_executor_fails_after_max_attempts() : void
    {
        $options  = new RetryOptions(attempts: 2, backoffMs: 0);
        $executor = new RetryExecutor(function () {
            throw new Exception('permanent failure');
        }, $options);

        $result = $executor->execute();

        $this->assertFalse($result->success);
        $this->assertSame(2, $result->attempts);
        $this->assertInstanceOf(Exception::class, $result->lastException);
        $this->assertSame('permanent failure', $result->lastException->getMessage());
    }
}
