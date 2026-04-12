<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Recover\CleanupExpiredPasswordResets;

use Avax\Auth\System\Flow\Recover\PruneExpiredPasswordResetsInterface;
use Avax\Auth\System\Foundation\Clock;

final readonly class CleanupExpiredPasswordResets
{
    public function __construct(
        private PruneExpiredPasswordResetsInterface|null $passwordResetStore,
        private Clock $clock
    ) {}

    public function execute() : int
    {
        if ($this->passwordResetStore === null) {
            return 0;
        }

        return $this->passwordResetStore->pruneExpired($this->clock->now());
    }
}
