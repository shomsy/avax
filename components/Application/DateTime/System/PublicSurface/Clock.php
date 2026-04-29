<?php

declare(strict_types=1);

namespace Avax\Components\Application\DateTime\System\PublicSurface;

use Avax\Components\Application\DateTime\System\Foundation\Failure\DateTimeFailure;
use DateTimeImmutable;
use DateTimeZone;

final class Clock
{
    private static ?SystemClock $instance = null;

    public static function tomorrow(string|null $timezone = null) : DateTimeImmutable
    {
        return self::today(timezone: $timezone)->modify('+1 day');
    }

    public static function today(string|null $timezone = null) : DateTimeImmutable
    {
        return self::now(timezone: $timezone)->setTime(0, 0, 0, 0);
    }

    public static function now(string|null $timezone = null) : DateTimeImmutable
    {
        return SystemClock::create(timezone: $timezone);
    }

    public static function yesterday(string|null $timezone = null) : DateTimeImmutable
    {
        return self::today(timezone: $timezone)->modify('-1 day');
    }

    public static function freeze(DateTimeImmutable $moment) : void
    {
        SystemClock::freeze($moment);
    }

    public static function unfreeze() : void
    {
        SystemClock::unfreeze();
    }

    public static function isFrozen() : bool
    {
        return SystemClock::isFrozen();
    }
}