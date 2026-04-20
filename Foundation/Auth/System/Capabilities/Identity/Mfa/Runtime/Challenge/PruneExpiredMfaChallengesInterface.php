<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Mfa\Challenge;

use DateTimeImmutable;

interface PruneExpiredMfaChallengesInterface
{
    public function pruneExpired(DateTimeImmutable $now) : int;
}
