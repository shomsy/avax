<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration\Assembly;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\AuthorizationCodeStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClientRegistryInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\OidcProviderInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\OidcRequestObjectStoreInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use SensitiveParameter;

/**
 * Configuration sub-builder for OAuth, OIDC, and token-related dependencies.
 *
 * Owns: OAuth client registry, authorization code store,
 * OIDC provider, OIDC request object store, and refresh token store.
 */
final class BuildOAuthGraph
{
    private OAuthClientRegistryInterface|null $oAuthClientRegistry = null;
    private AuthorizationCodeStoreInterface|null $authorizationCodeStore = null;
    private RefreshTokenStoreInterface|null $refreshTokenStore = null;
    private OidcProviderInterface|null $oidcProvider = null;
    private OidcRequestObjectStoreInterface|null $oidcRequestObjectStore = null;

    public function withOAuthClientRegistry(#[SensitiveParameter] OAuthClientRegistryInterface $oauthClientRegistry) : self
    {
        $this->oAuthClientRegistry = $oauthClientRegistry;

        return $this;
    }

    public function withAuthorizationCodeStore(#[SensitiveParameter] AuthorizationCodeStoreInterface $authorizationCodeStore) : self
    {
        $this->authorizationCodeStore = $authorizationCodeStore;

        return $this;
    }

    public function withRefreshTokenStore(#[SensitiveParameter] RefreshTokenStoreInterface $refreshTokenStore) : self
    {
        $this->refreshTokenStore = $refreshTokenStore;

        return $this;
    }

    public function withOidcProvider(OidcProviderInterface $oidcProvider) : self
    {
        $this->oidcProvider = $oidcProvider;

        return $this;
    }

    public function withOidcRequestObjectStore(OidcRequestObjectStoreInterface $oidcRequestObjectStore) : self
    {
        $this->oidcRequestObjectStore = $oidcRequestObjectStore;

        return $this;
    }

    /**
     * @return array{
     *     oAuthClientRegistry: OAuthClientRegistryInterface|null,
     *     authorizationCodeStore: AuthorizationCodeStoreInterface|null,
     *     refreshTokenStore: RefreshTokenStoreInterface|null,
     *     oidcProvider: OidcProviderInterface|null,
     *     oidcRequestObjectStore: OidcRequestObjectStoreInterface|null,
     * }
     */
    public function build() : array
    {
        return [
            'oAuthClientRegistry'      => $this->oAuthClientRegistry,
            'authorizationCodeStore'   => $this->authorizationCodeStore,
            'refreshTokenStore'        => $this->refreshTokenStore,
            'oidcProvider'             => $this->oidcProvider,
            'oidcRequestObjectStore'   => $this->oidcRequestObjectStore,
        ];
    }
}
