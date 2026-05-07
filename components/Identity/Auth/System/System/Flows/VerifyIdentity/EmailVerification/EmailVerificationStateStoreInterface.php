<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Flows\VerifyIdentity\EmailVerification;

use Avax\Components\Identity\Auth\System\System\Capabilities\Identity\User\UserId;

/**
 * Tracks whether a user has completed email verification.
 */
interface EmailVerificationStateStoreInterface
{
    public function isVerified(UserId $userId) : bool;

    public function markVerified(UserId $userId) : void;
}
