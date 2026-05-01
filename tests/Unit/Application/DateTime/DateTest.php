<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Application\DateTime;

use Avax\Components\Application\DateTime\System\Capabilities\CarbonCompat\Date;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Date class.
 */
final class DateTest extends TestCase
{
    #[Test]
    public function now_creates_current_datetime(): void
    {
        $now = Date::now();

        $this->assertInstanceOf(Date::class, $now);
        $this->assertLessThanOrEqual(2, abs(time() - $now->timestamp()));
    }

    #[Test]
    public function now_accepts_timezone(): void
    {
        $utc = Date::now(tz: 'UTC');
        $ny  = Date::now(tz: 'America/New_York');

        $this->assertEquals('UTC', $utc->timezoneName());
        $this->assertEquals('America/New_York', $ny->timezoneName());
    }

    #[Test]
    public function parse_creates_date_from_string(): void
    {
        $date = Date::parse('2024-06-15 10:30:00');

        $this->assertEquals(2024, $date->year());
        $this->assertEquals(6, $date->month());
        $this->assertEquals(15, $date->day());
        $this->assertEquals(10, $date->hour());
    }

    #[Test]
    public function from_timestamp_creates_date_from_unix_timestamp(): void
    {
        $timestamp = 1700000000;
        $date      = Date::fromTimestamp($timestamp);

        $this->assertEquals($timestamp, $date->timestamp());
    }

    #[Test]
    public function create_makes_date_from_components(): void
    {
        $date = Date::create(year: 2024, month: 12, day: 25);

        $this->assertEquals(2024, $date->year());
        $this->assertEquals(12, $date->month());
        $this->assertEquals(25, $date->day());
    }

    #[Test]
    public function diff_for_humans_returns_past_difference(): void
    {
        $past = Date::parse('2020-01-01');
        $diff = $past->diffForHumans(other: Date::parse('2023-01-01'));

        $this->assertStringContainsString('ago', $diff);
    }

    #[Test]
    public function diff_for_humans_returns_future_difference(): void
    {
        $future = Date::parse('2030-01-01');
        $diff   = $future->diffForHumans(other: Date::parse('2023-01-01'));

        $this->assertStringContainsString('from now', $diff);
    }

    #[Test]
    public function add_days_adds_specified_days(): void
    {
        $date    = Date::parse('2024-01-01');
        $newDate = $date->addDays(10);

        $this->assertEquals(11, $newDate->day());
    }

    #[Test]
    public function sub_days_subtracts_specified_days(): void
    {
        $date    = Date::parse('2024-01-15');
        $newDate = $date->subDays(10);

        $this->assertEquals(5, $newDate->day());
    }

    #[Test]
    public function add_hours_adds_specified_hours(): void
    {
        $date    = Date::parse('2024-01-01 10:00:00');
        $newDate = $date->addHours(5);

        $this->assertEquals(15, $newDate->hour());
    }

    #[Test]
    public function add_minutes_adds_specified_minutes(): void
    {
        $date    = Date::parse('2024-01-01 10:30:00');
        $newDate = $date->addMinutes(45);

        $this->assertEquals(15, $newDate->minute());
    }

    #[Test]
    public function add_seconds_adds_specified_seconds(): void
    {
        $date    = Date::parse('2024-01-01 10:00:30');
        $newDate = $date->addSeconds(45);

        $this->assertEquals(15, $newDate->second());
    }

    #[Test]
    public function add_weeks_adds_specified_weeks(): void
    {
        $date    = Date::parse('2024-01-01');
        $newDate = $date->addWeeks(2);

        $this->assertEquals(15, $newDate->day());
    }

    #[Test]
    public function add_months_adds_specified_months(): void
    {
        $date    = Date::parse('2024-01-15');
        $newDate = $date->addMonths(3);

        $this->assertEquals(4, $newDate->month());
    }

    #[Test]
    public function add_years_adds_specified_years(): void
    {
        $date    = Date::parse('2024-06-15');
        $newDate = $date->addYears(5);

        $this->assertEquals(2029, $newDate->year());
    }

    #[Test]
    public function is_today_returns_true_for_current_date(): void
    {
        $today = Date::now();
        $this->assertTrue($today->isToday());
    }

