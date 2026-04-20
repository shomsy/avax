<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Federation;

final class InMemoryFederationConnectionStore implements FederationConnectionStoreInterface
{
    /** @var array<string, FederationConnection> */
    private array $connections = [];

    public function save(FederationConnection $connection) : void
    {
        $this->connections[$connection->connectionId] = $connection;
    }

    public function find(string $connectionId) : FederationConnection|null
    {
        return $this->connections[$connectionId] ?? null;
    }

    public function findByDomain(string $domain) : FederationConnection|null
    {
        $normalized = strtolower(trim($domain));

        foreach ($this->connections as $connection) {
            if (strtolower($connection->domain) === $normalized) {
                return $connection;
            }
        }

        return null;
    }

    public function all() : array
    {
        return array_values($this->connections);
    }
}
