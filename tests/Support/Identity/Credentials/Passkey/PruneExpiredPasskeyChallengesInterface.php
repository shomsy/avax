<?php

declare(strict_types=1);

namespace Avax\Tests\Support\Identity\Credentials\Passkey;

use DateTimeImmutable;

interface PruneExpiredPasskeyChallengesInterface
{
    public function pruneExpired(DateTimeImmutable $now): int;
}
