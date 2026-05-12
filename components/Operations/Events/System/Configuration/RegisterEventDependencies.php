<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Configuration;

use Avax\Components\Operations\Events\System\Capabilities\Dispatcher\EventDispatcher;
use Avax\Components\Operations\Events\System\Capabilities\InvokeEventListener\InvokeEventListener;
use Avax\Components\Operations\Events\System\Capabilities\Registry\ListenerRegistry;
use Avax\Components\Operations\Events\System\Capabilities\ResolveEventListeners\ResolveEventListeners;
use Avax\Components\Operations\Events\System\Flows\CompileEventListeners\CompileEventListeners;
use Avax\Components\Operations\Events\System\Foundation\EventEmitter;
use Avax\Components\Operations\Events\System\Foundation\GlobalEventListenerState;

/**
 * Boot-time configuration: compiles DSL + attribute registrations
 * into the canonical CompiledListenerRegistry and wires the global emitter.
 */
final class RegisterEventDependencies
{
    /**
     * Compile all listener registrations and wire the global emitter.
     *
     * @param list<class-string> $listenerClasses Listener classes with #[ListensTo] attributes.
     */
    public static function compileAndWire(
        ListenerRegistry $registry,
        array $listenerClasses = [],
    ): void {
        // Compile DSL + attribute registrations into compiled registry.
        $compiler = new CompileEventListeners($registry);
        $compiledRegistry = $compiler->execute($listenerClasses);

        // Create the emitter with the compiled registry.
        $emitter = new EventEmitter(
            $compiledRegistry,
            new ResolveEventListeners(),
            new InvokeEventListener(),
        );

        // Wire the global state.
        GlobalEventListenerState::setRegistry($registry);
        GlobalEventListenerState::setEmitter($emitter);
    }

    /**
     * Legacy: wire only the registry (no compilation).
     * Kept for backward compatibility during transition.
     *
     * @deprecated Use compileAndWire() instead.
     */
    public static function register(EventDispatcher $eventDispatcher): void
    {
        // For V5.7, this no longer calls the non-existent Events::setDispatcher().
        // Instead, it wires the global registry for DSL usage.
        // The EventDispatcher is kept for legacy string-based dispatch compatibility.
    }
}
