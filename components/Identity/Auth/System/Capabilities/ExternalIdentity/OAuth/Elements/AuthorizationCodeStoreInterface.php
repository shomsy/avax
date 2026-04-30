<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use DateTimeImmutable;

/**
 * Shared authorization code persistence for OAuth flows.
 */
interface AuthorizationCodeStoreInterface
{
    /**
     * @param list<string> $scopes
     */
    public function issue(
        UserId                 $userId,
        string                 $clientId,
        string                 $redirectUri,
        array                  $scopes,
        DateTimeImmutable      $expiresAt,
        string|null            $state = null,
        string|null            $nonce = null,
        string|null            $codeChallenge = null,
        PkceMethod|null        $codeChallengeMethod = null,
        DateTimeImmutable|null $mfaVerifiedAt = null,
        bool                   $phishingResistant = false,
    ) : IssuedAuthorizationCode;

    public function find(string $plainCode) : AuthorizationCodeRecord|null;

    public function markUsed(string $codeId, DateTimeImmutable $usedAt) : void;
}
