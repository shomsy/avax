<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\CleanupExpiredAuthorizationCodes;

use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\PruneExpiredAuthorizationCodesInterface;
use SensitiveParameter;

final readonly class CleanupExpiredAuthorizationCodes
{
    public function __construct(
        #[SensitiveParameter]
        private ?PruneExpiredAuthorizationCodesInterface $codeStore,
        private Clock $clock,
    ) {}

    public function execute(): int
    {
        if ($this->codeStore === null) {
            return 0;
        }

        return $this->codeStore->pruneExpired(now: $this->clock->now());
    }
}
