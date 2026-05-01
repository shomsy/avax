<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\DataShape;

/**
 * Static cache for DataShape inspections to prevent redundant reflection work.
 */
final class CacheDataShape
{
    /**
     * @var array<string, DataShape>
     */
    private static array $cache = [];

    public function remember(string $key, callable $builder): DataShape
    {
        return self::$cache[$key] ??= $builder();
    }

    public function clear(): void
    {
        self::$cache = [];
    }
}
