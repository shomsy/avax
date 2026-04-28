<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support;

use DateTimeImmutable;

interface PruneExpiredAuthorizationCodesInterface
{
    public function pruneExpired(DateTimeImmutable $now) : int;
}
