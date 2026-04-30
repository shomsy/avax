<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use DateTimeImmutable;
use SensitiveParameter;

final readonly class RotatingOidcProvider implements OidcProviderInterface
{
    /**
     * @param list<OidcProviderInterface> $verificationProviders
     */
    public function __construct(private OidcProviderInterface $activeProvider, private array $verificationProviders = []) {}

    public function issueIdToken(
        User              $user,
        string            $clientId,
        array             $scopes,
        string            $nonce = null,
        DateTimeImmutable $authenticatedAt = null,
        #[SensitiveParameter]
        string            $sessionId = null,
        bool              $phishingResistant = false,
    ) : OidcIdToken
    {
        return $this->activeProvider->issueIdToken(
            user             : $user,
            clientId         : $clientId,
            scopes           : $scopes,
            nonce            : $nonce,
            authenticatedAt  : $authenticatedAt,
            sessionId        : $sessionId,
            phishingResistant: $phishingResistant,
        );
    }

    public function issueJwt(array $claims) : string
    {
        return $this->activeProvider->issueJwt(claims: $claims);
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

        return new OidcJsonWebKeySet(keys: array_values(array: $keys));
    }

    /**
     * @return list<OidcProviderInterface>
     */
    private function providers() : array
    {
        return [$this->activeProvider, ...$this->verificationProviders];
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

    public function resolveJwt(#[SensitiveParameter] string $jwt) : array|null
    {
        foreach ($this->providers() as $provider) {
            $claims = $provider->resolveJwt(jwt: $jwt);

            if ($claims !== null) {
                return $claims;
            }
        }

        return null;
    }
}
