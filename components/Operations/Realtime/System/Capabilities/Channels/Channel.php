<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Realtime\System\Capabilities\Channels;

use Avax\Components\Operations\Realtime\System\Capabilities\Connections\Connection;

final readonly class Channel
{
    public function __construct(
        public string $name,
        private RealtimeChannels $realtimeChannels,
    ) {
    }

    public function subscribe(Connection $connection): void
    {
        $this->realtimeChannels->subscribe(connection: $connection, channel: $this->name);
    }

    public function unsubscribe(Connection $connection): void
    {
        $this->realtimeChannels->unsubscribe(connection: $connection, channel: $this->name);
    }

    public function broadcast(mixed $message): int
    {
        return $this->realtimeChannels->broadcast(channel: $this->name, message: $message);
    }

    public function subscriberCount(): int
    {
        return $this->realtimeChannels->subscriberCount(channel: $this->name);
    }
}
