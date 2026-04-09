<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Verify;

use Avax\Auth\System\Capability\User\UserId;

/**
 * Default verification state store when verification is not configured.
 */
final class NullEmailVerificationStateStore implements EmailVerificationStateStoreInterface
{
    public function isVerified(UserId $userId) : bool
    {
        return false;
    }

    public function markVerified(UserId $userId) : void {}
}
