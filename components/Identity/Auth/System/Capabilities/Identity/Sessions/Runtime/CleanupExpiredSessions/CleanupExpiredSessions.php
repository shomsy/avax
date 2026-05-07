<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\CleanupExpiredSessions;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\PruneExpiredSessionsInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class CleanupExpiredSessions
{
    public function __construct(
        #[SensitiveParameter]
        private ?PruneExpiredSessionsInterface $pruneExpiredSessions,
        private Clock                          $clock,
    ) {}

    public function execute() : int
    {
        if (! $this->pruneExpiredSessions instanceof PruneExpiredSessionsInterface) {
            return 0;
        }

        return $this->pruneExpiredSessions->pruneExpired(now: $this->clock->now());
    }
}
