<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\OAuth\CleanupExpiredAuthorizationCodes;

use Avax\Auth\System\Capabilities\OAuth\PruneExpiredAuthorizationCodesInterface;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class CleanupExpiredAuthorizationCodes
{
    private Clock                                        $clock;
    private PruneExpiredAuthorizationCodesInterface|null $codeStore;

    public function __construct(
        #[SensitiveParameter] PruneExpiredAuthorizationCodesInterface|null $codeStore,
        Clock                                                              $clock
    )
    {
        $this->codeStore = $codeStore;
        $this->clock     = $clock;
    }

    public function execute() : int
    {
        if ($this->codeStore === null) {
            return 0;
        }

        return $this->codeStore->pruneExpired(now: $this->clock->now());
    }
}
