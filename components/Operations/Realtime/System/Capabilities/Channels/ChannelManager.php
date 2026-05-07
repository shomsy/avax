<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Realtime\System\Capabilities\Channels;

use Avax\Components\Operations\Realtime\System\Capabilities\Connections\Connection;

final class ChannelManager
{
    /** @var array<string, Channel> */
    private array $channels = [];

    /** @var array<string, array<int, string>> channel name -> connection ids */
    private array $subscriptions = [];

    public function create(string $name) : Channel
    {
        $channel               = new Channel(name: $name, channelManager: $this);
        $this->channels[$name] = $channel;

        return $channel;
    }

    public function get(string $name) : ?Channel
    {
        return $this->channels[$name] ?? null;
    }

    public function subscribe(Connection $connection, string $channel) : void
    {
        $this->subscriptions[$channel] ??= [];

        if (! in_array($connection->id, $this->subscriptions[$channel], true)) {
            $this->subscriptions[$channel][] = $connection->id;
        }
    }

    public function unsubscribe(Connection $connection, string $channel) : void
    {
        $index = array_search($connection->id, $this->subscriptions[$channel] ?? [], true);

        if ($index !== false) {
            unset($this->subscriptions[$channel][$index]);
        }
    }

    public function broadcast(string $channel, mixed $message) : int
    {
        $count = 0;

        foreach ($this->subscriptions[$channel] ?? [] as $connectionId) {
            // In a real implementation, we'd resolve the Connection object here
            // For now, this is a placeholder
            ++$count;
        }

        return $count;
    }

    public function subscriberCount(string $channel) : int
    {
        return count($this->subscriptions[$channel] ?? []);
    }

    public function forget(Connection $connection) : void
    {
        foreach ($this->subscriptions as $channel => $ids) {
            $index = array_search($connection->id, $ids, true);

            if ($index !== false) {
                unset($this->subscriptions[$channel][$index]);
            }
        }
    }
}
