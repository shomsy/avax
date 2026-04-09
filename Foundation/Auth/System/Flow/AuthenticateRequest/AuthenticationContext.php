<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\AuthenticateRequest;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Immutable authentication context captured at the ingress boundary.
 */
final readonly class AuthenticationContext
{
    public function __construct(
        private bool                   $authenticated,
        private AuthenticationMode     $mode,
        private AuthenticatedUser|null $user = null,
        private string|null            $reason = null,
        private string|null            $sessionId = null,
        private string|null            $accessTokenId = null,
        private DateTimeImmutable|null $accessTokenExpiresAt = null,
        private string|null            $refreshTokenId = null,
        private DateTimeImmutable|null $mfaVerifiedAt = null
    )
    {
        if ($this->authenticated && $this->user === null) {
            throw new InvalidArgumentException('Authenticated context requires a user.');
        }

        if (! $this->authenticated && $this->mode !== AuthenticationMode::NONE) {
            throw new InvalidArgumentException('Guest context must use AuthenticationMode::NONE.');
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
        AuthenticatedUser      $user,
        AuthenticationMode     $mode,
        string|null            $sessionId = null,
        string|null            $accessTokenId = null,
        DateTimeImmutable|null $accessTokenExpiresAt = null,
        string|null            $refreshTokenId = null,
        DateTimeImmutable|null $mfaVerifiedAt = null
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
            mfaVerifiedAt       : $mfaVerifiedAt
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

    public function mfaVerifiedAt() : DateTimeImmutable|null
    {
        return $this->mfaVerifiedAt;
    }
}
