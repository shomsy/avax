<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection;

/**
 * Static cache for DataShape inspections to prevent redundant reflection work.
 */
final class CacheDataShape
{
    /**
     * @var array<string, DataShape>
     */
    private static array $cache = [];

    public function remember(string $key, callable $builder) : DataShape
    {
        return self::$cache[$key] ??= $builder();
    }

    public function clear() : void
    {
        self::$cache = [];
    }

    /**
     * Reset static cache for long-lived worker safety.
     *
     * Must be called during worker warmup or between isolated test runs.
     */
    public static function reset() : void
    {
        self::$cache = [];
    }
}
