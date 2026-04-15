<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Token;

use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capability\User\UserId;
use DateTimeImmutable;

/**
 * Persisted refresh token ownership and rotation state.
 */
interface RefreshTokenStoreInterface
{
    /**
     * @param list<string> $scopes
     */
    public function issue(
        UserId                     $userId,
        DateTimeImmutable          $expiresAt,
        string|null                $familyId = null,
        DateTimeImmutable|null     $mfaVerifiedAt = null,
        bool                       $phishingResistant = false,
        string|null                $clientId = null,
        array                      $scopes = [],
        OAuthSenderConstraint|null $senderConstraint = null
    ) : IssuedRefreshToken;

    public function find(string $plainToken) : RefreshTokenRecord|null;

    public function markRotated(string $tokenId, string $replacementTokenId) : void;

    public function revokeFamily(string $familyId) : void;

    public function revokeUser(UserId $userId) : void;
}
