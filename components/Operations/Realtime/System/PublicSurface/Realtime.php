<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Realtime\System\PublicSurface;

use Avax\Components\Operations\Realtime\System\Capabilities\Channels\Channel;
use Avax\Components\Operations\Realtime\System\Capabilities\Channels\ChannelManager;
use Avax\Components\Operations\Realtime\System\Capabilities\Connections\Connection;
use Avax\Components\Operations\Realtime\System\Capabilities\Connections\ConnectionPool;
use Avax\Components\Operations\Realtime\System\Capabilities\WebSocket\WebSocketServer;
use Closure;

final class Realtime
{
    private static ?ConnectionPool $connectionPool = null;

    private static ?ChannelManager $channelManager = null;

    public static function connect(Closure $sender) : Connection
    {
        $connection = new Connection(sender: $sender);
        self::pool()->add(connection: $connection);

        return $connection;
    }

    private static function pool() : ConnectionPool
    {
        if (! self::$connectionPool instanceof ConnectionPool) {
            self::$connectionPool = new ConnectionPool();
        }

        return self::$connectionPool;
    }

    public static function channel(string $name) : Channel
    {
        return self::channels()->get(name: $name);
    }

    private static function channels() : ChannelManager
    {
        if (! self::$channelManager instanceof ChannelManager) {
            self::$channelManager = new ChannelManager();
        }

        return self::$channelManager;
    }

    public static function broadcast(string $channel, mixed $message) : int
    {
        return self::channels()->broadcast(channel: $channel, message: $message);
    }

    public static function disconnect(Connection $connection) : void
    {
        self::pool()->remove(connection: $connection);
        self::channels()->forget(connection: $connection);
    }

    public static function websocket() : string
    {
        return WebSocketServer::clientScript();
    }
}
