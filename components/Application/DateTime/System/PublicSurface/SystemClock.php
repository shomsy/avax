<?php

declare(strict_types=1);

namespace Avax\Components\Application\DateTime\System\PublicSurface;

use DateTimeImmutable;
use DateTimeZone;

final class SystemClock
{
    private static DateTimeImmutable|null $dateTimeImmutable = null;

    public static function create(string|null $timezone = null) : DateTimeImmutable
    {
        if (self::$dateTimeImmutable !== null) {
            $tz = $timezone ?? date_default_timezone_get();

            return self::$dateTimeImmutable->setTimezone(new DateTimeZone($tz));
        }

        $tz = $timezone ?? date_default_timezone_get();

        return new DateTimeImmutable('now', new DateTimeZone($tz));
    }

    public static function freeze(DateTimeImmutable $moment) : void
    {
        self::$dateTimeImmutable = $moment;
    }

    public static function unfreeze() : void
    {
        self::$dateTimeImmutable = null;
    }

    public static function isFrozen() : bool
    {
        return self::$dateTimeImmutable !== null;
    }
}
