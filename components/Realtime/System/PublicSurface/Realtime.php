<?php

declare(strict_types=1);

namespace Avax\Components\Realtime\System\PublicSurface;

use Avax\Components\Realtime\System\Capabilities\Channels\ChannelManager;
use Avax\Components\Realtime\System\Capabilities\Connections\ConnectionPool;
use Closure;

final class Realtime
{
    private static ConnectionPool $pool;
    private static ChannelManager $channels;

    public static function connect(Closure $handler) : Connection
    {
        $connection = new Connection($handler);
        self::pool()->add($connection);

        return $connection;
    }

    private static function pool() : ConnectionPool
    {
        if (! isset(self::$pool)) {
            self::$pool = new ConnectionPool();
        }

        return self::$pool;
    }

    public static function channel(string $name) : Channel
    {
        return self::channels()->get($name);
    }

    private static function channels() : ChannelManager
    {
        if (! isset(self::$channels)) {
            self::$channels = new ChannelManager();
        }

        return self::$channels;
    }

    public static function broadcast(string $channel, mixed $message) : void
    {
        self::channels()->broadcast($channel, $message);
    }

    public static function disconnect(Connection $connection) : void
    {
        self::pool()->remove($connection);
    }
}

final readonly class Connection
{
    public function __construct(
        private Closure $handler,
    ) {}

    public function send(mixed $message) : void
    {
        ($this->handler)($message);
    }
}

final readonly class Channel
{
    public function __construct(
        public string $name,
    ) {}

    public function subscribe(Connection $connection) : void
    {
        Realtime::channel($this->name)->subscribe($connection);
    }

    public function broadcast(mixed $message) : void
    {
        Realtime::broadcast($this->name, $message);
    }
}