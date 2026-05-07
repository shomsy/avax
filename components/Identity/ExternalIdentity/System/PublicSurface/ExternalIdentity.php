<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\PublicSurface;

final class ExternalIdentity
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private static array $links = [];

    /**
     * @param array<string, mixed> $externalData
     */
    public static function link(string $userId, string $provider, array $externalData) : void
    {
        self::$links[$userId][$provider] = $externalData;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function resolve(string $userId, string $provider) : ?array
    {
        return self::$links[$userId][$provider] ?? null;
    }
}
