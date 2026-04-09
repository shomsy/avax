<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Token;

use Avax\Auth\System\Capability\User\UserId;
use DateTimeImmutable;

/**
 * In-memory refresh token storage for tests and demos.
 */
final class InMemoryRefreshTokenStore implements RefreshTokenStoreInterface
{
    /** @var array<string, string> */
    private array $hashToTokenId = [];

    /** @var array<string, RefreshTokenRecord> */
    private array $records = [];

    public function issue(
        UserId                 $userId,
        DateTimeImmutable      $expiresAt,
        string|null            $familyId = null,
        DateTimeImmutable|null $mfaVerifiedAt = null
    ) : IssuedRefreshToken
    {
        $plainToken = bin2hex(random_bytes(32));
        $tokenId    = bin2hex(random_bytes(16));
        $familyId   ??= bin2hex(random_bytes(16));

        $record = new RefreshTokenRecord(
            tokenId      : $tokenId,
            familyId     : $familyId,
            userId       : $userId,
            expiresAt    : $expiresAt,
            mfaVerifiedAt: $mfaVerifiedAt
        );

        $this->records[$tokenId]                       = $record;
        $this->hashToTokenId[$this->hash($plainToken)] = $tokenId;

        return new IssuedRefreshToken(
            token        : $plainToken,
            tokenId      : $tokenId,
            familyId     : $familyId,
            userId       : $userId,
            expiresAt    : $expiresAt,
            mfaVerifiedAt: $mfaVerifiedAt
        );
    }

    private function hash(string $plainToken) : string
    {
        return hash('sha256', $plainToken);
    }

    public function find(string $plainToken) : RefreshTokenRecord|null
    {
        $tokenId = $this->hashToTokenId[$this->hash($plainToken)] ?? null;

        if ($tokenId === null) {
            return null;
        }

        return $this->records[$tokenId] ?? null;
    }

    public function markRotated(string $tokenId, string $replacementTokenId) : void
    {
        $record = $this->records[$tokenId] ?? null;

        if ($record === null) {
            return;
        }

        $this->records[$tokenId] = new RefreshTokenRecord(
            tokenId           : $record->tokenId,
            familyId          : $record->familyId,
            userId            : $record->userId,
            expiresAt         : $record->expiresAt,
            replacementTokenId: $replacementTokenId,
            revoked           : $record->revoked,
            mfaVerifiedAt     : $record->mfaVerifiedAt
        );
    }

    public function revokeFamily(string $familyId) : void
    {
        foreach ($this->records as $tokenId => $record) {
            if ($record->familyId !== $familyId) {
                continue;
            }

            $this->records[$tokenId] = new RefreshTokenRecord(
                tokenId           : $record->tokenId,
                familyId          : $record->familyId,
                userId            : $record->userId,
                expiresAt         : $record->expiresAt,
                replacementTokenId: $record->replacementTokenId,
                revoked           : true,
                mfaVerifiedAt     : $record->mfaVerifiedAt
            );
        }
    }

    public function revokeUser(UserId $userId) : void
    {
        foreach ($this->records as $tokenId => $record) {
            if ($record->userId->equals($userId)) {
                $this->records[$tokenId] = new RefreshTokenRecord(
                    tokenId           : $record->tokenId,
                    familyId          : $record->familyId,
                    userId            : $record->userId,
                    expiresAt         : $record->expiresAt,
                    replacementTokenId: $record->replacementTokenId,
                    revoked           : true,
                    mfaVerifiedAt     : $record->mfaVerifiedAt
                );
            }
        }
    }
}
