<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Foundation;

use Avax\Components\Operations\Events\System\Capabilities\Registry\ListenerRegistry;
use RuntimeException;

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
     * Return the global emitter.
     *
     * The emitter must be set at boot time via setEmitter().
     * In production, the EventsServiceProvider wires and sets the emitter during boot.
     * If no emitter is set, this indicates a boot configuration error.
     *
     * @throws RuntimeException If no emitter has been configured
     */
    public static function emitter(): EventEmitter
    {
        if (self::$emitter === null) {
            throw new RuntimeException(
                'No event emitter configured. Events must be wired through EventsServiceProvider at boot time. '
                .'Call GlobalEventListenerState::setEmitter() before dispatching events.',
            );
        }

        return self::$emitter;
    }

    public static function reset(): void
    {
        self::$registry = null;
        self::$emitter = null;
    }
}
