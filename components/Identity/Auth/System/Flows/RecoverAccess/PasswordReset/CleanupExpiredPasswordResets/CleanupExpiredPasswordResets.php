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
        private PruneExpiredPasswordResetsInterface|null $passwordResetStore,
        private Clock                                    $clock,
    ) {}

    public function execute() : int
    {
        if ($this->passwordResetStore === null) {
            return 0;
        }

        return $this->passwordResetStore->pruneExpired(now: $this->clock->now());
    }
}
