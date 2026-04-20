<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity;

use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Auth\System\Capabilities\Identity\User\User;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use DateTimeImmutable;
use InvalidArgumentException;
use SensitiveParameter;

/**
 * Unified identity façade that coordinates session and JWT authentication state.
 */
final readonly class Identity implements IdentityInterface
{
    private JwtIdentityInterface|null     $jwtIdentity;
    private SessionIdentityInterface|null $sessionIdentity;

    public function __construct(
        #[SensitiveParameter] SessionIdentityInterface|null $sessionIdentity = null,
        #[SensitiveParameter] JwtIdentityInterface|null     $jwtIdentity = null
    )
    {
        $this->sessionIdentity = $sessionIdentity;
        $this->jwtIdentity     = $jwtIdentity;
        if ($this->sessionIdentity === null && $this->jwtIdentity === null) {
            throw new InvalidArgumentException(message: 'Identity requires at least one backend.');
        }
    }

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
}
