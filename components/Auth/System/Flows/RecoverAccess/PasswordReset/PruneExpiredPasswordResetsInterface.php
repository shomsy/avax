<?php

declare(strict_types=1);

namespace components\Auth\System\Flows\RecoverAccess\PasswordReset;

use DateTimeImmutable;

interface PruneExpiredPasswordResetsInterface
{
    public function pruneExpired(DateTimeImmutable $now) : int;
}
