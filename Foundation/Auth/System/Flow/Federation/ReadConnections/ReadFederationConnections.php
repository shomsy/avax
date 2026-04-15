<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Federation\ReadConnections;

use Avax\Auth\System\Capability\Federation\FederationConnection;
use Avax\Auth\System\Capability\Federation\FederationConnectionStoreInterface;

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
