<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\CleanupExpiredPasswordResets;

use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\PruneExpiredPasswordResetsInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class CleanupExpiredPasswordResets
{
    public function __construct(
        #[SensitiveParameter]
        private PruneExpiredPasswordResetsInterface|null $pruneExpiredPasswordResets,
        private Clock                                $clock,
    ) {}

    public function execute() : int
    {
        if (! $this->pruneExpiredPasswordResets instanceof PruneExpiredPasswordResetsInterface) {
            return 0;
        }

        return $this->pruneExpiredPasswordResets->pruneExpired(now: $this->clock->now());
    }
}
