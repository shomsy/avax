<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Foundation\Time;

use DateTimeImmutable;

final readonly class SystemClock implements Clock
{
    public function now() : DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
