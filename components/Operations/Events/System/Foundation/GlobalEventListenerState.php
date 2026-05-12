<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Foundation;

use Avax\Components\Operations\Events\System\Capabilities\InvokeEventListener\InvokeEventListener;
use Avax\Components\Operations\Events\System\Capabilities\Registry\ListenerRegistry;
use Avax\Components\Operations\Events\System\Capabilities\ResolveEventListeners\ResolveEventListeners;

/**
 * Global registry state for the Events DSL and emitter.
 *
 * Set at boot time via onEventSetRegistry() and setEmitter().
 * In production, the framework wires this during bootstrap.
 */
final class GlobalEventListenerState
{
    private static ?ListenerRegistry $registry = null;
    private static ?EventEmitter $emitter = null;

    public static function setRegistry(ListenerRegistry $registry): void
    {
        self::$registry = $registry;
    }

    public static function registry(): ListenerRegistry
    {
        return self::$registry ??= new ListenerRegistry();
    }

    /**
     * Set the global event emitter used by emit().
     *
     * Must be called after compiling the listener registry.
     */
    public static function setEmitter(EventEmitter $emitter): void
    {
        self::$emitter = $emitter;
    }

    /**
     * Return the global emitter, creating a default one if not set.
     *
     * If no emitter has been explicitly set, creates a default emitter
     * backed by a CompiledListenerRegistry wired to the global registry.
     */
    public static function emitter(): EventEmitter
    {
        if (self::$emitter !== null) {
            return self::$emitter;
        }

        // Default: create an emitter with an empty compiled registry.
        // In production, the bootstrap should compile and set a real registry.
        $compiled = new CompiledListenerRegistry();
        $compiled->freeze();

        self::$emitter = new EventEmitter(
            $compiled,
            new ResolveEventListeners(),
            new InvokeEventListener(),
        );

        return self::$emitter;
    }

    public static function reset(): void
    {
        self::$registry = null;
        self::$emitter = null;
    }
}
