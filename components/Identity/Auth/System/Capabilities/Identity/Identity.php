<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityOwners\Account;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityOwners\Authentication;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityOwners\Recovery;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityOwners\Verification;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Sessions;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationFailed;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Components\Identity\Auth\System\Flows\Login\Credentials;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\RateLimitException;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Mfa;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Passkey;
use DateTimeImmutable;
use InvalidArgumentException;
use SensitiveParameter;

/**
 * Identity capability coordinator.
 *
 * @deprecated Use Identity::create() instead of the constructor for runtime assembly.
 *             The constructor accepts nullable sub-capabilities for bootstrap flows.
 *             The create() method requires all 9 parameters.
 */
final readonly class Identity implements IdentityInterface
{
    public function __construct(
        #[SensitiveParameter]
        private Authentication|null           $authentication = null,
        #[SensitiveParameter]
        private Sessions|null                 $sessions = null,
        #[SensitiveParameter]
        private Account|null                  $account = null,
        private Recovery|null                 $recovery = null,
        private Verification|null             $verification = null,
        private Mfa|null                      $mfa = null,
        private Passkey|null                  $passkey = null,
        #[SensitiveParameter]
        private SessionIdentityInterface|null $sessionIdentity = null,
        #[SensitiveParameter]
        private JwtIdentityInterface|null     $jwtIdentity = null,
    )
    {
        if (! $this->sessionIdentity instanceof SessionIdentityInterface && ! $this->jwtIdentity instanceof JwtIdentityInterface) {
            throw new InvalidArgumentException(message: 'Identity requires at least one backend.');
        }
    }

    /**
     * Create a fully populated Identity with all sub-capabilities.
     *
     * Unlike the constructor, this method requires all 7 sub-capability parameters
     * to be non-null. Backends (sessionIdentity/jwtIdentity) are nullable because
     * the constructor validates at least one is present.
     */
    public static function create(
        Authentication                   $authentication,
        Sessions                         $sessions,
        Account                          $account,
        Recovery                         $recovery,
        Verification                     $verification,
        Mfa                              $mfa,
        Passkey                          $passkey,
        SessionIdentityInterface|null    $sessionIdentity = null,
        JwtIdentityInterface|null        $jwtIdentity = null,
    ) : self {
        return new self(
            authentication : $authentication,
            sessions       : $sessions,
            account        : $account,
            recovery       : $recovery,
            verification   : $verification,
            mfa            : $mfa,
            passkey        : $passkey,
            sessionIdentity: $sessionIdentity,
            jwtIdentity    : $jwtIdentity,
        );
    }

    /**
     * @deprecated Use Identity::create() with all 9 parameters or the constructor with explicit params.
     *             This method exists only for backward compatibility with AuthBuilder bootstrap flow.
     */
    public static function fromBackends(
        #[SensitiveParameter]
        ?SessionIdentityInterface $sessionIdentity = null,
        #[SensitiveParameter]
        ?JwtIdentityInterface     $jwtIdentity = null,
    ) : self
    {
        return new self(
            sessionIdentity: $sessionIdentity,
            jwtIdentity    : $jwtIdentity,
        );
    }

    public function issue(
        User $user, DateTimeImmutable|null $mfaVerifiedAt = null,
        bool               $phishingResistant = false,
    ) : IssuedAuthentication
    {
        if (! $user->isActive()) {
            throw new InvalidArgumentException(message: 'Inactive users cannot be authenticated.');
        }

        $refreshToken = $this->jwtIdentity?->issueRefreshToken(
            user             : $user,
            mfaVerifiedAt    : $mfaVerifiedAt,
            phishingResistant: $phishingResistant,
        );
        $sessionId    = $this->sessionIdentity?->issue(
            userId           : $user->getId()->value,
            mfaVerifiedAt    : $mfaVerifiedAt,
            phishingResistant: $phishingResistant,
        );
        $accessToken  = $this->jwtIdentity?->issue(
            user                : $user,
            mfaVerifiedAt       : $mfaVerifiedAt,
            phishingResistant   : $phishingResistant,
            refreshTokenFamilyId: $refreshToken?->familyId,
        );

        return new IssuedAuthentication(
            mode             : $this->resolveMode(),
            sessionId        : $sessionId,
            accessToken      : $accessToken,
            refreshToken     : $refreshToken,
            mfaVerifiedAt    : $mfaVerifiedAt,
            phishingResistant: $phishingResistant,
        );
    }

    private function resolveMode() : AuthenticationMode
    {
        if ($this->sessionIdentity instanceof SessionIdentityInterface && $this->jwtIdentity instanceof JwtIdentityInterface) {
            return AuthenticationMode::HYBRID;
        }

        if ($this->sessionIdentity instanceof SessionIdentityInterface) {
            return AuthenticationMode::SESSION;
        }

        return AuthenticationMode::TOKEN;
    }

    public function clear(AuthenticationContext|null $authenticationContext = null) : void
    {
        $this->sessionIdentity?->clear();

        if (
            $authenticationContext instanceof AuthenticationContext
            && $this->jwtIdentity instanceof JwtIdentityInterface
            && $authenticationContext->accessTokenId() !== null
            && $authenticationContext->accessTokenExpiresAt() instanceof DateTimeImmutable
        ) {
            $this->jwtIdentity->revoke(
                tokenId  : $authenticationContext->accessTokenId(),
                expiresAt: $authenticationContext->accessTokenExpiresAt(),
            );
        }
    }

    public function sessionIdentity() : SessionIdentityInterface|null
    {
        return $this->sessionIdentity;
    }

    public function jwtIdentity() : JwtIdentityInterface|null
    {
        return $this->jwtIdentity;
    }

    public function login(#[SensitiveParameter] Credentials $credentials) : AuthenticationResult
    {
        return $this->authentication()->login(credentials: $credentials);
    }

    public function authentication() : Authentication
    {
        return $this->authentication ?? throw IdentityCapabilityUnavailable::coordinator(capability: 'authentication');
    }

    public function logout() : void
    {
        $this->authentication()->logout();
    }

    public function sessions() : Sessions
    {
        return $this->sessions ?? throw IdentityCapabilityUnavailable::coordinator(capability: 'sessions');
    }

    public function account() : Account
    {
        return $this->account ?? throw IdentityCapabilityUnavailable::coordinator(capability: 'account');
    }

    public function recovery() : Recovery
    {
        return $this->recovery ?? throw IdentityCapabilityUnavailable::coordinator(capability: 'recovery');
    }

    public function verification() : Verification
    {
        return $this->verification ?? throw IdentityCapabilityUnavailable::coordinator(capability: 'verification');
    }

    public function mfa() : Mfa
    {
        return $this->mfa ?? throw IdentityCapabilityUnavailable::coordinator(capability: 'mfa');
    }

    public function passkey() : Passkey
    {
        return $this->passkey ?? throw IdentityCapabilityUnavailable::coordinator(capability: 'passkey');
    }
}
