<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration\Assembly;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnectionStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederatedIdentityLinkStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationRuntimeInterface;

/**
 * Configuration assembly graph for federation and external identity dependencies.
 *
 * Owns: federation runtime, connection store, federated identity links.
 */
final class FederationIdentityGraph
{
    private FederationRuntimeInterface|null $federationRuntime = null;
    private FederationConnectionStoreInterface|null $federationConnectionStore = null;
    private FederatedIdentityLinkStoreInterface|null $federatedIdentityLinkStore = null;

    public function withFederationRuntime(FederationRuntimeInterface $federationRuntime) : self
    {
        $this->federationRuntime = $federationRuntime;

        return $this;
    }

    public function withFederationConnectionStore(FederationConnectionStoreInterface $federationConnectionStore) : self
    {
        $this->federationConnectionStore = $federationConnectionStore;

        return $this;
    }

    public function withFederatedIdentityLinkStore(FederatedIdentityLinkStoreInterface $federatedIdentityLinkStore) : self
    {
        $this->federatedIdentityLinkStore = $federatedIdentityLinkStore;

        return $this;
    }

    /**
     * @return array{
     *     federationRuntime: FederationRuntimeInterface|null,
     *     federationConnectionStore: FederationConnectionStoreInterface|null,
     *     federatedIdentityLinkStore: FederatedIdentityLinkStoreInterface|null,
     * }
     */
    public function assemble() : array
    {
        return [
            'federationRuntime'          => $this->federationRuntime,
            'federationConnectionStore'  => $this->federationConnectionStore,
            'federatedIdentityLinkStore' => $this->federatedIdentityLinkStore,
        ];
    }
}
