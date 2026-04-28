<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Limit;

use DateTimeImmutable;

interface PruneExpiredMfaChallengesInterface
{
    public function pruneExpired(DateTimeImmutable $now) : int;
}
