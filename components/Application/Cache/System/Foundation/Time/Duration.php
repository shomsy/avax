<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Foundation\Time;

use DateInterval;
use InvalidArgumentException;
use Override;
use Stringable;

final readonly class Duration implements Stringable
{
    public const int MICROSECONDS_PER_SECOND = 1_000_000;

    public const int MILLISECONDS_PER_SECOND = 1000;

    public const int NANOSECONDS_PER_SECOND = 1_000_000_000;

    public const int NANOSECONDS_PER_MILLISECOND = 1_000_000;

    public const int NANOSECONDS_PER_MICROSECOND = 1000;

    public function __construct(
        public int $seconds,
        public int $nanoseconds = 0,
    ) {
        if ($nanoseconds < 0 || $nanoseconds >= self::NANOSECONDS_PER_SECOND) {
            throw new InvalidArgumentException(
                message: 'Nanoseconds must be between 0 and 999,999,999',
            );
        }
    }

    public static function ofSeconds(int $seconds): self
    {
        return new self(seconds: $seconds);
    }

    public static function ofMilliseconds(int $milliseconds): self
    {
        $seconds = (int) floor($milliseconds / self::MILLISECONDS_PER_SECOND);
        $nanos = ($milliseconds % self::MILLISECONDS_PER_SECOND) * self::NANOSECONDS_PER_MILLISECOND;

        return new self(seconds: $seconds, nanoseconds: $nanos);
    }

    public static function ofMicroseconds(int $microseconds): self
    {
        $seconds = (int) floor($microseconds / self::MICROSECONDS_PER_SECOND);
        $nanos = ($microseconds % self::MICROSECONDS_PER_SECOND) * self::NANOSECONDS_PER_MICROSECOND;

        return new self(seconds: $seconds, nanoseconds: $nanos);
    }

    public static function fromDateInterval(DateInterval $dateInterval): self
    {
        $days = $dateInterval->days === false ? $dateInterval->d : $dateInterval->days;
        $seconds = $dateInterval->s + ($dateInterval->i * 60) + ($dateInterval->h * 3600) + ($days * 86400);
        $nanos = (int) round($dateInterval->f * self::NANOSECONDS_PER_SECOND);

        return new self(seconds: $seconds, nanoseconds: $nanos);
    }

    public function toSeconds(): int
    {
        return $this->seconds;
    }

    public function toMilliseconds(): int
    {
        return ($this->seconds * self::MILLISECONDS_PER_SECOND)
            + (int) round($this->nanoseconds / self::NANOSECONDS_PER_MILLISECOND);
    }

    public function toMicroseconds(): int
    {
        return ($this->seconds * self::MICROSECONDS_PER_SECOND)
            + (int) round($this->nanoseconds / self::NANOSECONDS_PER_MICROSECOND);
    }

    public function toDateInterval(): DateInterval
    {
        $dateInterval = new DateInterval(duration: 'P0DT0H0M0S');
        $dateInterval->s = $this->seconds % 60;
        $dateInterval->i = (int) floor($this->seconds / 60) % 60;
        $dateInterval->h = (int) floor($this->seconds / 3600) % 24;
        $dateInterval->d = (int) floor($this->seconds / 86400);
        $dateInterval->f = $this->nanoseconds / self::NANOSECONDS_PER_SECOND;

        return $dateInterval;
    }

    public function isZero(): bool
    {
        return $this->seconds === 0 && $this->nanoseconds === 0;
    }

    public function isPositive(): bool
    {
        return $this->seconds > 0 || $this->nanoseconds > 0;
    }

    public function add(self $other): self
    {
        $totalSeconds = $this->seconds + $other->seconds;
        $totalNanos = $this->nanoseconds + $other->nanoseconds;

        if ($totalNanos >= self::NANOSECONDS_PER_SECOND) {
            $totalSeconds++;
            $totalNanos -= self::NANOSECONDS_PER_SECOND;
        }

        return new self(seconds: $totalSeconds, nanoseconds: $totalNanos);
    }

    public function subtract(self $other): self
    {
        $totalSeconds = $this->seconds - $other->seconds;
        $totalNanos = $this->nanoseconds - $other->nanoseconds;

        if ($totalNanos < 0) {
            $totalSeconds--;
            $totalNanos += self::NANOSECONDS_PER_SECOND;
        }

        if ($totalSeconds < 0) {
            return new self(seconds: 0, nanoseconds: 0);
        }

        return new self(seconds: $totalSeconds, nanoseconds: $totalNanos);
    }

    public function multiply(int $factor): self
    {
        $totalNanos = $this->nanoseconds * $factor;
        $totalSeconds = ($this->seconds * $factor) + (int) floor($totalNanos / self::NANOSECONDS_PER_SECOND);
        $totalNanos %= self::NANOSECONDS_PER_SECOND;

        return new self(seconds: $totalSeconds, nanoseconds: $totalNanos);
    }

    #[Override]
    public function __toString(): string
    {
        return sprintf('%d.%09d seconds', $this->seconds, $this->nanoseconds);
    }
}
