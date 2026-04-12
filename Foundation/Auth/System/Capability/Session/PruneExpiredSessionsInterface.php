<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Session;

use DateTimeImmutable;

interface PruneExpiredSessionsInterface
{
    public function pruneExpired(DateTimeImmutable $now) : int;
}
