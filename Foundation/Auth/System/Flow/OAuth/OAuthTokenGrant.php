<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth;

use DateTimeImmutable;

/**
 * OAuth token endpoint result.
 */
final readonly class OAuthTokenGrant
{
    /**
     * @param list<string> $scopes
     */
    public function __construct(
        public string            $accessToken,
        public DateTimeImmutable $accessTokenExpiresAt,
        public string|null       $refreshToken,
        public string            $clientId,
        public int               $userId,
        public array             $scopes = [],
        public string            $tokenType = 'Bearer'
    ) {}

    public function expiresIn(DateTimeImmutable $moment) : int
    {
        return max(0, $this->accessTokenExpiresAt->getTimestamp() - $moment->getTimestamp());
    }

    public function scopeString() : string
    {
        return implode(' ', $this->scopes);
    }
}
