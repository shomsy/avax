<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\IntrospectToken;

use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraint;
use DateTimeImmutable;

final readonly class TokenIntrospection
{
    /**
     * @param list<string> $scopes
     */
    public function __construct(
        public bool                   $active,
        public string|null            $clientId = null,
        public int|null               $userId = null,
        public array                  $scopes = [],
        public DateTimeImmutable|null $expiresAt = null,
        public DateTimeImmutable|null $mfaVerifiedAt = null,
        public bool                   $phishingResistant = false,
        public OAuthSenderConstraint|null $senderConstraint = null
    ) {}

    public static function inactive() : self
    {
        return new self(active: false);
    }

    public function scopeString() : string
    {
        return implode(' ', $this->scopes);
    }
}
