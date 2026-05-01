<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Realtime\System\Capabilities\Channels;

use Avax\Components\Operations\Realtime\System\Capabilities\Connections\Connection;

final class Channels
{
    /** @var array<string, array<string, Connection>> */
    private array $channels = [];

    public function get(string $name) : Channel
    {
        return new Channel(name: $name, channels: $this);
    }

    public function subscribe(Connection $connection, string $channel) : void
    {
        $this->channels[$channel][$connection->id] = $connection;
    }

    public function unsubscribe(Connection $connection, string $channel) : void
    {
        unset($this->channels[$channel][$connection->id]);
    }

    public function forget(Connection|string $connection) : void
    {
        $connectionId = $connection instanceof Connection ? $connection->id : $connection;

        foreach ($this->channels as $channel => $connections) {
            unset($this->channels[$channel][$connectionId]);
        }
    }

    public function broadcast(string $channel, mixed $message) : int
    {
        $sent = 0;

        foreach ($this->channels[$channel] ?? [] as $connection) {
            $connection->send(message: $message);
            $sent++;
        }

        return $sent;
    }

    public function subscriberCount(string $channel) : int
    {
        return count($this->channels[$channel] ?? []);
    }

    /**
     * @return list<string>
     */
    public function connectionIds(string $channel) : array
    {
        return array_keys(array: $this->channels[$channel] ?? []);
    }
}
