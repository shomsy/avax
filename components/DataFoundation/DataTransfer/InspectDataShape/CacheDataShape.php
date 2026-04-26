<?php

declare(strict_types=1);

namespace components\DataFoundation\DataTransfer\InspectDataShape;

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
}
