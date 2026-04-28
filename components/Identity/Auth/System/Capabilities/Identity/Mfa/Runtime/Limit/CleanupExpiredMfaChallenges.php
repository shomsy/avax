<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\Identity\Mfa\Runtime\Limit;

use Avax\Components\Auth\System\Foundation\Clock;

final readonly class CleanupExpiredMfaChallenges
{
    public function __construct(private PruneExpiredMfaChallengesInterface|null $challengeStore, private Clock $clock) {}

    public function execute() : int
    {
        if ($this->challengeStore === null) {
            return 0;
        }

        return $this->challengeStore->pruneExpired(now: $this->clock->now());
    }
}
