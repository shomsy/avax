<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Recover;

use DateTimeImmutable;

interface PruneExpiredPasswordResetsInterface
{
    public function pruneExpired(DateTimeImmutable $now) : int;
}
