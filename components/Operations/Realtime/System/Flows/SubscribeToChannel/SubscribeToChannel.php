<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Realtime\System\Flows\SubscribeToChannel;

use Avax\Components\Operations\Realtime\System\Capabilities\Channels\Channel;
use Avax\Components\Operations\Realtime\System\Capabilities\Connections\Connection;

final readonly class SubscribeToChannel
{
    public function subscribe(Channel $channel, Connection $connection) : void
    {
        $channel->subscribe($connection);
    }
}
