<?php

declare(strict_types=1);

namespace Avax\Components\Application\DateTime\System\Flows\Modify;

use DateTimeImmutable;

final class ModifyDate
{
    public function addDays(DateTimeImmutable $date, int $days) : DateTimeImmutable
    {
        return $date->modify(sprintf('%+d days', $days));
    }

    public function addWeeks(DateTimeImmutable $date, int $weeks) : DateTimeImmutable
    {
        return $date->modify(sprintf('%+d weeks', $weeks));
    }

    public function addMonths(DateTimeImmutable $date, int $months) : DateTimeImmutable
    {
        return $date->modify(sprintf('%+d months', $months));
    }

    public function addYears(DateTimeImmutable $date, int $years) : DateTimeImmutable
    {
        return $date->modify(sprintf('%+d years', $years));
    }

    public function addHours(DateTimeImmutable $date, int $hours) : DateTimeImmutable
    {
        return $date->modify(sprintf('%+d hours', $hours));
    }

    public function addMinutes(DateTimeImmutable $date, int $minutes) : DateTimeImmutable
    {
        return $date->modify(sprintf('%+d minutes', $minutes));
    }

    public function addSeconds(DateTimeImmutable $date, int $seconds) : DateTimeImmutable
    {
        return $date->modify(sprintf('%+d seconds', $seconds));
    }

    public function startOfDay(DateTimeImmutable $date) : DateTimeImmutable
    {
        return $date->setTime(0, 0, 0, 0);
    }

    public function endOfDay(DateTimeImmutable $date) : DateTimeImmutable
    {
        return $date->setTime(23, 59, 59, 999999);
    }

    public function startOfMonth(DateTimeImmutable $date) : DateTimeImmutable
    {
        return $date->modify('first day of this month')->setTime(0, 0, 0, 0);
    }

    public function endOfMonth(DateTimeImmutable $date) : DateTimeImmutable
    {
        return $date->modify('last day of this month')->setTime(23, 59, 59, 999999);
    }

    public function startOfYear(DateTimeImmutable $date) : DateTimeImmutable
    {
        return $date->modify('first day of january this year')->setTime(0, 0, 0, 0);
    }

    public function endOfYear(DateTimeImmutable $date) : DateTimeImmutable
    {
        return $date->modify('last day of december this year')->setTime(23, 59, 59, 999999);
    }

    public function next(DateTimeImmutable $date, string $dayOfWeek) : DateTimeImmutable
    {
        return $date->modify("next $dayOfWeek");
    }

    public function previous(DateTimeImmutable $date, string $dayOfWeek) : DateTimeImmutable
    {
        return $date->modify("previous $dayOfWeek");
    }
}