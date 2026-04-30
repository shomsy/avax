<?php

declare(strict_types=1);

namespace Avax\Components\Realtime\System\Capabilities\WebSocket;

final class PresenceChannel
{
    /** @var array<string, array<string, array<string, mixed>>> */
    private static array $members = [];

    public static function join(string $channel, string $userId, array $userInfo = []) : void
    {
        self::$members[$channel][$userId] = $userInfo + ['joined_at' => time()];
    }

    public static function leave(string $channel, string $userId) : void
    {
        unset(self::$members[$channel][$userId]);
    }

    public static function members(string $channel) : array
    {
        return self::$members[$channel] ?? [];
    }

    public static function reset() : void
    {
        self::$members = [];
    }
}
