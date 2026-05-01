<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Foundation\Time;

use Avax\Framework\System\Foundation\Time\Clock;
use Avax\Framework\System\Foundation\Time\FrozenClock;
use Avax\Framework\System\Foundation\Time\SystemClock;
use Avax\Tests\Framework\TestCase;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(SystemClock::class)]
#[CoversClass(FrozenClock::class)]
final class ClockTest extends TestCase
{
    #[Test]
    public function it_implements_clock_interface(): void
    {
        $clock = new SystemClock;

        self::assertInstanceOf(Clock::class, $clock);
    }

    #[Test]
    public function it_returns_current_time(): void
    {
        $clock  = new SystemClock;
        $before = new DateTimeImmutable;

        $now = $clock->now();

        $after = new DateTimeImmutable;

        self::assertInstanceOf(DateTimeImmutable::class, $now);
        self::assertGreaterThanOrEqual($before, $now);
        self::assertLessThanOrEqual($after, $now);
    }

    #[Test]
    public function it_returns_frozen_time(): void
    {
        $frozen = new DateTimeImmutable('2026-01-01 12:00:00');
        $clock = new FrozenClock(frozenTime: $frozen);

        self::assertSame($frozen, $clock->now());
    }

    #[Test]
    public function it_creates_new_frozen_clock_with_different_time(): void
    {
        $original = new DateTimeImmutable('2026-01-01 12:00:00');
        $new = new DateTimeImmutable('2026-06-15 18:30:00');

        $clock = new FrozenClock(frozenTime: $original);
        $newClock = $clock->withFrozenTime(newTime: $new);

        self::assertSame($original, $clock->now());
        self::assertSame($new, $newClock->now());
    }
}
