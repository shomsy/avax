<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use DateTimeImmutable;
use SensitiveParameter;

final readonly class RotatingOidcProvider implements OidcProviderInterface
{
    /**
     * @param list<OidcProviderInterface> $verificationProviders
     */
    public function __construct(private OidcProviderInterface $oidcProvider, private array $verificationProviders = []) {}

    public function issueIdToken(
        User $user,
        string $clientId,
        array $scopes,
        ?string            $nonce = null,
        ?DateTimeImmutable $authenticatedAt = null,
        #[SensitiveParameter]
        ?string            $sessionId = null,
        bool $phishingResistant = false,
    ): OidcIdToken {
        return $this->oidcProvider->issueIdToken(
            user             : $user,
            clientId         : $clientId,
            scopes           : $scopes,
            nonce            : $nonce,
            authenticatedAt  : $authenticatedAt,
            sessionId        : $sessionId,
            phishingResistant: $phishingResistant,
        );
    }

    public function issueJwt(array $claims): string
    {
        return $this->oidcProvider->issueJwt(claims: $claims);
    }

    public function readProviderMetadata(): OidcProviderMetadata
    {
        return $this->oidcProvider->readProviderMetadata();
    }

    public function readJsonWebKeySet(): OidcJsonWebKeySet
    {
        $keys = [];

        foreach ($this->providers() as $oidcProvider) {
            foreach ($oidcProvider->readJsonWebKeySet()->keys as $key) {
                $keys[$key->keyId] = $key;
            }
        }

        return new OidcJsonWebKeySet(keys: array_values(array: $keys));
    }

    /**
     * @return list<OidcProviderInterface>
     */
    private function providers(): array
    {
        return [$this->oidcProvider, ...$this->verificationProviders];
    }

    public function subjectIdentifier(User $user, string $clientId): string
    {
        return $this->oidcProvider->subjectIdentifier(user: $user, clientId: $clientId);
    }

    public function resolveIdToken(#[SensitiveParameter] string $idToken): ?array
    {
        foreach ($this->providers() as $oidcProvider) {
            $claims = $oidcProvider->resolveIdToken(idToken: $idToken);

            if ($claims !== null) {
                return $claims;
            }
        }

        return null;
    }

    public function resolveJwt(#[SensitiveParameter] string $jwt): ?array
    {
        foreach ($this->providers() as $oidcProvider) {
            $claims = $oidcProvider->resolveJwt(jwt: $jwt);

            if ($claims !== null) {
                return $claims;
            }
        }

        return null;
    }
}
