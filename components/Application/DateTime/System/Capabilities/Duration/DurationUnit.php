<?php

declare(strict_types=1);

namespace Avax\Components\Application\DateTime\System\Capabilities\Duration;

enum DurationUnit: string
{
    case SECOND = 'second';
    case MINUTE = 'minute';
    case HOUR   = 'hour';
    case DAY    = 'day';
    case WEEK   = 'week';
    case MONTH  = 'month';
    case YEAR   = 'year';

    public function inSeconds(): int
    {
        return match ($this) {
            self::SECOND => 1,
            self::MINUTE => 60,
            self::HOUR   => 3600,
            self::DAY    => 86400,
            self::WEEK   => 604800,
            self::MONTH  => 2592000,
            self::YEAR   => 31536000,
        };
    }

    public function plural(): string
    {
        return match ($this) {
            self::SECOND => 'seconds',
            self::MINUTE => 'minutes',
            self::HOUR   => 'hours',
            self::DAY    => 'days',
            self::WEEK   => 'weeks',
            self::MONTH  => 'months',
            self::YEAR   => 'years',
        };
    }
}