    #[Test]
    public function is_past_returns_true_for_past_date(): void
    {
        $past = Date::parse('2000-01-01');
        $this->assertTrue($past->isPast());
    }

    #[Test]
    public function is_future_returns_true_for_future_date(): void
    {
        $future = Date::parse('2100-01-01');
        $this->assertTrue($future->isFuture());
    }

    #[Test]
    public function start_of_day_returns_midnight(): void
    {
        $date       = Date::parse('2024-06-15 14:30:45');
        $startOfDay = $date->startOfDay();

        $this->assertEquals(0, $startOfDay->hour());
        $this->assertEquals(0, $startOfDay->minute());
        $this->assertEquals(0, $startOfDay->second());
    }

    #[Test]
    public function end_of_day_returns_last_second(): void
    {
        $date     = Date::parse('2024-06-15 14:30:45');
        $endOfDay = $date->endOfDay();

        $this->assertEquals(23, $endOfDay->hour());
        $this->assertEquals(59, $endOfDay->minute());
        $this->assertEquals(59, $endOfDay->second());
    }

    #[Test]
    public function to_date_string_returns_y_m_d_format(): void
    {
        $date = Date::parse('2024-06-15 10:30:00');
        $this->assertEquals('2024-06-15', $date->toDateString());
    }

    #[Test]
    public function to_time_string_returns_h_i_s_format(): void
    {
        $date = Date::parse('2024-06-15 10:30:45');
        $this->assertEquals('10:30:45', $date->toTimeString());
    }

    #[Test]
    public function to_date_time_string_returns_full_format(): void
    {
        $date = Date::parse('2024-06-15 10:30:45');
        $this->assertEquals('2024-06-15 10:30:45', $date->toDateTimeString());
    }

    #[Test]
    public function to_iso8601_string_returns_iso_format(): void
    {
        $date = Date::parse('2024-06-15 10:30:00', tz: 'UTC');
        $iso  = $date->toIso8601String();

        $this->assertStringContainsString('2024-06-15', $iso);
    }

    #[Test]
    public function is_same_day_compares_dates(): void
    {
        $date1 = Date::parse('2024-06-15 10:00:00');
        $date2 = Date::parse('2024-06-15 18:00:00');
        $date3 = Date::parse('2024-06-16 10:00:00');

        $this->assertTrue($date1->isSameDay($date2));
        $this->assertFalse($date1->isSameDay($date3));
    }

    #[Test]
    public function copy_creates_independent_clone(): void
    {
        $date = Date::parse('2024-06-15');
        $copy = $date->copy();

        $this->assertEquals($date->toDateString(), $copy->toDateString());
        $this->assertNotSame($date, $copy);
    }

    #[Test]
    public function start_of_week_returns_monday(): void
    {
        // Wednesday June 19, 2024
        $date        = Date::parse('2024-06-19');
        $startOfWeek = $date->startOfWeek();

        $this->assertEquals(17, $startOfWeek->day()); // Monday
    }

    #[Test]
    public function end_of_week_returns_sunday(): void
    {
        // Wednesday June 19, 2024
        $date      = Date::parse('2024-06-19');
        $endOfWeek = $date->endOfWeek();

        $this->assertEquals(23, $endOfWeek->day()); // Sunday
    }

    #[Test]
    public function start_of_month_returns_first_day(): void
    {
        $date         = Date::parse('2024-06-15');
        $startOfMonth = $date->startOfMonth();

        $this->assertEquals(1, $startOfMonth->day());
    }

    #[Test]
    public function end_of_month_returns_last_day(): void
    {
        $date       = Date::parse('2024-06-15');
        $endOfMonth = $date->endOfMonth();

        $this->assertEquals(30, $endOfMonth->day()); // June has 30 days
    }

    #[Test]
    public function timestamp_returns_unix_timestamp(): void
    {
        $date = Date::fromTimestamp(1700000000);
        $this->assertEquals(1700000000, $date->timestamp());
    }

    #[Test]
    public function timezone_changes_timezone(): void
    {
        $date   = Date::parse('2024-06-15 12:00:00', tz: 'UTC');
        $nyDate = $date->timezone('America/New_York');

        $this->assertEquals('America/New_York', $nyDate->timezoneName());
    }
}
