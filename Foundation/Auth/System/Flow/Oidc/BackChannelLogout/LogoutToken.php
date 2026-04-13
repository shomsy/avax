<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\BackChannelLogout;

/**
 * Represents an OIDC back-channel logout token (JWT).
 */
final readonly class LogoutToken
{
    public function __construct(
        public string $jwtId,
        public string $issuer,
        public string $audience,
        public string $subject,
        public string $eventId,
        public int    $issuedAt,
        public int    $expiresAt,
        public string $sessionId
    ) {}

    public function isExpired(int $currentTime) : bool
    {
        return $this->expiresAt < $currentTime;
    }

    public function isIssuedInFuture(int $currentTime) : bool
    {
        return $this->issuedAt > $currentTime;
    }

    public function hasSessionId(string $sessionId) : bool
    {
        return $this->sessionId === $sessionId;
    }
}