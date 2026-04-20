<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Session;

use DateTimeImmutable;

interface PruneExpiredSessionsInterface
{
    public function pruneExpired(DateTimeImmutable $now) : int;
}
