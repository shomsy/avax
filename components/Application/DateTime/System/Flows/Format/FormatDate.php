<?php

declare(strict_types=1);

namespace Avax\Components\Application\DateTime\System\Flows\Format;

use Avax\Components\Application\DateTime\System\Foundation\Failure\InvalidDateTimeString;
use DateTimeImmutable;
use DateTimeInterface;

final class FormatDate
{
    public function toIso(DateTimeImmutable $date) : string
    {
        return $date->format(DateTimeInterface::ISO8601);
    }

    public function format(DateTimeImmutable $date, string $format) : string
    {
        return $date->format($format);
    }

    public function toDateString(DateTimeImmutable $date) : string
    {
        return $date->format('Y-m-d');
    }

    public function toTimeString(DateTimeImmutable $date) : string
    {
        return $date->format('H:i:s');
    }

    public function toDateTimeString(DateTimeImmutable $date) : string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function toHuman(DateTimeImmutable $date) : string
    {
        return $date->format('F j, Y g:i a');
    }

    public function toShortDate(DateTimeImmutable $date) : string
    {
        return $date->format('m/d/Y');
    }

    public function toLongDate(DateTimeImmutable $date) : string
    {
        return $date->format('l, F j, Y');
    }

    public function toMonthYear(DateTimeImmutable $date) : string
    {
        return $date->format('F Y');
    }

    public function toDayMonth(DateTimeImmutable $date) : string
    {
        return $date->format('j M');
    }
}