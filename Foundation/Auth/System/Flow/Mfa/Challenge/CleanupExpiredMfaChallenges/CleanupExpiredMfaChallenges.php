<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\Challenge\CleanupExpiredMfaChallenges;

use Avax\Auth\System\Flow\Mfa\Challenge\PruneExpiredMfaChallengesInterface;
use Avax\Auth\System\Foundation\Clock;

final readonly class CleanupExpiredMfaChallenges
{
    public function __construct(
        private PruneExpiredMfaChallengesInterface|null $challengeStore,
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
