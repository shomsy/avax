<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Passkey\CleanupExpiredPasskeyChallenges;

use Avax\Auth\System\Capability\Passkey\PruneExpiredPasskeyChallengesInterface;
use Avax\Auth\System\Foundation\Clock;

final readonly class CleanupExpiredPasskeyChallenges
{
    public function __construct(
        private PruneExpiredPasskeyChallengesInterface|null $challengeStore,
        private Clock $clock
    ) {}

    public function execute() : int
    {
        if ($this->challengeStore === null) {
            return 0;
        }

        return $this->challengeStore->pruneExpired($this->clock->now());
    }
}
