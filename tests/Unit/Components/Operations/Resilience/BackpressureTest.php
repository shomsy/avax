<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Resilience;

use Avax\Components\Operations\Resilience\System\Capabilities\Backpressure\BackpressurePolicy;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BackpressureTest extends TestCase
{
    #[Test]
    public function it_does_not_reject_below_thresholds() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 1000, loadThreshold: 0.9);

        self::assertFalse($policy->shouldReject(currentQueueSize: 500, currentLoad: 0.5));
    }

    #[Test]
    public function it_rejects_when_queue_size_reaches_max() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 1000, loadThreshold: 0.9);

        self::assertTrue($policy->shouldReject(currentQueueSize: 1000, currentLoad: 0.5));
    }

    #[Test]
    public function it_rejects_when_queue_size_exceeds_max() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 1000, loadThreshold: 0.9);

        self::assertTrue($policy->shouldReject(currentQueueSize: 1500, currentLoad: 0.5));
    }

    #[Test]
    public function it_rejects_when_load_reaches_threshold() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 1000, loadThreshold: 0.9);

        self::assertTrue($policy->shouldReject(currentQueueSize: 100, currentLoad: 0.9));
    }

    #[Test]
    public function it_rejects_when_load_exceeds_threshold() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 1000, loadThreshold: 0.9);

        self::assertTrue($policy->shouldReject(currentQueueSize: 100, currentLoad: 0.95));
    }

    #[Test]
    public function it_does_not_delay_below_80_percent() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 1000, loadThreshold: 0.9);

        self::assertFalse($policy->shouldDelay(currentQueueSize: 700));
    }

    #[Test]
    public function it_delays_at_80_percent() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 1000, loadThreshold: 0.9);

        self::assertTrue($policy->shouldDelay(currentQueueSize: 800));
    }

    #[Test]
    public function it_delays_above_80_percent() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 1000, loadThreshold: 0.9);

        self::assertTrue($policy->shouldDelay(currentQueueSize: 900));
    }

    #[Test]
    public function it_uses_default_max_queue_size_of_1000() : void
    {
        $policy = new BackpressurePolicy();

        self::assertSame(1000, $policy->maxQueueSize);
    }

    #[Test]
    public function it_uses_default_load_threshold_of_0_9() : void
    {
        $policy = new BackpressurePolicy();

        self::assertSame(0.9, $policy->loadThreshold);
    }

    #[Test]
    public function it_does_not_reject_when_both_values_are_low() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 500, loadThreshold: 0.8);

        self::assertFalse($policy->shouldReject(currentQueueSize: 100, currentLoad: 0.3));
    }

    #[Test]
    public function it_rejects_on_queue_size_even_with_low_load() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 100, loadThreshold: 0.9);

        self::assertTrue($policy->shouldReject(currentQueueSize: 100, currentLoad: 0.1));
    }

    #[Test]
    public function it_rejects_on_load_even_with_low_queue_size() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 1000, loadThreshold: 0.5);

        self::assertTrue($policy->shouldReject(currentQueueSize: 10, currentLoad: 0.5));
    }

    #[Test]
    public function it_uses_custom_thresholds() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 50, loadThreshold: 0.7);

        self::assertTrue($policy->shouldReject(currentQueueSize: 50, currentLoad: 0.5));
        self::assertTrue($policy->shouldReject(currentQueueSize: 10, currentLoad: 0.7));
        self::assertFalse($policy->shouldReject(currentQueueSize: 49, currentLoad: 0.69));
    }

    #[Test]
    public function delay_threshold_is_80_percent_of_max_queue_size() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 200, loadThreshold: 0.9);

        self::assertFalse($policy->shouldDelay(currentQueueSize: 159));
        self::assertTrue($policy->shouldDelay(currentQueueSize: 160));
        self::assertTrue($policy->shouldDelay(currentQueueSize: 200));
    }

    #[Test]
    public function it_handles_zero_queue_size() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 100, loadThreshold: 0.9);

        self::assertFalse($policy->shouldReject(currentQueueSize: 0, currentLoad: 0.0));
        self::assertFalse($policy->shouldDelay(currentQueueSize: 0));
    }

    #[Test]
    public function it_handles_zero_load() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 100, loadThreshold: 0.9);

        self::assertFalse($policy->shouldReject(currentQueueSize: 50, currentLoad: 0.0));
    }

    #[Test]
    public function it_handles_full_load() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 1000, loadThreshold: 0.9);

        self::assertTrue($policy->shouldReject(currentQueueSize: 500, currentLoad: 1.0));
    }

    #[Test]
    public function it_handles_boundary_values_exactly() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 1000, loadThreshold: 0.9);

        self::assertFalse($policy->shouldReject(currentQueueSize: 999, currentLoad: 0.89));
        self::assertTrue($policy->shouldReject(currentQueueSize: 1000, currentLoad: 0.89));
        self::assertTrue($policy->shouldReject(currentQueueSize: 999, currentLoad: 0.9));
    }

    #[Test]
    public function it_is_readonly() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 500, loadThreshold: 0.75);

        self::assertSame(500, $policy->maxQueueSize);
        self::assertSame(0.75, $policy->loadThreshold);
    }

    #[Test]
    public function it_handles_small_queue_size_threshold() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 5, loadThreshold: 0.9);

        self::assertFalse($policy->shouldReject(currentQueueSize: 4, currentLoad: 0.5));
        self::assertTrue($policy->shouldReject(currentQueueSize: 5, currentLoad: 0.5));
        self::assertTrue($policy->shouldDelay(currentQueueSize: 4));
    }

    #[Test]
    public function it_handles_high_load_threshold() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 1000, loadThreshold: 0.99);

        self::assertFalse($policy->shouldReject(currentQueueSize: 500, currentLoad: 0.98));
        self::assertTrue($policy->shouldReject(currentQueueSize: 500, currentLoad: 0.99));
    }

    #[Test]
    public function it_handles_low_load_threshold() : void
    {
        $policy = new BackpressurePolicy(maxQueueSize: 1000, loadThreshold: 0.1);

        self::assertTrue($policy->shouldReject(currentQueueSize: 100, currentLoad: 0.1));
        self::assertFalse($policy->shouldReject(currentQueueSize: 100, currentLoad: 0.09));
    }
}
