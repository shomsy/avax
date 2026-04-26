<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Store;

use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\IssuedRefreshToken;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\RefreshTokenRecord;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use DateTimeImmutable;
use SensitiveParameter;

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

    public function find(#[SensitiveParameter] string $plainToken) : RefreshTokenRecord|null;

    public function markRotated(#[SensitiveParameter] string $tokenId, #[SensitiveParameter] string $replacementTokenId) : void;

    public function revokeFamily(string $familyId) : void;

    public function revokeUser(UserId $userId) : void;
}
