<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\CleanupExpiredAuthorizationCodes;

use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\PruneExpiredAuthorizationCodesInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class CleanupExpiredAuthorizationCodes
{
    public function __construct(
        #[SensitiveParameter]
        private PruneExpiredAuthorizationCodesInterface|null $codeStore,
        private Clock                                        $clock,
    ) {}

    public function execute() : int
    {
        if ($this->codeStore === null) {
            return 0;
        }

        return $this->codeStore->pruneExpired(now: $this->clock->now());
    }
}
