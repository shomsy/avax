<?php

declare(strict_types=1);

namespace Avax\Components\Realtime\System\PublicSurface;

use Avax\Components\Realtime\System\Capabilities\Channels\Channel;
use Avax\Components\Realtime\System\Capabilities\Channels\ChannelManager;
use Avax\Components\Realtime\System\Capabilities\Connections\Connection;
use Avax\Components\Realtime\System\Capabilities\Connections\ConnectionPool;
use Avax\Components\Realtime\System\Capabilities\WebSocket\WebSocketServer;
use Closure;

final class Realtime
{
    private static ConnectionPool|null $pool = null;

    private static ChannelManager|null $channels = null;

    public static function connect(Closure $sender) : Connection
    {
        $connection = new Connection(sender: $sender);
        self::pool()->add(connection: $connection);

        return $connection;
    }

    public static function channel(string $name) : Channel
    {
        return self::channels()->get(name: $name);
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

    private static function pool() : ConnectionPool
    {
        if (self::$pool === null) {
            self::$pool = new ConnectionPool();
        }

        return self::$pool;
    }

    private static function channels() : ChannelManager
    {
        if (self::$channels === null) {
            self::$channels = new ChannelManager();
        }

        return self::$channels;
    }
}
