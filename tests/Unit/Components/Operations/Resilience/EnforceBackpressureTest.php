<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Resilience;

use Avax\Components\Operations\Resilience\System\Capabilities\Backpressure\BackpressurePolicy;
use Avax\Components\Operations\Resilience\System\Flows\EnforceBackpressure\EnforceBackpressure;
use Avax\Components\Operations\Resilience\System\Foundation\Failure\BackpressureFailure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EnforceBackpressure::class)]
#[CoversClass(BackpressureFailure::class)]
final class EnforceBackpressureTest extends TestCase
{
    public function testAllowsWorkBelowThreshold() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 100, loadThreshold: 0.9);
        $enforcer = new EnforceBackpressure($policy);

        // Should not throw
        $enforcer->execute(currentQueueSize: 50, currentLoad: 0.5);

        self::assertTrue(true);
    }

    public function testRejectsWorkWhenQueueFull() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 100, loadThreshold: 0.9);
        $enforcer = new EnforceBackpressure($policy);

        $this->expectException(BackpressureFailure::class);
        $this->expectExceptionMessage('Backpressure enforced');

        $enforcer->execute(currentQueueSize: 100, currentLoad: 0.5);
    }

    public function testRejectsWorkWhenLoadHigh() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 100, loadThreshold: 0.9);
        $enforcer = new EnforceBackpressure($policy);

        $this->expectException(BackpressureFailure::class);

        $enforcer->execute(currentQueueSize: 50, currentLoad: 0.95);
    }

    public function testCheckDelayReturnsFalseWhenQueueLow() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 100, loadThreshold: 0.9);
        $enforcer = new EnforceBackpressure($policy);

        $result = $enforcer->checkDelay(currentQueueSize: 50);

        self::assertFalse($result['should_delay']);
        self::assertLessThanOrEqual(500, $result['suggested_delay_ms']);
    }

    public function testCheckDelayReturnsTrueWhenQueueNearCapacity() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 100, loadThreshold: 0.9);
        $enforcer = new EnforceBackpressure($policy);

        $result = $enforcer->checkDelay(currentQueueSize: 85);

        self::assertTrue($result['should_delay']);
        self::assertGreaterThan(0, $result['suggested_delay_ms']);
    }

    public function testCheckDelayCapsAt1000ms() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 100, loadThreshold: 0.9);
        $enforcer = new EnforceBackpressure($policy);

        $result = $enforcer->checkDelay(currentQueueSize: 200);

        self::assertLessThanOrEqual(1000, $result['suggested_delay_ms']);
    }
}
