<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Realtime\System\Capabilities\WebSocket;

final readonly class UserBroadcaster
{
    public function __construct(private int|string $userId)
    {
    }

    public function send(string $event, mixed $data): int
    {
        $channel = 'user.'.$this->userId;

        return WebSocketServer::broadcast(
            channel: $channel,
            message: new BroadcastMessage(event: $event, data: $data, channel: $channel),
        );
    }
}
