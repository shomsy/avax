<?php

declare(strict_types=1);

namespace Avax\Tests\Integration\Components;

use Avax\Components\Operations\Realtime\System\Capabilities\WebSocket\WebSocketServer;
use Avax\Tests\TestCase;

final class WebSocketTest extends TestCase
{
    public function test_websocket_broadcast_reaches_channel_connections(): void
    {
        WebSocketServer::connect('ws-one', channel: 'integration');

        $sent = WebSocketServer::broadcast(channel: 'integration', 'hello');

        self::assertSame(1, $sent);
        self::assertSame(['hello'], WebSocketServer::messages('ws-one'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        WebSocketServer::reset();
    }
}
