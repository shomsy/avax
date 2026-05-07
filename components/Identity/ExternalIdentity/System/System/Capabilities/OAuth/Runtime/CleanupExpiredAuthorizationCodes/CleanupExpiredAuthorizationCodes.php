<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OAuth\Runtime\CleanupExpiredAuthorizationCodes;

use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OAuth\Elements\PruneExpiredAuthorizationCodesInterface;
use SensitiveParameter;

final readonly class CleanupExpiredAuthorizationCodes
{
    public function __construct(
        #[SensitiveParameter]
        private ?PruneExpiredAuthorizationCodesInterface $pruneExpiredAuthorizationCodes,
        private Clock                                    $clock,
    ) {}

    public function execute() : int
    {
        if (! $this->pruneExpiredAuthorizationCodes instanceof PruneExpiredAuthorizationCodesInterface) {
            return 0;
        }

        return $this->pruneExpiredAuthorizationCodes->pruneExpired(now: $this->clock->now());
    }
}
