<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\Login;

use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\MfaChallenge;
use SensitiveParameter;

/**
 * Stable public result of a login or refresh operation.
 */
final readonly class AuthenticationResult
{
    public function __construct(
        private AuthenticationState $state,
        private AuthenticationContext $context,
        private ?AuthenticatedUser $user = null,
        #[SensitiveParameter]
        private ?string $accessToken = null,
        #[SensitiveParameter]
        private ?string $refreshToken = null,
        private ?MfaChallenge $mfaChallenge = null,
    ) {}

    public static function success(
        AuthenticationContext $context,
        #[SensitiveParameter]
        ?string $accessToken = null,
        #[SensitiveParameter]
        ?string $refreshToken = null,
    ): self {
        return new self(
            state       : AuthenticationState::AUTHENTICATED,
            context     : $context,
            user        : $context->user(),
            accessToken : $accessToken,
            refreshToken: $refreshToken,
        );
    }

    public function user(): ?AuthenticatedUser
    {
        return $this->user;
    }

    public static function mfaRequired(
        AuthenticatedUser $user,
        MfaChallenge $challenge,
    ): self {
        return new self(
            state       : AuthenticationState::MFA_REQUIRED,
            context     : AuthenticationContext::guest(reason: 'mfa_required'),
            user        : $user,
            mfaChallenge: $challenge,
        );
    }

    public function state(): AuthenticationState
    {
        return $this->state;
    }

    public function context(): AuthenticationContext
    {
        return $this->context;
    }

    public function accessToken(): ?string
    {
        return $this->accessToken;
    }

    public function refreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function mfaChallenge(): ?MfaChallenge
    {
        return $this->mfaChallenge;
    }

    public function mfaChallengeId(): ?string
    {
        return $this->mfaChallenge?->challengeId;
    }

    public function isAuthenticated(): bool
    {
        return $this->context->isAuthenticated();
    }

    public function requiresMfa(): bool
    {
        return $this->state === AuthenticationState::MFA_REQUIRED;
    }
}
