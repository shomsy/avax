<?php

declare(strict_types=1);

namespace Avax\Components\Application\DateTime\System\PublicSurface;

use DateTimeImmutable;
use DateTimeZone;

final class SystemClock
{
    private static ?DateTimeImmutable $frozen = null;

    public static function create(string|null $timezone = null) : DateTimeImmutable
    {
        if (self::$frozen !== null) {
            $tz = $timezone ?? date_default_timezone_get();

            return self::$frozen->setTimezone(new DateTimeZone($tz));
        }

        $tz = $timezone ?? date_default_timezone_get();

        return new DateTimeImmutable('now', new DateTimeZone($tz));
    }

    public static function freeze(DateTimeImmutable $moment) : void
    {
        self::$frozen = $moment;
    }

    public static function unfreeze() : void
    {
        self::$frozen = null;
    }

    public static function isFrozen() : bool
    {
        return self::$frozen !== null;
    }
}
