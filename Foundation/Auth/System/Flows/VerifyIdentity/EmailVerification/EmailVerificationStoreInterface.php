<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Verify;

use Avax\Auth\System\Capabilities\User\UserId;
use DateTimeImmutable;

/**
 * Stores one-time email verification challenges.
 */
interface EmailVerificationStoreInterface
{
    public function issue(UserId $userId, DateTimeImmutable $expiresAt) : EmailVerificationChallenge;

    public function consume(string $token, DateTimeImmutable $now) : UserId|null;
}
