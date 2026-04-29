<?php
declare(strict_types=1);

namespace Avax\Components\Application\Facade\System\Foundation;

use RuntimeException;

abstract class BaseFacade
{
    protected static string $accessor;

    public static function __callStatic(string $method, array $args): mixed
    {
        $instance = static::resolveInstance();
        if (!is_callable([$instance, $method])) {
            throw new RuntimeException("Method '$method' not found on " . static::class);
        }
        return $instance->$method(...$args);
    }

    protected static function resolveInstance(): mixed
    {
        if (!isset(static::$accessor)) {
            throw new RuntimeException("Accessor not defined for " . static::class);
        }
        return app(static::$accessor);
    }
}
