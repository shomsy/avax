<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\OAuth;

use DateTimeImmutable;

interface PruneExpiredAuthorizationCodesInterface
{
    public function pruneExpired(DateTimeImmutable $now) : int;
}
