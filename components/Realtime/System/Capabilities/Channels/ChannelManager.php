<?php

declare(strict_types=1);

namespace Avax\Components\Realtime\System\Capabilities\Channels;

final class ChannelManager
{
    /** @var array<string, list<Connection>> */
    private array $channels = [];

    public function get(string $name) : Channel
    {
        return new Channel($name, $this);
    }

    public function subscribe(Connection $connection, string $channel) : void
    {
        if (! isset($this->channels[$channel])) {
            $this->channels[$channel] = [];
        }

        if (! in_array($connection, $this->channels[$channel], true)) {
            $this->channels[$channel][] = $connection;
        }
    }

    public function unsubscribe(Connection $connection, string $channel) : void
    {
        if (! isset($this->channels[$channel])) {
            return;
        }

        $index = array_search($connection, $this->channels[$channel], true);

        if ($index !== false) {
            unset($this->channels[$channel][$index]);
            $this->channels[$channel] = array_values($this->channels[$channel]);
        }
    }

    public function broadcast(string $channel, mixed $message) : void
    {
        if (! isset($this->channels[$channel])) {
            return;
        }

        foreach ($this->channels[$channel] as $connection) {
            $connection->send($message);
        }
    }

    public function subscriberCount(string $channel) : int
    {
        return count($this->channels[$channel] ?? []);
    }
}

final class Channel
{
    public function __construct(
        public string          $name,
        private ChannelManager $manager,
    ) {}

    public function subscribe(Connection $connection) : void
    {
        $this->manager->subscribe($connection, $this->name);
    }

    public function unsubscribe(Connection $connection) : void
    {
        $this->manager->unsubscribe($connection, $this->name);
    }

    public function broadcast(mixed $message) : void
    {
        $this->manager->broadcast($this->name, $message);
    }

    public function subscriberCount() : int
    {
        return $this->manager->subscriberCount($this->name);
    }
}