<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Limit;

use DateTimeImmutable;

interface PruneExpiredMfaChallengesInterface
{
    public function pruneExpired(DateTimeImmutable $now): int;
}
