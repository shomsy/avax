<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Foundation;

use Avax\Components\Operations\Events\System\Capabilities\Registry\ListenerRegistry;

/**
 * Global registry state for the Events DSL.
 *
 * Set at boot time via onEventSetRegistry().
 * In production, the framework wires this during bootstrap.
 */
final class GlobalEventListenerState
{
    private static ?ListenerRegistry $registry = null;

    public static function setRegistry(ListenerRegistry $registry): void
    {
        self::$registry = $registry;
    }

    public static function registry(): ListenerRegistry
    {
        return self::$registry ??= new ListenerRegistry();
    }

    public static function reset(): void
    {
        self::$registry = null;
    }
}
