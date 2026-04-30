<?php

declare(strict_types=1);

namespace Avax\Components\Application\DateTime\System\Capabilities\Timezone;

use DateTimeInterface;
use DateTimeZone;
use Override;

final readonly class UtcTimezone implements Timezone
{
    private const string NAME = 'UTC';

    #[Override]
    public function getName() : string
    {
        return self::NAME;
    }

    #[Override]
    public function toPhpTimezone() : DateTimeZone
    {
        return new DateTimeZone(self::NAME);
    }

    #[Override]
    public function getOffset(DateTimeInterface $dateTime) : int
    {
        return 0;
    }

    #[Override]
    public function isUtc() : bool
    {
        return true;
    }
}
