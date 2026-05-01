<?php

declare(strict_types=1);

namespace Avax\Components\Performance\System\PublicSurface;

use Avax\Components\Performance\System\Capabilities\ConfigCaching\ConfigCache;
use Avax\Components\Performance\System\Capabilities\LazyLoading\LazyValue;
use Avax\Components\Performance\System\Capabilities\QueryCaching\QueryCache;
use Avax\Components\Performance\System\Capabilities\RouteCaching\RouteCache;
use Closure;

final class Performance
{
    private static ?QueryCache $queries = null;

    public static function queryCache(): QueryCache
    {
        if (self::$queries === null) {
            self::$queries = new QueryCache;
        }

        return self::$queries;
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
