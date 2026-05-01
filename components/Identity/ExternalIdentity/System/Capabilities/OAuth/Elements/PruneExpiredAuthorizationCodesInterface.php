<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements;

use DateTimeImmutable;

interface PruneExpiredAuthorizationCodesInterface
{
    public function pruneExpired(DateTimeImmutable $now): int;
}
