<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\CleanupExpiredPasskeyChallenges;

use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PruneExpiredPasskeyChallengesInterface;

final readonly class CleanupExpiredPasskeyChallenges
{
    public function __construct(private ?PruneExpiredPasskeyChallengesInterface $pruneExpiredPasskeyChallenges, private Clock $clock) {}

    public function execute(): int
    {
        if (! $this->pruneExpiredPasskeyChallenges instanceof PruneExpiredPasskeyChallengesInterface) {
            return 0;
        }

        return $this->pruneExpiredPasskeyChallenges->pruneExpired(now: $this->clock->now());
    }
}
