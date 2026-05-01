<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Unit\Cache\Foundation\Time;

use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use PHPUnit\Framework\TestCase;

final class TimestampTest extends TestCase
{
    public function test_creates_from_unix_time() : void
    {
        $timestamp = Timestamp::fromUnixTime(timestamp: 1700000000);

        $this->assertSame(expected: 1700000000, actual: $timestamp->toUnixTime());
    }

    public function test_calculates_difference() : void
    {
        $a = Timestamp::fromUnixTime(timestamp: 100);
        $b = Timestamp::fromUnixTime(timestamp: 50);

        $diff = $a->difference(other: $b);

        $this->assertSame(expected: 50, actual: $diff->toSeconds());
    }

    public function test_adds_duration() : void
    {
        $timestamp = Timestamp::fromUnixTime(timestamp: 100);
        $duration  = Duration::ofSeconds(seconds: 50);

        $result = $timestamp->add(duration: $duration);

        $this->assertSame(expected: 150, actual: $result->toUnixTime());
    }

    public function test_subtracts_duration() : void
    {
        $timestamp = Timestamp::fromUnixTime(timestamp: 100);
        $duration  = Duration::ofSeconds(seconds: 30);

        $result = $timestamp->subtract(duration: $duration);

        $this->assertSame(expected: 70, actual: $result->toUnixTime());
    }

    public function test_compares_timestamps() : void
    {
        $a = Timestamp::fromUnixTime(timestamp: 100);
        $b = Timestamp::fromUnixTime(timestamp: 50);
        $c = Timestamp::fromUnixTime(timestamp: 100);

        $this->assertTrue(condition: $a->isAfter(other: $b));
        $this->assertTrue(condition: $b->isBefore(other: $a));
        $this->assertFalse(condition: $a->isAfter(other: $c));
        $this->assertFalse(condition: $a->isBefore(other: $c));
    }
}
