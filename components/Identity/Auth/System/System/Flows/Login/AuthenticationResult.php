<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Flows\Login;

use Avax\Components\Identity\Auth\System\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
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
        private ?AuthenticatedUser    $authenticatedUser = null,
        #[SensitiveParameter]
        private ?string               $accessToken = null,
        #[SensitiveParameter]
        private ?string               $refreshToken = null,
        private ?MfaChallenge         $mfaChallenge = null,
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
            accessToken : $accessToken,
            refreshToken: $refreshToken,
            state       : AuthenticationState::AUTHENTICATED,
            context     : $authenticationContext,
            user        : $authenticationContext->user(),
        );
    }

    public function user() : ?AuthenticatedUser
    {
        return $this->authenticatedUser;
    }

    public static function mfaRequired(
        AuthenticatedUser $authenticatedUser,
        MfaChallenge      $mfaChallenge,
    ) : self
    {
        return new self(
            mfaChallenge: $mfaChallenge,
            state       : AuthenticationState::MFA_REQUIRED,
            context     : AuthenticationContext::guest(reason: 'mfa_required'),
            user        : $authenticatedUser,
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

    public function accessToken() : ?string
    {
        return $this->accessToken;
    }

    public function refreshToken() : ?string
    {
        return $this->refreshToken;
    }

    public function mfaChallenge() : ?MfaChallenge
    {
        return $this->mfaChallenge;
    }

    public function mfaChallengeId() : ?string
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
