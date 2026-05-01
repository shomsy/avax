<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\ReadConnections;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationSupport\FederationConnection;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationSupport\FederationConnectionStoreInterface;

final readonly class ReadFederationConnections
{
    public function __construct(private FederationConnectionStoreInterface $connectionStore) {}

    /**
     * @return list<FederationConnection>
     */
    public function execute() : array
    {
        return $this->connectionStore->all();
    }
}
