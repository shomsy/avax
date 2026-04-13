<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Token;

use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capability\User\UserId;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Stored refresh token state used for rotation and revocation checks.
 */
final readonly class RefreshTokenRecord
{
    /**
     * @param list<string> $scopes
     */
    public function __construct(
        #[SensitiveParameter] public string      $tokenId,
        public string                            $familyId,
        public UserId                            $userId,
        public DateTimeImmutable                 $expiresAt,
        #[SensitiveParameter] public string|null $replacementTokenId = null,
        public bool                              $revoked = false,
        public DateTimeImmutable|null            $mfaVerifiedAt = null,
        public bool                              $phishingResistant = false,
        public string|null                       $clientId = null,
        public array                             $scopes = [],
        public OAuthSenderConstraint|null        $senderConstraint = null
    ) {}

    public function isExpiredAt(DateTimeImmutable $moment) : bool
    {
        return $this->expiresAt <= $moment;
    }

    public function wasRotated() : bool
    {
        return $this->replacementTokenId !== null;
    }
}
