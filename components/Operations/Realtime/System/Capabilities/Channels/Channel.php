<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Realtime\System\Capabilities\Channels;

use Avax\Components\Operations\Realtime\System\Capabilities\Connections\Connection;

final readonly class Channel
{
    public function __construct(
        public string $name,
        private ChannelManager $channelManager,
    ) {
    }

    public function subscribe(Connection $connection): void
    {
        $this->channelManager->subscribe(connection: $connection, channel: $this->name);
    }

    public function unsubscribe(Connection $connection): void
    {
        $this->channelManager->unsubscribe(connection: $connection, channel: $this->name);
    }

    public function broadcast(mixed $message): int
    {
        return $this->channelManager->broadcast(channel: $this->name, message: $message);
    }

    public function subscriberCount(): int
    {
        return $this->channelManager->subscriberCount(channel: $this->name);
    }
}
