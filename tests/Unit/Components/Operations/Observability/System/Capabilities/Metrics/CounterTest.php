<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Observability\System\Capabilities\Metrics;

use Avax\Components\Operations\Observability\System\Capabilities\Metrics\Counter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class CounterTest extends TestCase
{
    #[Test]
    public function it_starts_at_zero() : void
    {
        $counter = new Counter('requests');

        self::assertSame(0.0, $counter->getValue());
    }

    #[Test]
    public function it_has_name() : void
    {
        $counter = new Counter('http_requests_total');

        self::assertSame('http_requests_total', $counter->name);
    }

    #[Test]
    public function it_has_default_empty_tags() : void
    {
        $counter = new Counter('requests');

        self::assertSame([], $counter->tags);
    }

    #[Test]
    public function it_has_tags() : void
    {
        $counter = new Counter('requests', ['method' => 'GET', 'status' => '200']);

        self::assertSame(['method' => 'GET', 'status' => '200'], $counter->tags);
    }

    #[Test]
    public function it_increments_by_one_by_default() : void
    {
        $counter = new Counter('requests');

        $counter->increment();

        self::assertSame(1.0, $counter->getValue());
    }

    #[Test]
    public function it_increments_by_specified_amount() : void
    {
        $counter = new Counter('bytes');

        $counter->increment(100.0);

        self::assertSame(100.0, $counter->getValue());
    }

    #[Test]
    public function it_accumulates_increments() : void
    {
        $counter = new Counter('requests');

        $counter->increment(1.0);
        $counter->increment(2.0);
        $counter->increment(3.5);

        self::assertSame(6.5, $counter->getValue());
    }

    #[Test]
    public function it_increments_multiple_times_by_default() : void
    {
        $counter = new Counter('events');

        $counter->increment();
        $counter->increment();
        $counter->increment();

        self::assertSame(3.0, $counter->getValue());
    }

    #[Test]
    public function it_increments_by_fractional_amount() : void
    {
        $counter = new Counter('progress');

        $counter->increment(0.1);
        $counter->increment(0.2);

        self::assertEqualsWithDelta(0.3, $counter->getValue(), 0.0001);
    }

    #[Test]
    public function it_increments_by_zero() : void
    {
        $counter = new Counter('requests');
        $counter->increment(5.0);

        $counter->increment(0.0);

        self::assertSame(5.0, $counter->getValue());
    }

    #[Test]
    public function it_can_decrement_with_negative_amount() : void
    {
        $counter = new Counter('adjustable');
        $counter->increment(10.0);

        $counter->increment(-3.0);

        self::assertSame(7.0, $counter->getValue());
    }

    #[Test]
    public function it_has_readonly_name() : void
    {
        $reflection = new ReflectionClass(Counter::class);
        $nameProp   = $reflection->getProperty('name');

        self::assertTrue($nameProp->isReadOnly());
    }

    #[Test]
    public function it_has_readonly_tags() : void
    {
        $reflection = new ReflectionClass(Counter::class);
        $tagsProp   = $reflection->getProperty('tags');

        self::assertTrue($tagsProp->isReadOnly());
    }
}
