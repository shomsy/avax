<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraint;
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
        #[SensitiveParameter]
        public string                 $accessToken,
        #[SensitiveParameter]
        public DateTimeImmutable      $accessTokenExpiresAt,
        #[SensitiveParameter]
        public ?string                $refreshToken,
        #[SensitiveParameter]
        public ?string                $idToken,
        public string                 $clientId,
        public ?int                   $userId,
        ?array                        $scopes = null,
        #[SensitiveParameter]
        ?string                       $tokenType = null,
        public ?OAuthSenderConstraint $senderConstraint = null,
        public ?string                $subject = null,
        public ?string                $audience = null,
        public bool                   $workloadIdentity = false,
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
