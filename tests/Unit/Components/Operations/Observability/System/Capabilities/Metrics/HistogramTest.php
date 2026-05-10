<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Observability\System\Capabilities\Metrics;

use Avax\Components\Operations\Observability\System\Capabilities\Metrics\Histogram;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class HistogramTest extends TestCase
{
    #[Test]
    public function it_has_name() : void
    {
        $histogram = new Histogram('response_time_ms');

        self::assertSame('response_time_ms', $histogram->name);
    }

    #[Test]
    public function it_has_default_empty_tags() : void
    {
        $histogram = new Histogram('response_time_ms');

        self::assertSame([], $histogram->tags);
    }

    #[Test]
    public function it_has_tags() : void
    {
        $histogram = new Histogram('response_time_ms', ['endpoint' => '/api/users']);

        self::assertSame(['endpoint' => '/api/users'], $histogram->tags);
    }

    #[Test]
    public function it_starts_with_zero_count() : void
    {
        $histogram = new Histogram('latency');

        self::assertSame(0, $histogram->count());
    }

    #[Test]
    public function it_records_single_value() : void
    {
        $histogram = new Histogram('latency');

        $histogram->record(100.0);

        self::assertSame(1, $histogram->count());
    }

    #[Test]
    public function it_records_multiple_values() : void
    {
        $histogram = new Histogram('latency');

        $histogram->record(10.0);
        $histogram->record(20.0);
        $histogram->record(30.0);

        self::assertSame(3, $histogram->count());
    }

    #[Test]
    public function it_calculates_average() : void
    {
        $histogram = new Histogram('latency');

        $histogram->record(10.0);
        $histogram->record(20.0);
        $histogram->record(30.0);

        self::assertSame(20.0, $histogram->avg());
    }

    #[Test]
    public function it_returns_zero_average_when_empty() : void
    {
        $histogram = new Histogram('latency');

        self::assertSame(0.0, $histogram->avg());
    }

    #[Test]
    public function it_handles_single_value_average() : void
    {
        $histogram = new Histogram('latency');

        $histogram->record(42.0);

        self::assertSame(42.0, $histogram->avg());
    }

    #[Test]
    public function it_handles_fractional_values() : void
    {
        $histogram = new Histogram('latency');

        $histogram->record(1.5);
        $histogram->record(2.5);

        self::assertSame(2.0, $histogram->avg());
    }

    #[Test]
    public function it_handles_negative_values() : void
    {
        $histogram = new Histogram('temperature_change');

        $histogram->record(-5.0);
        $histogram->record(5.0);

        self::assertSame(0.0, $histogram->avg());
        self::assertSame(2, $histogram->count());
    }

    #[Test]
    public function it_handles_zero_values() : void
    {
        $histogram = new Histogram('latency');

        $histogram->record(0.0);
        $histogram->record(0.0);

        self::assertSame(2, $histogram->count());
        self::assertSame(0.0, $histogram->avg());
    }

    #[Test]
    public function it_accumulates_all_recorded_values() : void
    {
        $histogram = new Histogram('latency');

        for ($i = 1; $i <= 10; $i++) {
            $histogram->record((float) $i);
        }

        self::assertSame(10, $histogram->count());
        self::assertSame(5.5, $histogram->avg());
    }

    #[Test]
    public function it_has_readonly_name() : void
    {
        $reflection = new ReflectionClass(Histogram::class);
        $nameProp   = $reflection->getProperty('name');

        self::assertTrue($nameProp->isReadOnly());
    }

    #[Test]
    public function it_has_readonly_tags() : void
    {
        $reflection = new ReflectionClass(Histogram::class);
        $tagsProp   = $reflection->getProperty('tags');

        self::assertTrue($tagsProp->isReadOnly());
    }

    #[Test]
    public function it_handles_very_large_values() : void
    {
        $histogram = new Histogram('file_size');

        $histogram->record(1_000_000_000.0);
        $histogram->record(2_000_000_000.0);

        self::assertSame(1_500_000_000.0, $histogram->avg());
    }
}
