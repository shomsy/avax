<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\ReadConnections;

use Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnection;
use Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnectionStoreInterface;

final readonly class ReadFederationConnections
{
    private FederationConnectionStoreInterface $connectionStore;

    public function __construct(
        FederationConnectionStoreInterface $connectionStore
    )
    {
        $this->connectionStore = $connectionStore;
    }

    /**
     * @return list<FederationConnection>
     */
    public function execute() : array
    {
        return $this->connectionStore->all();
    }
}
