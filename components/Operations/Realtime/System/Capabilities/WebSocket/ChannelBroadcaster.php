<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Realtime\System\Capabilities\WebSocket;

final readonly class ChannelBroadcaster
{
    public function __construct(private string $channel) {}

    public function send(string $event, mixed $data): int
    {
        return WebSocketServer::broadcast(
            channel: $this->channel,
            message: new BroadcastMessage(event: $event, data: $data, channel: $this->channel),
        );
    }
}
