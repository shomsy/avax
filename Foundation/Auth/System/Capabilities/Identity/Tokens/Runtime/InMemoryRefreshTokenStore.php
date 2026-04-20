<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Tokens\Runtime;

use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use DateTimeImmutable;
use Random\RandomException;
use SensitiveParameter;

/**
 * In-memory refresh token storage for tests and demos.
 */
final class InMemoryRefreshTokenStore implements RefreshTokenStoreInterface
{
    /** @var array<string, string> */
    private array $hashToTokenId = [];

    /** @var array<string, RefreshTokenRecord> */
    private array $records = [];

    /**
     * @param list<string> $scopes
     *
     * @throws RandomException
     * @throws RandomException
     * @throws RandomException
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
    ) : IssuedRefreshToken
    {
        $plainToken = bin2hex(random_bytes(32));
        $tokenId    = bin2hex(random_bytes(16));
        $familyId   ??= bin2hex(random_bytes(16));

        $record = new RefreshTokenRecord(
            tokenId          : $tokenId,
            familyId         : $familyId,
            userId           : $userId,
            expiresAt        : $expiresAt,
            mfaVerifiedAt    : $mfaVerifiedAt,
            phishingResistant: $phishingResistant,
            clientId         : $clientId,
            scopes           : array_values($scopes),
            senderConstraint : $senderConstraint
        );

        $this->records[$tokenId]                                   = $record;
        $this->hashToTokenId[$this->hash(plainToken: $plainToken)] = $tokenId;

        return new IssuedRefreshToken(
            token            : $plainToken,
            tokenId          : $tokenId,
            familyId         : $familyId,
            userId           : $userId,
            expiresAt        : $expiresAt,
            mfaVerifiedAt    : $mfaVerifiedAt,
            phishingResistant: $phishingResistant,
            clientId         : $clientId,
            scopes           : array_values($scopes),
            senderConstraint : $senderConstraint
        );
    }

    private function hash(#[SensitiveParameter] string $plainToken) : string
    {
        return hash('sha256', $plainToken);
    }

    public function find(#[SensitiveParameter] string $plainToken) : RefreshTokenRecord|null
    {
        $tokenId = $this->hashToTokenId[$this->hash(plainToken: $plainToken)] ?? null;

        if ($tokenId === null) {
            return null;
        }

        return $this->records[$tokenId] ?? null;
    }

    public function markRotated(#[SensitiveParameter] string $tokenId, #[SensitiveParameter] string $replacementTokenId) : void
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
            mfaVerifiedAt     : $record->mfaVerifiedAt,
            phishingResistant : $record->phishingResistant,
            clientId          : $record->clientId,
            scopes            : $record->scopes,
            senderConstraint  : $record->senderConstraint
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
                mfaVerifiedAt     : $record->mfaVerifiedAt,
                phishingResistant : $record->phishingResistant,
                clientId          : $record->clientId,
                scopes            : $record->scopes,
                senderConstraint  : $record->senderConstraint
            );
        }
    }

    public function revokeUser(UserId $userId) : void
    {
        foreach ($this->records as $tokenId => $record) {
            if ($record->userId->equals(other: $userId)) {
                $this->records[$tokenId] = new RefreshTokenRecord(
                    tokenId           : $record->tokenId,
                    familyId          : $record->familyId,
                    userId            : $record->userId,
                    expiresAt         : $record->expiresAt,
                    replacementTokenId: $record->replacementTokenId,
                    revoked           : true,
                    mfaVerifiedAt     : $record->mfaVerifiedAt,
                    phishingResistant : $record->phishingResistant,
                    clientId          : $record->clientId,
                    scopes            : $record->scopes,
                    senderConstraint  : $record->senderConstraint
                );
            }
        }
    }
}
