<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\DateTime;

use Avax\Components\Application\DateTime\System\Capabilities\Duration\Duration;
use Avax\Components\Application\DateTime\System\Capabilities\Duration\DurationUnit;
use Avax\Components\Application\DateTime\System\Capabilities\Timezone\UtcTimezone;
use Avax\Components\Application\DateTime\System\Flows\Diff\DiffDates;
use Avax\Components\Application\DateTime\System\Flows\Format\FormatDate;
use Avax\Components\Application\DateTime\System\PublicSurface\Clock;
use Avax\Components\Application\DateTime\System\PublicSurface\SystemClock;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

final class DateTimeCapabilitiesTest extends TestCase
{
    public function testSystemClockCreate() : void
    {
        $dt = SystemClock::create();
        $this->assertInstanceOf(DateTimeImmutable::class, $dt);
    }

    public function testSystemClockWithTimezone() : void
    {
        $dt = SystemClock::create('UTC');
        $this->assertSame('UTC', $dt->getTimezone()->getName());
    }

    public function testSystemClockFreezeAndUnfreeze() : void
    {
        $frozen = new DateTimeImmutable('2024-01-01 12:00:00');
        SystemClock::freeze($frozen);

        $this->assertTrue(SystemClock::isFrozen());
        $this->assertSame('2024-01-01', SystemClock::create()->format('Y-m-d'));

        SystemClock::unfreeze();
        $this->assertFalse(SystemClock::isFrozen());
    }

    public function testClockNow() : void
    {
        $now = Clock::now();
        $this->assertInstanceOf(DateTimeImmutable::class, $now);
    }

    public function testClockToday() : void
    {
        $today = Clock::today();
        $this->assertInstanceOf(DateTimeImmutable::class, $today);
        $this->assertSame('00:00:00', $today->format('H:i:s'));
    }

    public function testClockTomorrow() : void
    {
        $tomorrow = Clock::tomorrow();
        $this->assertInstanceOf(DateTimeImmutable::class, $tomorrow);
    }

    public function testClockYesterday() : void
    {
        $yesterday = Clock::yesterday();
        $this->assertInstanceOf(DateTimeImmutable::class, $yesterday);
    }

    public function testDurationSeconds() : void
    {
        $seconds = Duration::seconds(60);
        $this->assertSame(60, $seconds->inSeconds());
    }

    public function testDurationMinutes() : void
    {
        $minutes = Duration::minutes(2);
        $this->assertSame(120, $minutes->inSeconds());
    }

    public function testDurationHours() : void
    {
        $hours = Duration::hours(1);
        $this->assertSame(3600, $hours->inSeconds());
    }

    public function testDurationDays() : void
    {
        $days = Duration::days(1);
        $this->assertSame(86400, $days->inSeconds());
    }

    public function testDurationWeeks() : void
    {
        $weeks = Duration::weeks(1);
        $this->assertSame(604800, $weeks->inSeconds());
    }

    public function testDurationAdd() : void
    {
        $one      = Duration::days(1);
        $two      = Duration::days(2);
        $combined = $one->add($two);

        $this->assertSame(259200, $combined->inSeconds());
    }

    public function testDurationSubtract() : void
    {
        $three    = Duration::days(3);
        $one      = Duration::days(1);
        $combined = $three->subtract($one);

        $this->assertSame(172800, $combined->inSeconds());
    }

    public function testDurationIsZero() : void
    {
        $zero = Duration::seconds(0);
        $this->assertTrue($zero->isZero());
    }

    public function testDurationIsNegative() : void
    {
        $neg = Duration::seconds(-5);
        $this->assertTrue($neg->isNegative());
    }

    public function testDiffDates() : void
    {
        $from = new DateTimeImmutable('2024-01-01');
        $to   = new DateTimeImmutable('2024-01-05');
        $diff = new DiffDates();

        $result = $diff->inDays($from, $to);

        $this->assertSame(4, $result);
    }

    public function testDiffDatesNegative() : void
    {
        $from = new DateTimeImmutable('2024-01-05');
        $to   = new DateTimeImmutable('2024-01-01');
        $diff = new DiffDates();

        $result = $diff->inDays($from, $to);

        $this->assertSame(-4, $result);
    }

    public function testFormatDate() : void
    {
        $date      = new DateTimeImmutable('2024-01-15');
        $formatter = new FormatDate();

        $formatted = $formatter->format($date, 'Y-m-d');

        $this->assertSame('2024-01-15', $formatted);
    }

    public function testFormatDateHuman() : void
    {
        $date      = new DateTimeImmutable('2024-01-15');
        $formatter = new FormatDate();

        $formatted = $formatter->format($date, 'F j, Y');

        $this->assertSame('January 15, 2024', $formatted);
    }

    public function testUtcTimezone() : void
    {
        $utc = new UtcTimezone();
        $this->assertInstanceOf(DateTimeZone::class, $utc->toPhpTimezone());
        $this->assertTrue($utc->isUtc());
        $this->assertSame('UTC', $utc->getName());
    }

    protected function setUp() : void
    {
        parent::setUp();
        SystemClock::unfreeze();
    }

    protected function tearDown() : void
    {
        parent::tearDown();
        SystemClock::unfreeze();
    }
}