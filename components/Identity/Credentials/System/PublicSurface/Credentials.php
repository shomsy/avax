<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\PublicSurface;

final class Credentials
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private static array $store = [];

    /**
     * @param array<string, mixed> $credentials
     */
    public static function store(string $userId, array $credentials) : void
    {
        self::$store[$userId] = $credentials;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function read(string $userId) : ?array
    {
        return self::$store[$userId] ?? null;
    }

    public static function forget(string $userId) : void
    {
        unset(self::$store[$userId]);
    }
}
