<?php

declare(strict_types=1);

namespace Avax\Components\Realtime\System\Capabilities\WebSocket;

final class WebSocketServer
{
    private static array $connections = [];
    private static array $channels    = [];

    public static function connect(string $connectionId, string $channel = 'default') : void
    {
        self::$connections[$connectionId] = [
            'id'        => $connectionId,
            'channel'   => $channel,
            'joined_at' => time(),
        ];

        if (! isset(self::$channels[$channel])) {
            self::$channels[$channel] = [];
        }

        self::$channels[$channel][$connectionId] = true;
    }

    public static function disconnect(string $connectionId) : void
    {
        $channel = self::$connections[$connectionId]['channel'] ?? 'default';

        unset(self::$connections[$connectionId]);
        unset(self::$channels[$channel][$connectionId]);
    }

    public static function broadcast(string $channel, string $message) : int
    {
        $count = 0;

        foreach (self::$channels[$channel] ?? [] as $connectionId => $_) {
            if (self::send($connectionId, $message)) {
                $count++;
            }
        }

        return $count;
    }

    public static function send(string $connectionId, string $message) : bool
    {
        if (! isset(self::$connections[$connectionId])) {
            return false;
        }

        return true;
    }

    public static function toChannel(string $channel) : ChannelBroadcaster
    {
        return new ChannelBroadcaster($channel);
    }

    public static function toUser(int $userId) : UserBroadcaster
    {
        return new UserBroadcaster($userId);
    }

    public static function connections(string|null $channel = null) : array
    {
        if ($channel === null) {
            return self::$connections;
        }

        return array_keys(self::$channels[$channel] ?? []);
    }
}

final class ChannelBroadcaster
{
    private string $channel;

    public function __construct(string $channel)
    {
        $this->channel = $channel;
    }

    public function send(string $event, mixed $data) : int
    {
        $message = json_encode([
                                   'event'   => $event,
                                   'data'    => $data,
                                   'channel' => $this->channel,
                               ]);

        return WebSocketServer::broadcast($this->channel, $message);
    }
}

final class UserBroadcaster
{
    private int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function send(string $event, mixed $data) : int
    {
        return 0;
    }
}

final class PresenceChannel
{
    public static function join(string $channel, string $userId, array $userInfo = []) : void
    {
        $key = "presence:{$channel}";

        if (! isset($_SESSION[$key])) {
            $_SESSION[$key] = [];
        }

        $_SESSION[$key][$userId] = $userInfo + ['joined_at' => time()];
    }

    public static function leave(string $channel, string $userId) : void
    {
        $key = "presence:{$channel}";
        unset($_SESSION[$key][$userId]);
    }

    public static function members(string $channel) : array
    {
        $key = "presence:{$channel}";

        return $_SESSION[$key] ?? [];
    }
}