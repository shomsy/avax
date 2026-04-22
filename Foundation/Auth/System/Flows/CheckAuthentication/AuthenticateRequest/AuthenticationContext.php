<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest;

use DateTimeImmutable;
use InvalidArgumentException;
use SensitiveParameter;

/**
 * Immutable authentication context captured at the ingress boundary.
 */
final readonly class AuthenticationContext
{
    public function __construct(
        private bool                                         $authenticated,
        private AuthenticationMode                           $mode,
        private AuthenticatedUser|null                       $user = null,
        private string|null                                  $reason = null,
        #[SensitiveParameter] private string|null            $sessionId = null,
        #[SensitiveParameter] private string|null            $accessTokenId = null,
        #[SensitiveParameter] private DateTimeImmutable|null $accessTokenExpiresAt = null,
        #[SensitiveParameter] private string|null            $refreshTokenId = null,
        #[SensitiveParameter] private string|null            $refreshTokenFamilyId = null,
        private DateTimeImmutable|null                       $mfaVerifiedAt = null,
        private bool                                         $phishingResistant = false
    )
    {
        if ($this->authenticated && $this->user === null) {
            throw new InvalidArgumentException(message: 'Authenticated context requires a user.');
        }

        if (! $this->authenticated && $this->mode !== AuthenticationMode::NONE) {
            throw new InvalidArgumentException(message: 'Guest context must use AuthenticationMode::NONE.');
        }
    }

    public static function guest(string|null $reason = null) : self
    {
        return new self(
            authenticated: false,
            mode         : AuthenticationMode::NONE,
            reason       : $reason
        );
    }

    public static function authenticated(
        AuthenticatedUser                            $user,
        AuthenticationMode                           $mode,
        #[SensitiveParameter] string|null            $sessionId = null,
        #[SensitiveParameter] string|null            $accessTokenId = null,
        #[SensitiveParameter] DateTimeImmutable|null $accessTokenExpiresAt = null,
        #[SensitiveParameter] string|null            $refreshTokenId = null,
        #[SensitiveParameter] string|null            $refreshTokenFamilyId = null,
        DateTimeImmutable|null                       $mfaVerifiedAt = null,
        bool                                         $phishingResistant = false
    ) : self
    {
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
            phishingResistant   : $phishingResistant
        );
    }

    public function isAuthenticated() : bool
    {
        return $this->authenticated;
    }

    public function mode() : AuthenticationMode
    {
        return $this->mode;
    }

    public function user() : AuthenticatedUser|null
    {
        return $this->user;
    }

    public function reason() : string|null
    {
        return $this->reason;
    }

    public function sessionId() : string|null
    {
        return $this->sessionId;
    }

    public function accessTokenId() : string|null
    {
        return $this->accessTokenId;
    }

    public function accessTokenExpiresAt() : DateTimeImmutable|null
    {
        return $this->accessTokenExpiresAt;
    }

    public function refreshTokenId() : string|null
    {
        return $this->refreshTokenId;
    }

    public function refreshTokenFamilyId() : string|null
    {
        return $this->refreshTokenFamilyId;
    }

    public function mfaVerifiedAt() : DateTimeImmutable|null
    {
        return $this->mfaVerifiedAt;
    }

    public function isPhishingResistant() : bool
    {
        return $this->phishingResistant;
    }
}
