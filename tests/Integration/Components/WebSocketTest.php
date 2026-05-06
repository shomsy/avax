<?php

declare(strict_types=1);

namespace Avax\Tests\Integration\Components;

use Avax\Components\Operations\Realtime\System\Capabilities\WebSocket\WebSocketServer;
use Avax\Tests\TestCase;

final class WebSocketTest extends TestCase
{
    public function test_websocket_broadcast_reaches_channel_connections(): void
    {
        WebSocketServer::connect(connectionId: 'ws-one', channel: 'integration');

        $sent = WebSocketServer::broadcast(channel: 'integration', message: 'hello');

        self::assertSame(expected: 1, actual: $sent);
        self::assertSame(expected: ['hello'], actual: WebSocketServer::messages(connectionId: 'ws-one'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        WebSocketServer::reset();
    }
}
