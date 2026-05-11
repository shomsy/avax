<?php

declare(strict_types=1);

namespace Avax\Components\Application\DateTime\System\Flows\Parse;

use Avax\Components\Application\DateTime\System\Foundation\Failure\InvalidDateTimeString;
use DateTimeImmutable;
use DateTimeZone;
use Exception;

final class ParseDate
{
    public function fromString(string $datetime, string|null $timezone = null) : DateTimeImmutable
    {
        $tz = $timezone ?? date_default_timezone_get();

        try {
            return new DateTimeImmutable($datetime, new DateTimeZone($tz));
        } catch (Exception) {
            throw new InvalidDateTimeString('Invalid datetime string: '.$datetime);
        }
    }

    public function fromFormat(string $datetime, string $format, string|null $timezone = null) : DateTimeImmutable
    {
        $tz = $timezone ?? date_default_timezone_get();
        $date = DateTimeImmutable::createFromFormat($format, $datetime, new DateTimeZone($tz));

        if ($date === false) {
            throw new InvalidDateTimeString(sprintf("Cannot parse '%s' with format '%s'", $datetime, $format));
        }

        return $date;
    }

    public function fromTimestamp(int $timestamp, string|null $timezone = null) : DateTimeImmutable
    {
        $tz = $timezone ?? date_default_timezone_get();

        return new DateTimeImmutable('@'.$timestamp, new DateTimeZone($tz))->setTimezone(new DateTimeZone($tz));
    }

    public function fromIso(string $iso): DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($iso);
        } catch (Exception) {
            throw new InvalidDateTimeString('Invalid ISO datetime: '.$iso);
        }
    }

    public function parseRelative(string $relative, string|null $timezone = null) : DateTimeImmutable
    {
        $tz = $timezone ?? date_default_timezone_get();

        try {
            $date = new DateTimeImmutable($relative, new DateTimeZone($tz));
            if ($date->format('Y-m-d') === '1970-01-01' && ! str_contains($relative, '1970')) {
                throw new InvalidDateTimeString('Cannot parse relative time: '.$relative);
            }

            return $date;
        } catch (Exception) {
            throw new InvalidDateTimeString('Invalid relative datetime: '.$relative);
        }
    }
}
