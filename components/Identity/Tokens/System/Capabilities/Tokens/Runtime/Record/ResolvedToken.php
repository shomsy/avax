<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Record;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Support\SenderConstraint\OAuthSenderConstraint;
use DateTimeImmutable;
use SensitiveParameter;

final readonly class ResolvedToken
{
    /**
     * @param list<string> $scopes
     */
    public function __construct(
        public User                       $user,
        #[SensitiveParameter]
        public string                     $tokenId,
        public DateTimeImmutable          $expiresAt,
        public DateTimeImmutable|null     $mfaVerifiedAt = null,
        public bool                       $phishingResistant = false,
        public string|null                $clientId = null,
        public array                      $scopes = [],
        public OAuthSenderConstraint|null $senderConstraint = null,
        public string|null                $familyId = null,
    ) {}

    public function isExpired() : bool
    {
        return $this->expiresAt < new DateTimeImmutable();
    }
}
