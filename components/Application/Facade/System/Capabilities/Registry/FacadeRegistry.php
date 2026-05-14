<?php

declare(strict_types=1);

namespace Avax\Components\Application\Facade\System\Capabilities\Registry;

final class FacadeRegistry
{
    /**
     * @var array<string, object>
     */
    private static array $registry = [];

    public static function set(string $name, object $instance) : void
    {
        self::$registry[$name] = $instance;
    }

    public static function get(string $name) : object|null
    {
        return self::$registry[$name] ?? null;
    }

    public static function has(string $name) : bool
    {
        return isset(self::$registry[$name]);
    }

    public static function clear() : void
    {
        self::$registry = [];
    }

    /**
     * Reset static state for long-lived worker safety.
     */
    public static function reset() : void
    {
        self::$registry = [];
    }
}
