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
        private AuthenticationState   $authenticationState,
        private AuthenticationContext $authenticationContext,
        private AuthenticatedUser|null $authenticatedUser = null,
        #[SensitiveParameter]
        private string|null            $accessToken = null,
        #[SensitiveParameter]
        private string|null            $refreshToken = null,
        private MfaChallenge|null      $mfaChallenge = null,
    ) {}

    public static function success(
        AuthenticationContext $authenticationContext,
        #[SensitiveParameter]
        ?string               $accessToken = null,
        #[SensitiveParameter]
        ?string               $refreshToken = null,
    ) : self
    {
        return new self(
            authenticationState : AuthenticationState::AUTHENTICATED,
            authenticationContext: $authenticationContext,
            authenticatedUser   : $authenticationContext->user(),
            accessToken         : $accessToken,
            refreshToken        : $refreshToken,
        );
    }

    public function user() : AuthenticatedUser|null
    {
        return $this->authenticatedUser;
    }

    public static function mfaRequired(
        AuthenticatedUser $authenticatedUser,
        MfaChallenge      $mfaChallenge,
    ) : self
    {
        return new self(
            mfaChallenge      : $mfaChallenge,
            authenticationState: AuthenticationState::MFA_REQUIRED,
            authenticationContext: AuthenticationContext::guest(reason: 'mfa_required'),
            authenticatedUser : $authenticatedUser,
        );
    }

    public function state() : AuthenticationState
    {
        return $this->authenticationState;
    }

    public function context() : AuthenticationContext
    {
        return $this->authenticationContext;
    }

    public function accessToken() : string|null
    {
        return $this->accessToken;
    }

    public function refreshToken() : string|null
    {
        return $this->refreshToken;
    }

    public function mfaChallenge() : MfaChallenge|null
    {
        return $this->mfaChallenge;
    }

    public function mfaChallengeId() : string|null
    {
        return $this->mfaChallenge?->challengeId;
    }

    public function isAuthenticated() : bool
    {
        return $this->authenticationContext->isAuthenticated();
    }

    public function requiresMfa() : bool
    {
        return $this->authenticationState === AuthenticationState::MFA_REQUIRED;
    }
}
