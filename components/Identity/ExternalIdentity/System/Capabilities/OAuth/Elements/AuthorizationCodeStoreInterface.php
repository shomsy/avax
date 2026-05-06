<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use DateTimeImmutable;

/**
 * Shared authorization code persistence for OAuth flows.
 */
interface AuthorizationCodeStoreInterface
{
    /**
     * @param  list<string>  $scopes
     */
    public function issue(
        UserId $userId,
        string $clientId,
        string $redirectUri,
        array $scopes,
        DateTimeImmutable $expiresAt,
        ?string $state = null,
        ?string $nonce = null,
        ?string $codeChallenge = null,
        ?PkceMethod $pkceMethod = null,
        ?DateTimeImmutable $mfaVerifiedAt = null,
        bool $phishingResistant = false,
    ): IssuedAuthorizationCode;

    public function find(string $plainCode): ?AuthorizationCodeRecord;

    public function markUsed(string $codeId, DateTimeImmutable $usedAt): void;
}
