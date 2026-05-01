<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Realtime\System\Capabilities\WebSocket;

final class WebSocketServer
{
    /** @var array<string, array{id:string, channel:string, joined_at:int, messages:list<string>}> */
    private static array $connections = [];

    /** @var array<string, array<string, true>> */
    private static array $channels = [];

    public static function disconnect(string $connectionId) : void
    {
        $channel = self::$connections[$connectionId]['channel'] ?? null;

        if ($channel !== null) {
            unset(self::$channels[$channel][$connectionId]);
        }

        unset(self::$connections[$connectionId]);
    }

    public static function subscribe(string $connectionId, string $channel) : void
    {
        if (! isset(self::$connections[$connectionId])) {
            self::connect(connectionId: $connectionId, channel: $channel);

            return;
        }

        $oldChannel = self::$connections[$connectionId]['channel'];
        unset(self::$channels[$oldChannel][$connectionId]);
        self::$connections[$connectionId]['channel'] = $channel;
        self::$channels[$channel][$connectionId] = true;
    }

    public static function connect(string $connectionId, string $channel = 'default') : void
    {
        self::$connections[$connectionId] = [
            'id'       => $connectionId,
            'channel'  => $channel,
            'joined_at' => time(),
            'messages' => [],
        ];
        self::$channels[$channel][$connectionId] = true;
    }

    public static function broadcast(string $channel, string|BroadcastMessage $message) : int
    {
        $sent = 0;

        foreach (array_keys(array: self::$channels[$channel] ?? []) as $connectionId) {
            if (self::send(connectionId: $connectionId, message: $message)) {
                $sent++;
            }
        }

        return $sent;
    }

    public static function send(string $connectionId, string|BroadcastMessage $message) : bool
    {
        if (! isset(self::$connections[$connectionId])) {
            return false;
        }

        self::$connections[$connectionId]['messages'][] = $message instanceof BroadcastMessage ? $message->toJson() : $message;

        return true;
    }

    public static function toChannel(string $channel) : ChannelBroadcaster
    {
        return new ChannelBroadcaster(channel: $channel);
    }

    public static function toUser(int|string $userId) : UserBroadcaster
    {
        return new UserBroadcaster(userId: $userId);
    }

    public static function clientScript(string $endpoint = '/ws') : string
    {
        return WebSocketClientScript::forEndpoint(endpoint: $endpoint);
    }

    public static function connections(?string $channel = null) : array
    {
        if ($channel === null) {
            return array_keys(array: self::$connections);
        }

        return array_keys(array: self::$channels[$channel] ?? []);
    }

    public static function messages(string $connectionId) : array
    {
        return self::$connections[$connectionId]['messages'] ?? [];
    }

    public static function reset() : void
    {
        self::$connections = [];
        self::$channels = [];
        PresenceChannel::reset();
    }
}
