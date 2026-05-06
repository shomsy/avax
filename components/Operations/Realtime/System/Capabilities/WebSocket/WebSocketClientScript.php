<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Realtime\System\Capabilities\WebSocket;

final readonly class WebSocketClientScript
{
    public static function forEndpoint(string $endpoint): string
    {
        return <<<JAVASCRIPT
            window.AvaxRealtime = {
              connect(onMessage) {
                const socket = new WebSocket('{$endpoint}');
                socket.onmessage = event => onMessage(JSON.parse(event.data));
                return socket;
              },
              subscribe(socket, channel) {
                socket.send(JSON.stringify({type: 'subscribe', channel}));
              },
              publish(socket, channel, event, data) {
                socket.send(JSON.stringify({type: 'publish', channel, event, data}));
              }
            };
            JAVASCRIPT;
    }
}
