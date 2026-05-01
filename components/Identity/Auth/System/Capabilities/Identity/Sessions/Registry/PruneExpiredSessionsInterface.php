<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry;

use DateTimeImmutable;

interface PruneExpiredSessionsInterface
{
    public function pruneExpired(DateTimeImmutable $now): int;
}
