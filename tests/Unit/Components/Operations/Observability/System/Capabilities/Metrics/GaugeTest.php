<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Observability\System\Capabilities\Metrics;

use Avax\Components\Operations\Observability\System\Capabilities\Metrics\Gauge;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class GaugeTest extends TestCase
{
    #[Test]
    public function it_starts_at_zero() : void
    {
        $gauge = new Gauge('temperature');

        self::assertSame(0.0, $gauge->getValue());
    }

    #[Test]
    public function it_has_name() : void
    {
        $gauge = new Gauge('cpu_usage_percent');

        self::assertSame('cpu_usage_percent', $gauge->name);
    }

    #[Test]
    public function it_has_default_empty_tags() : void
    {
        $gauge = new Gauge('memory');

        self::assertSame([], $gauge->tags);
    }

    #[Test]
    public function it_has_tags() : void
    {
        $gauge = new Gauge('memory', ['host' => 'server-1', 'region' => 'us-east']);

        self::assertSame(['host' => 'server-1', 'region' => 'us-east'], $gauge->tags);
    }

    #[Test]
    public function it_sets_value() : void
    {
        $gauge = new Gauge('temperature');

        $gauge->set(36.6);

        self::assertSame(36.6, $gauge->getValue());
    }

    #[Test]
    public function it_overwrites_previous_value() : void
    {
        $gauge = new Gauge('temperature');

        $gauge->set(20.0);
        $gauge->set(25.0);

        self::assertSame(25.0, $gauge->getValue());
    }

    #[Test]
    public function it_sets_negative_value() : void
    {
        $gauge = new Gauge('balance');

        $gauge->set(-100.5);

        self::assertSame(-100.5, $gauge->getValue());
    }

    #[Test]
    public function it_sets_zero_value() : void
    {
        $gauge = new Gauge('requests');
        $gauge->set(10.0);

        $gauge->set(0.0);

        self::assertSame(0.0, $gauge->getValue());
    }

    #[Test]
    public function it_sets_large_value() : void
    {
        $gauge = new Gauge('file_size');

        $gauge->set(1_073_741_824.0);

        self::assertSame(1_073_741_824.0, $gauge->getValue());
    }

    #[Test]
    public function it_sets_fractional_value() : void
    {
        $gauge = new Gauge('progress');

        $gauge->set(0.75);

        self::assertSame(0.75, $gauge->getValue());
    }

    #[Test]
    public function it_reflects_latest_value_not_accumulation() : void
    {
        $gauge = new Gauge('active_connections');

        $gauge->set(5.0);
        $gauge->set(10.0);
        $gauge->set(3.0);

        self::assertSame(3.0, $gauge->getValue());
    }

    #[Test]
    public function it_has_readonly_name() : void
    {
        $reflection = new ReflectionClass(Gauge::class);
        $nameProp   = $reflection->getProperty('name');

        self::assertTrue($nameProp->isReadOnly());
    }

    #[Test]
    public function it_has_readonly_tags() : void
    {
        $reflection = new ReflectionClass(Gauge::class);
        $tagsProp   = $reflection->getProperty('tags');

        self::assertTrue($tagsProp->isReadOnly());
    }
}
