<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation;

final class InMemoryFederationConnectionStore implements FederationConnectionStoreInterface
{
    /** @var array<string, FederationConnection> */
    private array $connections = [];

    public function save(FederationConnection $federationConnection) : void
    {
        $this->connections[$federationConnection->connectionId] = $federationConnection;
    }

    public function find(string $connectionId) : FederationConnection|null
    {
        return $this->connections[$connectionId] ?? null;
    }

    public function findByDomain(string $domain) : FederationConnection|null
    {
        $normalized = strtolower(string: trim(string: $domain));

        foreach ($this->connections as $connection) {
            if (strtolower(string: $connection->domain) === $normalized) {
                return $connection;
            }
        }

        return null;
    }

    public function all() : array
    {
        return array_values(array: $this->connections);
    }
}
