<?php

declare(strict_types=1);

namespace Avax\DateTime\System\Capabilities\Timezone;

use DateTimeInterface;
use DateTimeZone;

final readonly class UtcTimezone implements Timezone
{
    private const NAME = 'UTC';

    public function getName() : string
    {
        return self::NAME;
    }

    public function toPhpTimezone() : DateTimeZone
    {
        return new DateTimeZone(self::NAME);
    }

    public function getOffset(DateTimeInterface $dateTime) : int
    {
        return 0;
    }

    public function isUtc() : bool
    {
        return true;
    }
}