<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Limit;

use Avax\Components\Identity\Auth\System\Foundation\Clock;

final readonly class CleanupExpiredMfaChallenges
{
    public function __construct(private ?PruneExpiredMfaChallengesInterface $pruneExpiredMfaChallenges, private Clock $clock) {}

    public function execute(): int
    {
        if (! $this->pruneExpiredMfaChallenges instanceof PruneExpiredMfaChallengesInterface) {
            return 0;
        }

        return $this->pruneExpiredMfaChallenges->pruneExpired(now: $this->clock->now());
    }
}
