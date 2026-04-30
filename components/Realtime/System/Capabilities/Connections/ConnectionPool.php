<?php

declare(strict_types=1);

namespace Avax\Components\Realtime\System\Capabilities\Connections;

use Closure;

final class ConnectionPool
{
    /** @var list<Connection> */
    private array $connections = [];

    public function add(Connection $connection) : void
    {
        $this->connections[] = $connection;
    }

    public function remove(Connection $connection) : void
    {
        $index = array_search($connection, $this->connections, true);

        if ($index !== false) {
            unset($this->connections[$index]);
            $this->connections = array_values($this->connections);
        }
    }

    /**
     * @return list<Connection>
     */
    public function all() : array
    {
        return $this->connections;
    }

    public function count() : int
    {
        return count($this->connections);
    }

    public function broadcast(mixed $message) : void
    {
        foreach ($this->connections as $connection) {
            $connection->send($message);
        }
    }
}

final class Connection
{
    private string $id;
    private mixed  $handler;

    public function __construct(Closure $handler)
    {
        $this->id      = bin2hex(random_bytes(16));
        $this->handler = $handler;
    }

    public function id() : string
    {
        return $this->id;
    }

    public function send(mixed $message) : void
    {
        ($this->handler)($message);
    }
}