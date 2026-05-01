<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;

/**
 * Default verification state store when verification is not configured.
 */
final class NullEmailVerificationStateStore implements EmailVerificationStateStoreInterface
{
    public function isVerified(UserId $userId): bool
    {
        return false;
    }

    public function markVerified(UserId $userId): void {}
}
