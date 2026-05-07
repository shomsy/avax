<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Flows\RecoverAccess\PasswordReset;

use DateTimeImmutable;

interface PruneExpiredPasswordResetsInterface
{
    public function pruneExpired(DateTimeImmutable $now) : int;
}
