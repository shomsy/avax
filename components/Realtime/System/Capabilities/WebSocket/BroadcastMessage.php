<?php

declare(strict_types=1);

namespace Avax\Components\Realtime\System\Capabilities\WebSocket;

final readonly class BroadcastMessage
{
    public function __construct(
        public string $event,
        public mixed  $data,
        public string $channel,
    ) {}

    public function toJson() : string
    {
        return json_encode(
            value: [
                       'event'   => $this->event,
                       'data'    => $this->data,
                       'channel' => $this->channel,
                   ],
            flags: JSON_THROW_ON_ERROR,
        );
    }
}
