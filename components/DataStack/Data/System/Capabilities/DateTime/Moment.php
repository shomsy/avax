<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\DateTime;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Throwable;

/**
 * Immutable date-time wrapper (Carbon-like).
 * Part of the 'Data' stack for enterprise-grade convenience.
 */
final readonly class Moment
{
    private DateTimeImmutable $dateTimeImmutable;

    public function __construct(string|DateTimeImmutable $time = 'now', string|DateTimeZone|null $timezone = null)
    {
        if ($timezone instanceof string) {
            $timezone = new DateTimeZone($timezone);
        }

        if ($time instanceof DateTimeImmutable) {
            $this->dateTimeImmutable = $timezone ? $time->setTimezone($timezone) : $time;
        } else {
            try {
                $this->dateTimeImmutable = new DateTimeImmutable($time, $timezone);
            } catch (Throwable $e) {
                throw new InvalidArgumentException('Invalid date time string: ' . $time, 0, $e);
            }
        }
    }

    public static function today(string|DateTimeZone|null $timezone = null) : self
    {
        return new self('today', $timezone);
    }

    public function addDays(int $days) : self
    {
        return new self($this->dateTimeImmutable->modify(sprintf('+%d days', $days)));
    }

    public function subDays(int $days) : self
    {
        return new self($this->dateTimeImmutable->modify(sprintf('-%d days', $days)));
    }

    public function toIso8601() : string
    {
        return $this->dateTimeImmutable->format(DateTimeImmutable::ATOM);
    }

    public function format(string $format) : string
    {
        return $this->dateTimeImmutable->format($format);
    }

    public function diffForHumans(self|null $other = null) : string
    {
        $other ??= self::now();
        $diff = $this->dateTimeImmutable->diff($other->dateTimeImmutable);

        if ($diff->y > 0) {
            return $diff->y . ' years ago';
        }

        if ($diff->m > 0) {
            return $diff->m . ' months ago';
        }

        if ($diff->d > 0) {
            return $diff->d . ' days ago';
        }

        if ($diff->h > 0) {
            return $diff->h . ' hours ago';
        }

        if ($diff->i > 0) {
            return $diff->i . ' minutes ago';
        }

        return 'just now';
    }

    public static function now(string|DateTimeZone|null $timezone = null) : self
    {
        return new self('now', $timezone);
    }

    public function native() : DateTimeImmutable
    {
        return $this->dateTimeImmutable;
    }
}
