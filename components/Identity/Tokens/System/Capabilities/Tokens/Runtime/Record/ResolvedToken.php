<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Record;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraint;
use DateTimeImmutable;
use SensitiveParameter;

final readonly class ResolvedToken
{
    /**
     * @param  list<string>  $scopes
     */
    public function __construct(
        public User $user,
        #[SensitiveParameter]
        public string $tokenId,
        public DateTimeImmutable $expiresAt,
        public ?DateTimeImmutable $mfaVerifiedAt = null,
        public bool $phishingResistant = false,
        public ?string $clientId = null,
        public array $scopes = [],
        public ?OAuthSenderConstraint $senderConstraint = null,
        public ?string $familyId = null,
    ) {
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new DateTimeImmutable();
    }
}
