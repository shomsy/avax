<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest;

use DateTimeImmutable;
use InvalidArgumentException;
use SensitiveParameter;

/**
 * Immutable authentication context captured at the ingress boundary.
 */
final readonly class AuthenticationContext
{
    public function __construct(
        private bool $authenticated,
        private AuthenticationMode $mode,
        private ?AuthenticatedUser $user = null,
        private ?string $reason = null,
        #[SensitiveParameter]
        private ?string $sessionId = null,
        #[SensitiveParameter]
        private ?string $accessTokenId = null,
        #[SensitiveParameter]
        private ?DateTimeImmutable $accessTokenExpiresAt = null,
        #[SensitiveParameter]
        private ?string $refreshTokenId = null,
        #[SensitiveParameter]
        private ?string $refreshTokenFamilyId = null,
        private ?DateTimeImmutable $mfaVerifiedAt = null,
        private bool $phishingResistant = false,
    ) {
        if ($this->authenticated && $this->user === null) {
            throw new InvalidArgumentException(message: 'Authenticated context requires a user.');
        }

        if (! $this->authenticated && $this->mode !== AuthenticationMode::NONE) {
            throw new InvalidArgumentException(message: 'Guest context must use AuthenticationMode::NONE.');
        }
    }

    public static function guest(string $reason = null) : self
    {
        return new self(
            authenticated: false,
            mode         : AuthenticationMode::NONE,
            reason       : $reason,
        );
    }

    public static function authenticated(
        AuthenticatedUser $user,
        AuthenticationMode $mode,
        #[SensitiveParameter]
        string            $sessionId = null,
        #[SensitiveParameter]
        string            $accessTokenId = null,
        #[SensitiveParameter]
        DateTimeImmutable $accessTokenExpiresAt = null,
        #[SensitiveParameter]
        string            $refreshTokenId = null,
        #[SensitiveParameter]
        string            $refreshTokenFamilyId = null,
        DateTimeImmutable $mfaVerifiedAt = null,
        bool $phishingResistant = false,
    ): self {
        return new self(
            authenticated       : true,
            mode                : $mode,
            user                : $user,
            sessionId           : $sessionId,
            accessTokenId       : $accessTokenId,
            accessTokenExpiresAt: $accessTokenExpiresAt,
            refreshTokenId      : $refreshTokenId,
            refreshTokenFamilyId: $refreshTokenFamilyId,
            mfaVerifiedAt       : $mfaVerifiedAt,
            phishingResistant   : $phishingResistant,
        );
    }

    public function isAuthenticated(): bool
    {
        return $this->authenticated;
    }

    public function mode(): AuthenticationMode
    {
        return $this->mode;
    }

    public function user(): ?AuthenticatedUser
    {
        return $this->user;
    }

    public function reason(): ?string
    {
        return $this->reason;
    }

    public function sessionId(): ?string
    {
        return $this->sessionId;
    }

    public function accessTokenId(): ?string
    {
        return $this->accessTokenId;
    }

    public function accessTokenExpiresAt(): ?DateTimeImmutable
    {
        return $this->accessTokenExpiresAt;
    }

    public function refreshTokenId(): ?string
    {
        return $this->refreshTokenId;
    }

    public function refreshTokenFamilyId(): ?string
    {
        return $this->refreshTokenFamilyId;
    }

    public function mfaVerifiedAt(): ?DateTimeImmutable
    {
        return $this->mfaVerifiedAt;
    }

    public function isPhishingResistant(): bool
    {
        return $this->phishingResistant;
    }
}
