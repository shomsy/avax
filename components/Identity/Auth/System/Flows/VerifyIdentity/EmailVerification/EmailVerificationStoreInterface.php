<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use DateTimeImmutable;

/**
 * Stores one-time email verification challenges.
 */
interface EmailVerificationStoreInterface
{
    public function issue(UserId $userId, DateTimeImmutable $expiresAt) : EmailVerificationChallenge;

    public function consume(string $token, DateTimeImmutable $now) : ?UserId;
}
