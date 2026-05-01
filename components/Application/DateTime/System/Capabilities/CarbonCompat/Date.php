<?php

declare(strict_types=1);

namespace Avax\Components\Application\DateTime\System\Capabilities\CarbonCompat;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use Override;

/**
 * Carbon-compatible Date class.
 * Provides a fluent interface for date/time manipulation.
 */
class Date extends DateTimeImmutable
{
    /**
     * Create from a Unix timestamp.
     */
    public static function fromTimestamp(int $timestamp, DateTimeZone|string|null $tz = null) : self
    {
        return self::parse('@' . $timestamp, $tz);
    }

    /**
     * Create a new Date instance from a string.
     */
    public static function parse(string $datetime, DateTimeZone|string|null $tz = null) : self
    {
        $dateTimeZone = self::resolveTimezone($tz);

        try {
            return new self($datetime, $dateTimeZone);
        } catch (Exception $exception) {
            throw new InvalidArgumentException('Failed to parse date: ' . $datetime, 0, $exception);
        }
    }

    private static function resolveTimezone(DateTimeZone|string|null $tz) : DateTimeZone
    {
        if ($tz instanceof DateTimeZone) {
            return $tz;
        }

        if ($tz !== null) {
            return new DateTimeZone($tz);
        }

        $defaultTz = date_default_timezone_get();

        return new DateTimeZone($defaultTz !== '' && $defaultTz !== '0' ? $defaultTz : 'UTC');
    }

    /**
     * Create from year, month, day.
     */
    public static function create(int $year, int $month = 1, int $day = 1, DateTimeZone|string|null $tz = null) : self
    {
        return self::parse(sprintf('%d-%d-%d', $year, $month, $day), $tz);
    }

    /**
     * Create from year, month, day, hour, minute, second.
     */
    public static function createFromDateTime(
        int                      $year,
        int                      $month = 1,
        int                      $day = 1,
        int                      $hour = 0,
        int                      $minute = 0,
        int                      $second = 0,
        DateTimeZone|string|null $tz = null,
    ) : self
    {
        return self::parse(sprintf('%d-%d-%d %d:%d:%d', $year, $month, $day, $hour, $minute, $second), $tz);
    }

    /**
     * Get a human-readable difference (e.g., "2 hours ago", "3 days from now").
     */
    public function diffForHumans(self|DateTimeImmutable|null $other = null) : string
    {
        $other ??= self::now($this->getTimezone());

        $diff = $this->diff($other);
        $isPast = $other > $this;

        $units = [
            'year'  => $diff->y,
            'month' => $diff->m,
            'day'   => $diff->d,
            'hour'  => $diff->h,
            'minute' => $diff->i,
            'second' => $diff->s,
        ];

        // Find the first non-zero unit
        foreach ($units as $unit => $value) {
            if ($value > 0) {
                $suffix = $isPast ? ' ago' : ' from now';
                $label = $value === 1 ? $unit : $unit . 's';

                return sprintf('%d %s%s', $value, $label, $suffix);
            }
        }

        return 'just now';
    }

    /**
     * Create a new Date instance for now.
     */
    public static function now(DateTimeZone|string|null $tz = null) : self
    {
        $dateTimeZone = self::resolveTimezone($tz);

        return new self('now', $dateTimeZone);
    }

    /**
     * Add hours to the date.
     */
    public function addHours(int $hours) : self
    {
        return $this->add(new DateInterval(sprintf('PT%dH', $hours)));
    }

    /**
     * Subtract hours from the date.
     */
    public function subHours(int $hours) : self
    {
        return $this->sub(new DateInterval(sprintf('PT%dH', $hours)));
    }

    /**
     * Add minutes to the date.
     */
    public function addMinutes(int $minutes) : self
    {
        return $this->add(new DateInterval(sprintf('PT%dM', $minutes)));
    }

    /**
     * Subtract minutes from the date.
     */
    public function subMinutes(int $minutes) : self
    {
        return $this->sub(new DateInterval(sprintf('PT%dM', $minutes)));
    }

    /**
     * Add seconds to the date.
     */
    public function addSeconds(int $seconds) : self
    {
        return $this->add(new DateInterval(sprintf('PT%dS', $seconds)));
    }

    /**
     * Subtract seconds from the date.
     */
    public function subSeconds(int $seconds) : self
    {
        return $this->sub(new DateInterval(sprintf('PT%dS', $seconds)));
    }

    /**
     * Add weeks to the date.
     */
    public function addWeeks(int $weeks) : self
    {
        return $this->add(new DateInterval(sprintf('P%dW', $weeks)));
    }

    /**
     * Subtract weeks from the date.
     */
    public function subWeeks(int $weeks) : self
    {
        return $this->sub(new DateInterval(sprintf('P%dW', $weeks)));
    }

    /**
     * Add months to the date.
     */
    public function addMonths(int $months) : self
    {
        return $this->add(new DateInterval(sprintf('P%dM', $months)));
    }

