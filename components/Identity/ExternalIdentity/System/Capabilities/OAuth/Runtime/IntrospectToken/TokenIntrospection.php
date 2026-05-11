<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\IntrospectToken;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraint;
use DateTimeImmutable;

final readonly class TokenIntrospection
{
    public bool $phishingResistant;

    /** @var list<string> */
    public array $scopes;

    /**
     * @param list<string> $scopes
     */
    public function __construct(
        public bool                   $active,
        public string|null                $clientId = null,
        public int|null                   $userId = null, array|null $scopes = null,
        public DateTimeImmutable|null     $expiresAt = null,
        public DateTimeImmutable|null     $mfaVerifiedAt = null, bool|null $phishingResistant = null,
        public OAuthSenderConstraint|null $senderConstraint = null,
        public string|null                $subject = null,
        public string|null                $audience = null,
        public string|null                $issuer = null,
        public bool                   $workloadIdentity = false,
    )
    {
        $scopes                  ??= [];
        $phishingResistant       ??= false;
        $this->scopes            = $scopes;
        $this->phishingResistant = $phishingResistant;
    }

    public static function inactive() : self
    {
        return new self(active: false);
    }

    public function scopeString() : string
    {
        return implode(separator: ' ', array: $this->scopes);
    }
}
