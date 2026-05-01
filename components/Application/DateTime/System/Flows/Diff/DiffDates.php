<?php

declare(strict_types=1);

namespace Avax\Components\Application\DateTime\System\Flows\Diff;

use DateTimeImmutable;

final class DiffDates
{
    public function inDays(DateTimeImmutable $from, DateTimeImmutable $to): int
    {
        return (int) ($to->getTimestamp() - $from->getTimestamp()) / (60 * 60 * 24);
    }

    public function inHours(DateTimeImmutable $from, DateTimeImmutable $to): int
    {
        return (int) ($to->getTimestamp() - $from->getTimestamp()) / (60 * 60);
    }

    public function inMinutes(DateTimeImmutable $from, DateTimeImmutable $to): int
    {
        return (int) ($to->getTimestamp() - $from->getTimestamp()) / 60;
    }

    public function inSeconds(DateTimeImmutable $from, DateTimeImmutable $to): int
    {
        return $to->getTimestamp() - $from->getTimestamp();
    }

    public function inWeeks(DateTimeImmutable $from, DateTimeImmutable $to): int
    {
        return (int) (($to->getTimestamp() - $from->getTimestamp()) / (60 * 60 * 24 * 7));
    }

    public function inMonths(DateTimeImmutable $from, DateTimeImmutable $to): int
    {
        return ($to->format('Y') - $from->format('Y')) * 12 + (int) $to->format('m') - (int) $from->format('m');
    }

    public function inYears(DateTimeImmutable $from, DateTimeImmutable $to): int
    {
        return (int) $to->format('Y') - (int) $from->format('Y');
    }

    public function forHumans(DateTimeImmutable $from, DateTimeImmutable $to): string
    {
        $diff = $to->getTimestamp() - $from->getTimestamp();

        if (abs($diff) < 60) {
            return $diff === 0 ? 'just now' : ($diff > 0 ? 'seconds ago' : 'seconds from now');
        }

        if (abs($diff) < 3600) {
            $minutes = (int) ($diff / 60);

            return $minutes === 1 ? '1 minute ago' : ($minutes === -1 ? '1 minute from now' : ($diff > 0 ? $minutes . ' minutes ago' : abs($minutes) . ' minutes from now'));
        }

        if (abs($diff) < 86400) {
            $hours = (int) ($diff / 3600);

            return $hours === 1 ? '1 hour ago' : ($hours === -1 ? '1 hour from now' : ($diff > 0 ? $hours . ' hours ago' : abs($hours) . ' hours from now'));
        }

        if (abs($diff) < 604800) {
            $days = (int) ($diff / 86400);

            return $days === 1 ? '1 day ago' : ($days === -1 ? '1 day from now' : ($diff > 0 ? $days . ' days ago' : abs($days) . ' days from now'));
        }

        if (abs($diff) < 2592000) {
            $weeks = (int) ($diff / 604800);

            return $weeks === 1 ? '1 week ago' : ($weeks === -1 ? '1 week from now' : ($diff > 0 ? $weeks . ' weeks ago' : abs($weeks) . ' weeks from now'));
        }

        if (abs($diff) < 31536000) {
            $months = (int) ($diff / 2592000);

            return $months === 1 ? '1 month ago' : ($months === -1 ? '1 month from now' : ($diff > 0 ? $months . ' months ago' : abs($months) . ' months from now'));
        }

        $years = (int) ($diff / 31536000);

        return $years === 1 ? '1 year ago' : ($years === -1 ? '1 year from now' : ($diff > 0 ? $years . ' years ago' : abs($years) . ' years from now'));
    }

    public function isPast(DateTimeImmutable $date): bool
    {
        return $date < new DateTimeImmutable();
    }

    public function isFuture(DateTimeImmutable $date): bool
    {
        return $date > new DateTimeImmutable();
    }

    public function isToday(DateTimeImmutable $date): bool
    {
        return $date->format('Y-m-d') === (new DateTimeImmutable())->format('Y-m-d');
    }

    public function isTomorrow(DateTimeImmutable $date): bool
    {
        return $date->format('Y-m-d') === (new DateTimeImmutable())->modify('+1 day')->format('Y-m-d');
    }

    public function isYesterday(DateTimeImmutable $date): bool
    {
        return $date->format('Y-m-d') === (new DateTimeImmutable())->modify('-1 day')->format('Y-m-d');
    }
}
