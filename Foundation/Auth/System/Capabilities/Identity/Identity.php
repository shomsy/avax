<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity;

use Avax\Auth\System\Capabilities\Identity\IdentityOwners\Account;
use Avax\Auth\System\Capabilities\Identity\IdentityOwners\Authentication;
use Avax\Auth\System\Capabilities\Identity\IdentityOwners\Recovery;
use Avax\Auth\System\Capabilities\Identity\IdentityOwners\Verification;
use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Mfa\Mfa;
use Avax\Auth\System\Capabilities\Identity\Passkey\Passkey;
use Avax\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Sessions\Sessions;
use Avax\Auth\System\Capabilities\Identity\User\User;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flows\Login\AuthenticationFailed;
use Avax\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Auth\System\Flows\Login\Credentials;
use Avax\Auth\System\Flows\Login\RateLimit\RateLimitException;
use DateTimeImmutable;
use InvalidArgumentException;
use SensitiveParameter;

/**
 * Identity capability coordinator.
 *
 * Owns the cross-cutting identity lifecycle (issue, clear, resolve mode)
 * and exposes sub-capability owners for domain-specific operations:
 *
 *   $identity->mfa()->startMfaEnrollment();
 *   $identity->passkey()->beginPasskeyRegistration();
 *   $identity->sessions()->readActiveSessions();
 */
final readonly class Identity implements IdentityInterface
{
    public static function fromBackends(
        #[SensitiveParameter] SessionIdentityInterface|null $sessionIdentity = null,
        #[SensitiveParameter] JwtIdentityInterface|null     $jwtIdentity = null
    ) : self
    {
        return new self(
            sessionIdentity: $sessionIdentity,
            jwtIdentity    : $jwtIdentity
        );
    }

    public function __construct(
        #[SensitiveParameter] private Authentication|null $authentication = null,
        #[SensitiveParameter] private Sessions|null       $sessions = null,
        #[SensitiveParameter] private Account|null        $account = null,
        private Recovery|null                             $recovery = null,
        private Verification|null                         $verification = null,
        private Mfa|null                                  $mfa = null,
        private Passkey|null                              $passkey = null,
        #[SensitiveParameter] private SessionIdentityInterface|null $sessionIdentity = null,
        #[SensitiveParameter] private JwtIdentityInterface|null     $jwtIdentity = null
    )
    {
        if ($this->sessionIdentity === null && $this->jwtIdentity === null) {
            throw new InvalidArgumentException(message: 'Identity requires at least one backend.');
        }
    }

    // ── Owned behavior (cross-cutting identity lifecycle) ──

    public function issue(
        User                   $user,
        DateTimeImmutable|null $mfaVerifiedAt = null,
        bool                   $phishingResistant = false
    ) : IssuedAuthentication
    {
        if (! $user->isActive()) {
            throw new InvalidArgumentException(message: 'Inactive users cannot be authenticated.');
        }

        $refreshToken = $this->jwtIdentity?->issueRefreshToken(
            user             : $user,
            mfaVerifiedAt    : $mfaVerifiedAt,
            phishingResistant: $phishingResistant
        );
        $sessionId    = $this->sessionIdentity?->issue(
            userId           : $user->getId()->value,
            mfaVerifiedAt    : $mfaVerifiedAt,
            phishingResistant: $phishingResistant
        );
        $accessToken  = $this->jwtIdentity?->issue(
            user                : $user,
            mfaVerifiedAt       : $mfaVerifiedAt,
            phishingResistant   : $phishingResistant,
            refreshTokenFamilyId: $refreshToken?->familyId
        );

        return new IssuedAuthentication(
            mode             : $this->resolveMode(),
            sessionId        : $sessionId,
            accessToken      : $accessToken,
            refreshToken     : $refreshToken,
            mfaVerifiedAt    : $mfaVerifiedAt,
            phishingResistant: $phishingResistant
        );
    }

    public function clear(AuthenticationContext|null $context = null) : void
    {
        $this->sessionIdentity?->clear();

        if (
            $context !== null
            && $this->jwtIdentity !== null
            && $context->accessTokenId() !== null
            && $context->accessTokenExpiresAt() !== null
        ) {
            $this->jwtIdentity->revoke(
                tokenId  : $context->accessTokenId(),
                expiresAt: $context->accessTokenExpiresAt()
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

    // ── Fast-path convenience (high-frequency auth operations) ──

    /**
     * @throws AuthenticationFailed
     * @throws RateLimitException
     */
    public function login(#[SensitiveParameter] Credentials $credentials) : AuthenticationResult
    {
        return $this->authentication()->login(credentials: $credentials);
    }

    public function logout() : void
    {
        $this->authentication()->logout();
    }

    // ── Sub-capability accessors ──

    public function authentication() : Authentication
    {
        return $this->authentication ?? throw IdentityCapabilityUnavailable::coordinator(capability: 'authentication');
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

    private function resolveMode() : AuthenticationMode
    {
        if ($this->sessionIdentity !== null && $this->jwtIdentity !== null) {
            return AuthenticationMode::HYBRID;
        }

        if ($this->sessionIdentity !== null) {
            return AuthenticationMode::SESSION;
        }

        return AuthenticationMode::TOKEN;
    }
}
