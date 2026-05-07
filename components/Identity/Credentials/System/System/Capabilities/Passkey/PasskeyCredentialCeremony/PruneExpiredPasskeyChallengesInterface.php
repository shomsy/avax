<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\PasskeyCredentialCeremony;

use DateTimeImmutable;

interface PruneExpiredPasskeyChallengesInterface
{
    public function pruneExpired(DateTimeImmutable $now) : int;
}
