<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Realtime\System\Capabilities\Connections;

final class ConnectionPool
{
    /** @var array<string, Connection> */
    private array $connections = [];

    public function add(Connection $connection) : void
    {
        $this->connections[$connection->id] = $connection;
    }

    public function remove(Connection|string $connection) : void
    {
        unset($this->connections[$this->idFor(connection: $connection)]);
    }

    private function idFor(Connection|string $connection) : string
    {
        return $connection instanceof Connection ? $connection->id : $connection;
    }

    public function get(string $connectionId) : ?Connection
    {
        return $this->connections[$connectionId] ?? null;
    }

    /**
     * @return list<Connection>
     */
    public function all() : array
    {
        return array_values(array: $this->connections);
    }

    public function count() : int
    {
        return count($this->connections);
    }

    public function broadcast(mixed $message) : int
    {
        foreach ($this->connections as $connection) {
            $connection->send(message: $message);
        }

        return count($this->connections);
    }
}
