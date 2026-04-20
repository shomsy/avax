<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Challenge;

use DateTimeImmutable;

interface PruneExpiredMfaChallengesInterface
{
    public function pruneExpired(DateTimeImmutable $now) : int;
}
