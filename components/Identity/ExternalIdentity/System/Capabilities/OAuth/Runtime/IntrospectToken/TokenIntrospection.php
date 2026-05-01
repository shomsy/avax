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
        public bool $active,
        public ?string $clientId = null,
        public ?int $userId = null,
        array $scopes = null,
        public ?DateTimeImmutable $expiresAt = null,
        public ?DateTimeImmutable $mfaVerifiedAt = null,
        bool  $phishingResistant = null,
        public ?OAuthSenderConstraint $senderConstraint = null,
        public ?string $subject = null,
        public ?string $audience = null,
        public ?string $issuer = null,
        public bool $workloadIdentity = false,
    ) {
        $scopes       ??= [];
        $phishingResistant ??= false;
        $this->scopes = $scopes;
        $this->phishingResistant = $phishingResistant;
    }

    public static function inactive(): self
    {
        return new self(active: false);
    }

    public function scopeString(): string
    {
        return implode(separator: ' ', array: $this->scopes);
    }
}
