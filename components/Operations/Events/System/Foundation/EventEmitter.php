<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Foundation;

use Avax\Components\Operations\Events\System\Capabilities\InvokeEventListener\InvokeEventListener;
use Avax\Components\Operations\Events\System\Capabilities\ResolveEventListeners\ResolveEventListeners;

/**
 * Core event emitter that dispatches events through the canonical compiled registry.
 *
 * Runtime flow:
 *   emit(event)
 *     -> EventEmitter
 *     -> CompiledListenerRegistry (no reflection)
 *     -> ResolveEventListeners (class-string -> callable)
 *     -> InvokeEventListener
 *     -> return event
 */
final class EventEmitter
{
    private bool $attributeReflectionUsed = false;

    public function __construct(
        private CompiledListenerRegistry $registry,
        private ResolveEventListeners $resolver,
        private InvokeEventListener $invoker,
    ) {
    }

    /**
     * Dispatch an event object to all registered listeners.
     *
     * @param object $event The event object (plain object, no interface required).
     * @return object The same event object (possibly modified by listeners).
     */
    public function emit(object $event): object
    {
        $compiledListeners = $this->registry->getListenersFor($event);

        foreach ($compiledListeners as $compiled) {
            // Stoppable event check: if event has isPropagationStopped(), respect it.
            if ($this->isPropagationStopped($event)) {
                break;
            }

            // Resolve class-string listeners to callables.
            // Attribute resolution uses reflection, but only once per listener class,
            // not on every emit call (reflection happens at resolve time, cached in callable).
            $callable = $this->resolver->resolve($compiled);

            // Track if we used reflection for attribute-based listeners.
            if ($compiled->source === ListenerSource::Attribute && ! $this->attributeReflectionUsed) {
                $this->attributeReflectionUsed = true;
            }

            // Invoke the listener. Exceptions bubble up by default.
            $this->invoker->invoke($callable, $event);
        }

        return $event;
    }

    /**
     * Check if the event signals propagation should stop.
     * Duck-typed: supports PSR-14 StoppableEventInterface if available.
     */
    private function isPropagationStopped(object $event): bool
    {
        // Duck-type check: any object with isPropagationStopped() returning true.
        if (method_exists($event, 'isPropagationStopped')) {
            return (bool) $event->isPropagationStopped();
        }

        return false;
    }

    /**
     * Returns the compiled registry (read-only access for adapters).
     */
    public function getRegistry(): CompiledListenerRegistry
    {
        return $this->registry;
    }
}
