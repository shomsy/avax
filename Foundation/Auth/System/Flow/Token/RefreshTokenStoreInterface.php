<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Token;

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
        UserId                 $userId,
        DateTimeImmutable      $expiresAt,
        string|null            $familyId = null,
        DateTimeImmutable|null $mfaVerifiedAt = null,
        string|null            $clientId = null,
        array                  $scopes = []
    ) : IssuedRefreshToken;

    public function find(string $plainToken) : RefreshTokenRecord|null;

    public function markRotated(string $tokenId, string $replacementTokenId) : void;

    public function revokeFamily(string $familyId) : void;

    public function revokeUser(UserId $userId) : void;
}
