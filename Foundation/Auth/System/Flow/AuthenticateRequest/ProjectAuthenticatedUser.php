<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\AuthenticateRequest;

use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Flow\Mfa\MfaStoreInterface;
use Avax\Auth\System\Flow\Verify\EmailVerificationStateStoreInterface;
use SensitiveParameter;

/**
 * Builds the public auth user snapshot from internal auth state.
 */
final readonly class ProjectAuthenticatedUser
{
    private MfaStoreInterface                    $mfaStore;
    private EmailVerificationStateStoreInterface $emailVerificationState;

    public function __construct(
        #[SensitiveParameter] EmailVerificationStateStoreInterface $emailVerificationState,
        MfaStoreInterface                                          $mfaStore
    )
    {
        $this->emailVerificationState = $emailVerificationState;
        $this->mfaStore               = $mfaStore;
    }

    public function fromUser(User $user) : AuthenticatedUser
    {
        return AuthenticatedUser::fromUser(
            user         : $user,
            emailVerified: $this->emailVerificationState->isVerified(userId: $user->getId()),
            mfaEnabled   : $this->mfaStore->isEnabled(userId: $user->getId())
        );
    }
}
