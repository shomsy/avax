<?php

declare(strict_types=1);

namespace Avax\Components\Application\Pipeline\System\PublicSurface;

use Closure;

final class Pipeline
{
    private static ?HookRegistry $hookRegistry = null;

    public static function beforeRoute(Closure $handler): void
    {
        self::registry()->add('beforeRoute', $handler);
    }

    private static function registry(): HookRegistry
    {
        if (! self::$hookRegistry instanceof HookRegistry) {
            self::$hookRegistry = new HookRegistry();
        }

        return self::$hookRegistry;
    }

    public static function afterRoute(Closure $handler): void
    {
        self::registry()->add('afterRoute', $handler);
    }

    public static function beforeController(Closure $handler): void
    {
        self::registry()->add('beforeController', $handler);
    }

    public static function afterController(Closure $handler): void
    {
        self::registry()->add('afterController', $handler);
    }

    public static function beforeResponse(Closure $handler): void
    {
        self::registry()->add('beforeResponse', $handler);
    }

    public static function afterResponse(Closure $handler): void
    {
        self::registry()->add('afterResponse', $handler);
    }

    public static function onException(Closure $handler): void
    {
        self::registry()->add('onException', $handler);
    }

    public static function onTerminate(Closure $handler): void
    {
        self::registry()->add('onTerminate', $handler);
    }

    public static function execute(string $hook, mixed $data = null): mixed
    {
        return self::registry()->execute($hook, $data);
    }

    public static function hooks(): array
    {
        return self::registry()->all();
    }

    /**
     * Reset the static hook registry. Required for test isolation.
     */
    public static function reset(): void
    {
        self::$hookRegistry = null;
    }

    /**
     * Replace the hook registry (for testing or DI injection).
     */
    public static function setInstance(HookRegistry $registry): void
    {
        self::$hookRegistry = $registry;
    }
}
