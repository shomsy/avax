<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Passkey;

use DateTimeImmutable;

interface PruneExpiredPasskeyChallengesInterface
{
    public function pruneExpired(DateTimeImmutable $now) : int;
}
