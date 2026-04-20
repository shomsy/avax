<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Passkey\Support;

use DateTimeImmutable;

interface PruneExpiredPasskeyChallengesInterface
{
    public function pruneExpired(DateTimeImmutable $now) : int;
}
