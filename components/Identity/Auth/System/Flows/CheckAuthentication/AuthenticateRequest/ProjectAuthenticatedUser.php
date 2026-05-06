<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\EmailVerificationStateStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Stores\MfaStoreInterface;
use SensitiveParameter;

/**
 * Builds the public auth user snapshot from internal auth state.
 */
final readonly class ProjectAuthenticatedUser
{
    public function __construct(
        #[SensitiveParameter]
        private EmailVerificationStateStoreInterface $emailVerificationStateStore,
        private MfaStoreInterface $mfaStore,
    ) {
    }

    public function fromUser(User $user): AuthenticatedUser
    {
        return AuthenticatedUser::fromUser(
            user         : $user,
            emailVerified: $this->emailVerificationStateStore->isVerified(userId: $user->getId()),
            mfaEnabled   : $this->mfaStore->isEnabled(userId: $user->getId()),
        );
    }
}
