<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\VerifyIdentity\EmailVerification;

use Avax\Auth\System\Capabilities\Identity\User\UserId;

/**
 * Tracks whether a user has completed email verification.
 */
interface EmailVerificationStateStoreInterface
{
    public function isVerified(UserId $userId) : bool;

    public function markVerified(UserId $userId) : void;
}
