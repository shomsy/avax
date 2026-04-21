<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Login;

use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaChallenge;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use SensitiveParameter;

/**
 * Stable public result of a login or refresh operation.
 */
final readonly class AuthenticationResult
{
    private MfaChallenge|null      $mfaChallenge;
    private string|null            $refreshToken;
    private string|null            $accessToken;
    private AuthenticatedUser|null $user;
    private AuthenticationContext  $context;
    private AuthenticationState    $state;

    public function __construct(
        AuthenticationState               $state,
        AuthenticationContext             $context,
        AuthenticatedUser|null            $user = null,
        #[SensitiveParameter] string|null $accessToken = null,
        #[SensitiveParameter] string|null $refreshToken = null,
        MfaChallenge|null                 $mfaChallenge = null
    )
    {
        $this->state        = $state;
        $this->context      = $context;
        $this->user         = $user;
        $this->accessToken  = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->mfaChallenge = $mfaChallenge;
    }

    public static function success(
        AuthenticationContext             $context,
        #[SensitiveParameter] string|null $accessToken = null,
        #[SensitiveParameter] string|null $refreshToken = null
    ) : self
    {
        return new self(
            state       : AuthenticationState::AUTHENTICATED,
            context     : $context,
            user        : $context->user(),
            accessToken : $accessToken,
            refreshToken: $refreshToken
        );
    }

    public function user() : AuthenticatedUser|null
    {
        return $this->user;
    }

    public static function mfaRequired(
        AuthenticatedUser $user,
        MfaChallenge      $challenge
    ) : self
    {
        return new self(
            state       : AuthenticationState::MFA_REQUIRED,
            context     : AuthenticationContext::guest(reason: 'mfa_required'),
            user        : $user,
            mfaChallenge: $challenge
        );
    }

    public function state() : AuthenticationState
    {
        return $this->state;
    }

    public function context() : AuthenticationContext
    {
        return $this->context;
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
        return $this->context->isAuthenticated();
    }

    public function requiresMfa() : bool
    {
        return $this->state === AuthenticationState::MFA_REQUIRED;
    }
}
