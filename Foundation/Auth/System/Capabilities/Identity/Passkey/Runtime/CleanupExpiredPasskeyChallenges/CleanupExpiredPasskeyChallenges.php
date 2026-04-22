<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\CleanupExpiredPasskeyChallenges;

use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PruneExpiredPasskeyChallengesInterface;
use Avax\Auth\System\Foundation\Clock;

final readonly class CleanupExpiredPasskeyChallenges
{
    public function __construct(private PruneExpiredPasskeyChallengesInterface|null $challengeStore, private Clock $clock)
    {
    }

    public function execute() : int
    {
        if ($this->challengeStore === null) {
            return 0;
        }

        return $this->challengeStore->pruneExpired(now: $this->clock->now());
    }
}
