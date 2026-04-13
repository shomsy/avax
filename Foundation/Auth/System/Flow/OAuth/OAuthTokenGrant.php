<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth;

use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraint;
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
        public string|null       $idToken,
        public string            $clientId,
        public int|null          $userId,
        public array             $scopes = [],
        public string            $tokenType = 'Bearer',
        public OAuthSenderConstraint|null $senderConstraint = null,
        public string|null       $subject = null,
        public string|null       $audience = null,
        public bool              $workloadIdentity = false
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
