<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Oidc;

use Avax\Auth\System\Capability\User\User;
use DateTimeImmutable;
use SensitiveParameter;

interface OidcProviderInterface
{
    /**
     * @param list<string> $scopes
     */
    public function issueIdToken(
        User $user,
        string $clientId,
        array $scopes,
        string|null $nonce = null,
        DateTimeImmutable|null $authenticatedAt = null,
        string|null $sessionId = null,
        bool $phishingResistant = false
    ) : OidcIdToken;

    /**
     * @param array<string, mixed> $claims
     */
    public function issueJwt(array $claims) : string;

    public function readProviderMetadata() : OidcProviderMetadata;

    public function readJsonWebKeySet() : OidcJsonWebKeySet;

    public function subjectIdentifier(User $user, string $clientId) : string;

    /**
     * @return array<string, mixed>|null
     */
    public function resolveIdToken(#[SensitiveParameter] string $idToken) : array|null;

    /**
     * @return array<string, mixed>|null
     */
    public function resolveJwt(#[SensitiveParameter] string $jwt) : array|null;
}