    /**
     * Subtract months from the date.
     */
    public function subMonths(int $months) : self
    {
        return $this->sub(new DateInterval(sprintf('P%dM', $months)));
    }

    /**
     * Add years to the date.
     */
    public function addYears(int $years) : self
    {
        return $this->add(new DateInterval(sprintf('P%dY', $years)));
    }

    /**
     * Subtract years from the date.
     */
    public function subYears(int $years) : self
    {
        return $this->sub(new DateInterval(sprintf('P%dY', $years)));
    }

    /**
     * Set the timezone.
     */
    public function timezone(DateTimeZone|string $tz) : self
    {
        return $this->setTimezone(self::resolveTimezone($tz));
    }

    /**
     * Get the timezone name.
     */
    public function timezoneName() : string
    {
        return $this->getTimezone()->getName();
    }

    /**
     * Check if the date is today.
     */
    public function isToday() : bool
    {
        return $this->format('Y-m-d') === self::now($this->getTimezone())->format('Y-m-d');
    }

    /**
     * Format the date using a format string.
     */
    #[Override]
    public function format(string $format = 'Y-m-d H:i:s') : string
    {
        return parent::format($format);
    }

    /**
     * Check if the date is in the past.
     */
    public function isPast() : bool
    {
        return $this < self::now($this->getTimezone());
    }

    /**
     * Check if the date is in the future.
     */
    public function isFuture() : bool
    {
        return $this > self::now($this->getTimezone());
    }

    /**
     * Get the start of the week (Monday).
     */
    public function startOfWeek() : self
    {
        $dayOfWeek = (int) $this->format('N');

        return $this->subDays($dayOfWeek - 1)->startOfDay();
    }

    /**
     * Get the start of the day.
     */
    public function startOfDay() : self
    {
        return $this->setTime(0, 0, 0);
    }

    /**
     * Subtract days from the date.
     */
    public function subDays(int $days) : self
    {
        return $this->sub(new DateInterval(sprintf('P%dD', $days)));
    }

    /**
     * Get the end of the week (Sunday).
     */
    public function endOfWeek() : self
    {
        $dayOfWeek = (int) $this->format('N');

        return $this->addDays(7 - $dayOfWeek)->endOfDay();
    }

    /**
     * Get the end of the day.
     */
    public function endOfDay() : self
    {
        return $this->setTime(23, 59, 59);
    }

    /**
     * Add days to the date.
     */
    public function addDays(int $days) : self
    {
        return $this->add(new DateInterval(sprintf('P%dD', $days)));
    }

    /**
     * Get the start of the month.
     */
    public function startOfMonth() : self
    {
        return self::parse($this->format('Y-m-01'), $this->getTimezone())->startOfDay();
    }

    /**
     * Get the end of the month.
     */
    public function endOfMonth() : self
    {
        return self::parse($this->format('Y-m-t'), $this->getTimezone())->endOfDay();
    }

    /**
     * Get the Unix timestamp.
     */
    public function timestamp() : int
    {
        return (int) $this->format('U');
    }

    /**
     * Convert to ISO 8601 string.
     */
    public function toIso8601String() : string
    {
        return $this->format('c');
    }

    /**
     * Convert to date string (Y-m-d).
     */
    public function toDateString() : string
    {
        return $this->format('Y-m-d');
    }

    /**
     * Convert to time string (H:i:s).
     */
    public function toTimeString() : string
    {
        return $this->format('H:i:s');
    }

    /**
     * Convert to date-time string (Y-m-d H:i:s).
     */
    public function toDateTimeString() : string
    {
        return $this->format('Y-m-d H:i:s');
    }

    /**
     * Check if the date is the same as another.
     */
    public function isSameDay(self|DateTimeImmutable $other) : bool
    {
        return $this->format('Y-m-d') === $other->format('Y-m-d');
    }

    /**
     * Get the day of the week (1-7, Monday is 1).
     */
    public function dayOfWeek() : int
    {
        return (int) $this->format('N');
    }

    /**
     * Get the day of the month.
     */
    public function day() : int
    {
        return (int) $this->format('j');
    }

    /**
     * Get the month.
     */
    public function month() : int
    {
        return (int) $this->format('n');
    }

    /**
     * Get the year.
     */
    public function year() : int
    {
        return (int) $this->format('Y');
    }

    /**
     * Get the hour.
     */
    public function hour() : int
    {
        return (int) $this->format('G');
    }

    /**
     * Get the minute.
     */
    public function minute() : int
    {
        return (int) $this->format('i');
    }

    /**
     * Get the second.
     */
    public function second() : int
    {
        return (int) $this->format('s');
    }

    /**
     * Copy the date instance.
     */
    public function copy() : self
    {
        return new self($this->format('Y-m-d H:i:s.u'), $this->getTimezone());
    }
}
