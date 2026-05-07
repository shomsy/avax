<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Realtime\System\Flows\BroadcastToChannel;

use Avax\Components\Operations\Realtime\System\Capabilities\Channels\Channel;

final readonly class BroadcastToChannel
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return array{broadcast: bool, subscribers: int}
     */
    public function broadcast(Channel $channel, array $payload) : array
    {
        $channel->broadcast($payload);

        return [
            'broadcast'   => true,
            'subscribers' => $channel->subscriberCount(),
        ];
    }
}
