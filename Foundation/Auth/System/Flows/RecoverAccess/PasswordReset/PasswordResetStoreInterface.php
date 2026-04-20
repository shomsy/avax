<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Recover;

use Avax\Auth\System\Capabilities\User\UserId;
use DateTimeImmutable;

/**
 * Stores one-time password reset challenges.
 */
interface PasswordResetStoreInterface
{
    public function issue(UserId $userId, DateTimeImmutable $expiresAt) : PasswordResetChallenge;

    public function consume(string $token, DateTimeImmutable $now) : UserId|null;
}
