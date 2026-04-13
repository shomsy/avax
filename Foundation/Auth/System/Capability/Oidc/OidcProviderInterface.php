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
        bool $phishingResistant = false
    ) : OidcIdToken;

    public function readProviderMetadata() : OidcProviderMetadata;

    public function readJsonWebKeySet() : OidcJsonWebKeySet;

    /**
     * @return array<string, mixed>|null
     */
    public function resolveIdToken(#[SensitiveParameter] string $idToken) : array|null;
}
