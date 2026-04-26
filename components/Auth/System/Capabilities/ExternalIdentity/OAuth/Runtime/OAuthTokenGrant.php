<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime;

use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraint;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * OAuth token endpoint result.
 */
final readonly class OAuthTokenGrant
{
    public string $tokenType;
    /** @var list<string> */
    public array $scopes;

    /**
     * @param list<string> $scopes
     */
    public function __construct(
        #[SensitiveParameter] public string            $accessToken,
        #[SensitiveParameter] public DateTimeImmutable $accessTokenExpiresAt,
        #[SensitiveParameter] public string|null       $refreshToken,
        #[SensitiveParameter] public string|null       $idToken,
        public string                                  $clientId,
        public int|null                                $userId,
        array|null                                     $scopes = null,
        #[SensitiveParameter] string|null              $tokenType = null,
        public OAuthSenderConstraint|null              $senderConstraint = null,
        public string|null                             $subject = null,
        public string|null                             $audience = null,
        public bool                                    $workloadIdentity = false
    )
    {
        $scopes          ??= [];
        $tokenType       ??= 'Bearer';
        $this->scopes    = $scopes;
        $this->tokenType = $tokenType;
    }

    public function expiresIn(DateTimeImmutable $moment) : int
    {
        return max(0, $this->accessTokenExpiresAt->getTimestamp() - $moment->getTimestamp());
    }

    public function scopeString() : string
    {
        return implode(separator: ' ', array: $this->scopes);
    }
}
