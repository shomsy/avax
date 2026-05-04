<?php

declare(strict_types=1);

namespace Avax\Benchmarks\Performance\System\PublicSurface;

use Avax\Benchmarks\Performance\Capabilities\ConfigCaching\ConfigCache;
use Avax\Benchmarks\Performance\Capabilities\LazyLoading\LazyValue;
use Avax\Benchmarks\Performance\Capabilities\QueryCaching\QueryCache;
use Avax\Benchmarks\Performance\Capabilities\RouteCaching\RouteCache;
use Closure;

final class Performance
{
    private static ?QueryCache $queryCache = null;

    public static function queryCache(): QueryCache
    {
        if (!self::$queryCache instanceof QueryCache) {
            self::$queryCache = new QueryCache();
        }

        return self::$queryCache;
    }

    public static function routes(string $path): RouteCache
    {
        return new RouteCache(path: $path);
    }

    public static function config(string $path): ConfigCache
    {
        return new ConfigCache(path: $path);
    }

    public static function lazy(Closure $resolver): LazyValue
    {
        return new LazyValue(resolver: $resolver);
    }
}
