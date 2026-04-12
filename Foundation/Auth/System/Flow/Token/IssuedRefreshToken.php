<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Token;

use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capability\User\UserId;
use DateTimeImmutable;

/**
 * Opaque refresh token pair returned to clients.
 */
final readonly class IssuedRefreshToken
{
    /**
     * @param list<string> $scopes
     */
    public function __construct(
        public string                 $token,
        public string                 $tokenId,
        public string                 $familyId,
        public UserId                 $userId,
        public DateTimeImmutable      $expiresAt,
        public DateTimeImmutable|null $mfaVerifiedAt = null,
        public bool                   $phishingResistant = false,
        public string|null            $clientId = null,
        public array                  $scopes = [],
        public OAuthSenderConstraint|null $senderConstraint = null
    ) {}
}
