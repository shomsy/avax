<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\Tests\Unit\Cache\Foundation\Time;

use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use PHPUnit\Framework\TestCase;

final class TimestampTest extends TestCase
{
    public function test_creates_from_unix_time() : void
    {
        $timestamp = Timestamp::fromUnixTime(1700000000);

        $this->assertSame(1700000000, $timestamp->toUnixTime());
    }

    public function test_calculates_difference() : void
    {
        $timestamp = Timestamp::fromUnixTime(100);
        $b         = Timestamp::fromUnixTime(50);

        $duration = $timestamp->difference($b);

        $this->assertSame(50, $duration->toSeconds());
    }

    public function test_adds_duration() : void
    {
        $timestamp = Timestamp::fromUnixTime(100);
        $duration  = Duration::ofSeconds(50);

        $result = $timestamp->add($duration);

        $this->assertSame(150, $result->toUnixTime());
    }

    public function test_subtracts_duration() : void
    {
        $timestamp = Timestamp::fromUnixTime(100);
        $duration  = Duration::ofSeconds(30);

        $result = $timestamp->subtract($duration);

        $this->assertSame(70, $result->toUnixTime());
    }

    public function test_compares_timestamps() : void
    {
        $timestamp = Timestamp::fromUnixTime(timestamp: 100);
        $b = Timestamp::fromUnixTime(timestamp: 50);
        $c = Timestamp::fromUnixTime(timestamp: 100);

        $this->assertTrue(condition: $timestamp->isAfter(other: $b));
        $this->assertTrue(condition: $b->isBefore(other: $timestamp));
        $this->assertFalse(condition: $timestamp->isAfter(other: $c));
        $this->assertFalse(condition: $timestamp->isBefore(other: $c));
    }
}
