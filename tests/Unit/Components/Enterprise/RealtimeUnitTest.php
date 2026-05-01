<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Enterprise;

use Avax\Components\Operations\Realtime\System\Capabilities\Channels\ChannelManager;
use Avax\Components\Operations\Realtime\System\Capabilities\Connections\Connection;
use Avax\Components\Operations\Realtime\System\Capabilities\Connections\ConnectionPool;
use Avax\Components\Operations\Realtime\System\Capabilities\WebSocket\PresenceChannel;
use Avax\Components\Operations\Realtime\System\Capabilities\WebSocket\WebSocketServer;
use Avax\Components\Operations\Realtime\System\PublicSurface\Realtime;
use Avax\Tests\TestCase;

final class RealtimeUnitTest extends TestCase
{
    public function test_connection_sends_messages_through_sender() : void
    {
        $messages   = [];
        $connection = new Connection(sender: static function (mixed $message) use (&$messages) : void {
            $messages[] = $message;
        });

        $connection->send(message: 'hello');

        self::assertSame(expected: ['hello'], actual: $messages);
    }

    public function test_connection_pool_counts_connections() : void
    {
        $pool = new ConnectionPool();

        $pool->add(connection: new Connection(sender: static fn () => null, id: 'one'));

        self::assertSame(expected: 1, actual: $pool->count());
    }

    public function test_connection_pool_removes_connections() : void
    {
        $pool       = new ConnectionPool();
        $connection = new Connection(sender: static fn () => null, id: 'gone');
        $pool->add(connection: $connection);

        $pool->remove(connection: $connection);

        self::assertSame(expected: 0, actual: $pool->count());
    }

    public function test_channel_manager_broadcasts_to_subscribers() : void
    {
        $messages   = [];
        $manager    = new ChannelManager();
        $connection = new Connection(sender: static function (mixed $message) use (&$messages) : void {
            $messages[] = $message;
        });

        $manager->subscribe(connection: $connection, channel: 'orders');
        $sent = $manager->broadcast(channel: 'orders', message: 'created');

        self::assertSame(expected: 1, actual: $sent);
        self::assertSame(expected: ['created'], actual: $messages);
    }

    public function test_channel_manager_unsubscribes_connections() : void
    {
        $manager    = new ChannelManager();
        $connection = new Connection(sender: static fn () => null);

        $manager->subscribe(connection: $connection, channel: 'orders');
        $manager->unsubscribe(connection: $connection, channel: 'orders');

        self::assertSame(expected: 0, actual: $manager->subscriberCount(channel: 'orders'));
    }

    public function test_websocket_server_tracks_channel_connections() : void
    {
        WebSocketServer::connect(connectionId: 'c1', channel: 'room');

        self::assertSame(expected: ['c1'], actual: WebSocketServer::connections(channel: 'room'));
    }

    public function test_websocket_server_records_sent_messages() : void
    {
        WebSocketServer::connect(connectionId: 'c1', channel: 'room');

        WebSocketServer::send(connectionId: 'c1', message: 'payload');

        self::assertSame(expected: ['payload'], actual: WebSocketServer::messages(connectionId: 'c1'));
    }

    public function test_channel_broadcaster_sends_structured_events() : void
    {
        WebSocketServer::connect(connectionId: 'c1', channel: 'room');

        WebSocketServer::toChannel(channel: 'room')->send(event: 'OrderCreated', data: ['id' => 5]);

        self::assertStringContainsString(needle: 'OrderCreated', haystack: WebSocketServer::messages(connectionId: 'c1')[0]);
    }

    public function test_presence_channel_tracks_members() : void
    {
        PresenceChannel::join(channel: 'presence-room', userId: '42', userInfo: ['name' => 'Ada']);

        self::assertSame(expected: 'Ada', actual: PresenceChannel::members(channel: 'presence-room')['42']['name']);
    }

    public function test_public_surface_broadcasts_to_channel() : void
    {
        $messages   = [];
        $connection = Realtime::connect(sender: static function (mixed $message) use (&$messages) : void {
            $messages[] = $message;
        });
        Realtime::channel(name: 'public')->subscribe(connection: $connection);

        Realtime::broadcast(channel: 'public', message: 'visible');

        self::assertSame(expected: ['visible'], actual: $messages);
    }

    protected function setUp() : void
    {
        parent::setUp();
        WebSocketServer::reset();
    }
}
