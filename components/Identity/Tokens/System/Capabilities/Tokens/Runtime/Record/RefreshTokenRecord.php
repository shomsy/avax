<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Record;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraint;
use DateTimeImmutable;
use SensitiveParameter;

final readonly class RefreshTokenRecord
{
    /**
     * @param  list<string>  $scopes
     */
    public function __construct(
        #[SensitiveParameter]
        public string $tokenId,
        public string $familyId,
        public UserId $userId,
        public DateTimeImmutable $expiresAt,
        public ?DateTimeImmutable $mfaVerifiedAt = null,
        public bool $phishingResistant = false,
        public ?string $clientId = null,
        public array $scopes = [],
        public ?string $replacementId = null,
        public ?OAuthSenderConstraint $senderConstraint = null,
        public bool $revoked = false,
    ) {}

    public function isExpired(): bool
    {
        return $this->expiresAt < new DateTimeImmutable;
    }

    public function isExpiredAt(DateTimeImmutable $moment): bool
    {
        return $this->expiresAt < $moment;
    }

    public function wasRotated(): bool
    {
        return $this->isRotated();
    }

    public function isRotated(): bool
    {
        return $this->replacementId !== null;
    }

    public function markRotated(string $replacementId): self
    {
        return new self(
            tokenId          : $this->tokenId,
            familyId         : $this->familyId,
            userId           : $this->userId,
            expiresAt        : $this->expiresAt,
            mfaVerifiedAt    : $this->mfaVerifiedAt,
            phishingResistant: $this->phishingResistant,
            clientId         : $this->clientId,
            scopes           : $this->scopes,
            replacementId    : $replacementId,
            senderConstraint : $this->senderConstraint,
            revoked          : $this->revoked,
        );
    }
}
