<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use DateTimeImmutable;

/**
 * Stores one-time password reset challenges.
 */
interface PasswordResetStoreInterface
{
    public function issue(UserId $userId, DateTimeImmutable $expiresAt) : PasswordResetChallenge;

    public function consume(string $token, DateTimeImmutable $now) : ?UserId;
}
