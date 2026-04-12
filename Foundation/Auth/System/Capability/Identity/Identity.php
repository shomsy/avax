<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Identity;

use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capability\Identity\Session\SessionIdentityInterface;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Unified identity façade that coordinates session and JWT authentication state.
 */
final readonly class Identity implements IdentityInterface
{
    public function __construct(
        private SessionIdentityInterface|null $sessionIdentity = null,
        private JwtIdentityInterface|null     $jwtIdentity = null
    )
    {
        if ($this->sessionIdentity === null && $this->jwtIdentity === null) {
            throw new InvalidArgumentException(message: 'Identity requires at least one backend.');
        }
    }

    public function issue(
        User $user,
        DateTimeImmutable|null $mfaVerifiedAt = null,
        bool $phishingResistant = false
    ) : IssuedAuthentication
    {
        if (! $user->isActive()) {
            throw new InvalidArgumentException(message: 'Inactive users cannot be authenticated.');
        }

        $sessionId    = $this->sessionIdentity?->issue(
            userId       : $user->getId()->value,
            mfaVerifiedAt: $mfaVerifiedAt,
            phishingResistant: $phishingResistant
        );
        $accessToken  = $this->jwtIdentity?->issue(
            user               : $user,
            mfaVerifiedAt      : $mfaVerifiedAt,
            phishingResistant  : $phishingResistant
        );
        $refreshToken = $this->jwtIdentity?->issueRefreshToken(
            user               : $user,
            mfaVerifiedAt      : $mfaVerifiedAt,
            phishingResistant  : $phishingResistant
        );

        return new IssuedAuthentication(
            mode         : $this->resolveMode(),
            sessionId    : $sessionId,
            accessToken  : $accessToken,
            refreshToken : $refreshToken,
            mfaVerifiedAt: $mfaVerifiedAt,
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
