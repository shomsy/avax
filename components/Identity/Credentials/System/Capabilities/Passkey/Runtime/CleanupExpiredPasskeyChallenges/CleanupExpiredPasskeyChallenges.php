<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\CleanupExpiredPasskeyChallenges;

use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Support\PruneExpiredPasskeyChallengesInterface;

final readonly class CleanupExpiredPasskeyChallenges
{
    public function __construct(private ?PruneExpiredPasskeyChallengesInterface $challengeStore, private Clock $clock) {}

    public function execute(): int
    {
        if ($this->challengeStore === null) {
            return 0;
        }

        return $this->challengeStore->pruneExpired(now: $this->clock->now());
    }
}
