<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Passkey\CleanupExpiredPasskeyChallenges;

use Avax\Auth\System\Capabilities\Passkey\PruneExpiredPasskeyChallengesInterface;
use Avax\Auth\System\Foundation\Clock;

final readonly class CleanupExpiredPasskeyChallenges
{
    private Clock                                       $clock;
    private PruneExpiredPasskeyChallengesInterface|null $challengeStore;

    public function __construct(
        PruneExpiredPasskeyChallengesInterface|null $challengeStore,
        Clock                                       $clock
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
