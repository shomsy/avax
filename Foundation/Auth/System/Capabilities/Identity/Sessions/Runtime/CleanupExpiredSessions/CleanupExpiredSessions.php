<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Sessions\Runtime\CleanupExpiredSessions;

use Avax\Auth\System\Capabilities\Identity\Sessions\Registry\PruneExpiredSessionsInterface;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class CleanupExpiredSessions
{
    public function __construct(
        #[SensitiveParameter] private PruneExpiredSessionsInterface|null $sessionRegistry,
        private Clock                                                    $clock
    )
    {
    }

    public function execute() : int
    {
        if ($this->sessionRegistry === null) {
            return 0;
        }

        return $this->sessionRegistry->pruneExpired(now: $this->clock->now());
    }
}