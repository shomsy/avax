<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Oidc;

use Avax\Auth\System\Capability\User\User;
use DateTimeImmutable;
use SensitiveParameter;

final readonly class RotatingOidcProvider implements OidcProviderInterface
{
    /**
     * @param list<OidcProviderInterface> $verificationProviders
     */
    public function __construct(
        private OidcProviderInterface $activeProvider,
        private array $verificationProviders = []
    ) {}

    public function issueIdToken(
        User $user,
        string $clientId,
        array $scopes,
        string|null $nonce = null,
        DateTimeImmutable|null $authenticatedAt = null,
        bool $phishingResistant = false
    ) : OidcIdToken
    {
        return $this->activeProvider->issueIdToken(
            user               : $user,
            clientId           : $clientId,
            scopes             : $scopes,
            nonce              : $nonce,
            authenticatedAt    : $authenticatedAt,
            phishingResistant  : $phishingResistant
        );
    }

    public function readProviderMetadata() : OidcProviderMetadata
    {
        return $this->activeProvider->readProviderMetadata();
    }

    public function readJsonWebKeySet() : OidcJsonWebKeySet
    {
        $keys = [];

        foreach ($this->providers() as $provider) {
            foreach ($provider->readJsonWebKeySet()->keys as $key) {
                $keys[$key->keyId] = $key;
            }
        }

        return new OidcJsonWebKeySet(keys: array_values($keys));
    }

    public function subjectIdentifier(User $user, string $clientId) : string
    {
        return $this->activeProvider->subjectIdentifier(user: $user, clientId: $clientId);
    }

    public function resolveIdToken(#[SensitiveParameter] string $idToken) : array|null
    {
        foreach ($this->providers() as $provider) {
            $claims = $provider->resolveIdToken(idToken: $idToken);

            if ($claims !== null) {
                return $claims;
            }
        }

        return null;
    }

    /**
     * @return list<OidcProviderInterface>
     */
    private function providers() : array
    {
        return [$this->activeProvider, ...$this->verificationProviders];
    }
}
