<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\tests\Unit\Cache\Foundation\Time;

use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use PHPUnit\Framework\TestCase;

final class DurationTest extends TestCase
{
    public function test_creates_from_seconds(): void
    {
        $duration = Duration::ofSeconds(60);

        $this->assertSame(60, $duration->toSeconds());
        $this->assertSame(60000, $duration->toMilliseconds());
    }

    public function test_creates_from_milliseconds(): void
    {
        $duration = Duration::ofMilliseconds(1500);

        $this->assertSame(1, $duration->toSeconds());
        $this->assertSame(1500, $duration->toMilliseconds());
    }

    public function test_adds_durations(): void
    {
        $duration = Duration::ofSeconds(30);
        $b        = Duration::ofSeconds(20);

        $result = $duration->add($b);

        $this->assertSame(50, $result->toSeconds());
    }

    public function test_subtracts_durations(): void
    {
        $duration = Duration::ofSeconds(30);
        $b        = Duration::ofSeconds(20);

        $result = $duration->subtract($b);

        $this->assertSame(10, $result->toSeconds());
    }

    public function test_prevents_negative_result(): void
    {
        $duration = Duration::ofSeconds(10);
        $b        = Duration::ofSeconds(20);

        $result = $duration->subtract($b);

        $this->assertSame(0, $result->toSeconds());
    }

    public function test_is_zero(): void
    {
        $duration = Duration::ofSeconds(0);
        $nonZero  = Duration::ofSeconds(1);

        $this->assertTrue($duration->isZero());
        $this->assertFalse($nonZero->isZero());
    }

    public function test_is_positive(): void
    {
        $duration = Duration::ofSeconds(1);
        $zero     = Duration::ofSeconds(0);

        $this->assertTrue($duration->isPositive());
        $this->assertFalse($zero->isPositive());
    }

    public function test_multiplies_duration(): void
    {
        $duration = Duration::ofSeconds(10);
        $result   = $duration->multiply(3);

        $this->assertSame(30, $result->toSeconds());
    }
}
