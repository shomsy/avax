<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Unit\Cache\Foundation\Time;

use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use PHPUnit\Framework\TestCase;

final class DurationTest extends TestCase
{
    public function test_creates_from_seconds() : void
    {
        $duration = Duration::ofSeconds(seconds: 60);

        $this->assertSame(expected: 60, actual: $duration->toSeconds());
        $this->assertSame(expected: 60000, actual: $duration->toMilliseconds());
    }

    public function test_creates_from_milliseconds() : void
    {
        $duration = Duration::ofMilliseconds(milliseconds: 1500);

        $this->assertSame(expected: 1, actual: $duration->toSeconds());
        $this->assertSame(expected: 1500, actual: $duration->toMilliseconds());
    }

    public function test_adds_durations() : void
    {
        $a = Duration::ofSeconds(seconds: 30);
        $b = Duration::ofSeconds(seconds: 20);

        $result = $a->add(other: $b);

        $this->assertSame(expected: 50, actual: $result->toSeconds());
    }

    public function test_subtracts_durations() : void
    {
        $a = Duration::ofSeconds(seconds: 30);
        $b = Duration::ofSeconds(seconds: 20);

        $result = $a->subtract(other: $b);

        $this->assertSame(expected: 10, actual: $result->toSeconds());
    }

    public function test_prevents_negative_result() : void
    {
        $a = Duration::ofSeconds(seconds: 10);
        $b = Duration::ofSeconds(seconds: 20);

        $result = $a->subtract(other: $b);

        $this->assertSame(expected: 0, actual: $result->toSeconds());
    }

    public function test_is_zero() : void
    {
        $zero = Duration::ofSeconds(seconds: 0);
        $nonZero = Duration::ofSeconds(seconds: 1);

        $this->assertTrue(condition: $zero->isZero());
        $this->assertFalse(condition: $nonZero->isZero());
    }

    public function test_is_positive() : void
    {
        $positive = Duration::ofSeconds(seconds: 1);
        $zero = Duration::ofSeconds(seconds: 0);

        $this->assertTrue(condition: $positive->isPositive());
        $this->assertFalse(condition: $zero->isPositive());
    }

    public function test_multiplies_duration() : void
    {
        $duration = Duration::ofSeconds(seconds: 10);
        $result = $duration->multiply(factor: 3);

        $this->assertSame(expected: 30, actual: $result->toSeconds());
    }
}
