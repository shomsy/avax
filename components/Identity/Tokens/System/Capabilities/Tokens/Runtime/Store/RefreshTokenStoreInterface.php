<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraint;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Record\IssuedRefreshToken;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Record\RefreshTokenRecord;
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
        UserId $userId,
        DateTimeImmutable $expiresAt,
        ?string                $familyId = null,
        ?DateTimeImmutable     $mfaVerifiedAt = null,
        bool $phishingResistant = false,
        ?string                $clientId = null,
        array $scopes = [],
        ?OAuthSenderConstraint $oAuthSenderConstraint = null,
    ): IssuedRefreshToken;

    public function find(#[SensitiveParameter] string $plainToken): ?RefreshTokenRecord;

    public function markRotated(#[SensitiveParameter] string $tokenId, #[SensitiveParameter] string $replacementTokenId): void;

    public function revokeFamily(string $familyId): void;

    public function revokeUser(UserId $userId): void;
}
