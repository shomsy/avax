<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Store;

use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraint;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\IssuedRefreshToken;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\RefreshTokenRecord;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use DateTimeImmutable;
use Random\RandomException;
use SensitiveParameter;

final class InMemoryRefreshTokenStore implements RefreshTokenStoreInterface
{
    /** @var array<string, RefreshTokenRecord> */
    private array $tokens = [];

    /**
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
        $tokenId  = bin2hex(string: random_bytes(length: 32));
        $familyId ??= bin2hex(string: random_bytes(length: 16));

        $record = new RefreshTokenRecord(
            tokenId          : $tokenId,
            familyId         : $familyId,
            userId           : $userId,
            expiresAt        : $expiresAt,
            mfaVerifiedAt    : $mfaVerifiedAt,
            phishingResistant: $phishingResistant,
            clientId         : $clientId,
            scopes           : $scopes,
            senderConstraint : $senderConstraint
        );

        $this->tokens[$tokenId] = $record;

        return new IssuedRefreshToken(
            token            : $tokenId, // In memory, tokenId IS the token
            tokenId          : $tokenId,
            expiresAt        : $expiresAt,
            familyId         : $familyId,
            mfaVerifiedAt    : $mfaVerifiedAt,
            phishingResistant: $phishingResistant
        );
    }

    public function find(#[SensitiveParameter] string $plainToken) : RefreshTokenRecord|null
    {
        return $this->tokens[$plainToken] ?? null;
    }

    public function markRotated(#[SensitiveParameter] string $tokenId, #[SensitiveParameter] string $replacementTokenId) : void
    {
        if (isset($this->tokens[$tokenId])) {
            $this->tokens[$tokenId] = $this->tokens[$tokenId]->markRotated(replacementId: $replacementTokenId);
        }
    }

    public function revokeFamily(string $familyId) : void
    {
        foreach ($this->tokens as $id => $token) {
            if ($token->familyId === $familyId) {
                unset($this->tokens[$id]);
            }
        }
    }

    public function revokeUser(UserId $userId) : void
    {
        foreach ($this->tokens as $id => $token) {
            if ($token->userId->value === $userId->value) {
                unset($this->tokens[$id]);
            }
        }
    }
}
