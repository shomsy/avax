<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\Challenge\CleanupExpiredMfaChallenges;

use Avax\Auth\System\Flow\Mfa\Challenge\PruneExpiredMfaChallengesInterface;
use Avax\Auth\System\Foundation\Clock;

final readonly class CleanupExpiredMfaChallenges
{
    private Clock                                   $clock;
    private PruneExpiredMfaChallengesInterface|null $challengeStore;

    public function __construct(
        PruneExpiredMfaChallengesInterface|null $challengeStore,
        Clock                                   $clock
    )
    {
        $this->challengeStore = $challengeStore;
        $this->clock          = $clock;
    }

    public function execute() : int
    {
        if ($this->challengeStore === null) {
            return 0;
        }

        return $this->challengeStore->pruneExpired(now: $this->clock->now());
    }
}
