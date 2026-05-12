<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\PublicSurface;

use Avax\Components\Operations\Events\System\Capabilities\Registry\ListenerRegistry;
use Avax\Components\Operations\Events\System\Flows\RegisterEventListeners\EventListenerDsl;
use Avax\Components\Operations\Events\System\Foundation\EventEmitter;
use Avax\Components\Operations\Events\System\Foundation\GlobalEventListenerState;

if (! function_exists(__NAMESPACE__ . '\onEvent')) {
    /**
     * Begin fluent event listener registration for the given event class.
     *
     * @param  class-string  $eventClass
     */
    function onEvent(string $eventClass): EventListenerDsl
    {
        return new EventListenerDsl($eventClass, GlobalEventListenerState::registry());
    }
}

if (! function_exists(__NAMESPACE__ . '\onEventSetRegistry')) {
    /**
     * Set the global listener registry used by onEvent().
     *
     * Called during framework bootstrap to wire a shared registry instance.
     */
    function onEventSetRegistry(ListenerRegistry $registry): void
    {
        GlobalEventListenerState::setRegistry($registry);
    }
}

if (! function_exists(__NAMESPACE__ . '\emit')) {
    /**
     * Dispatch an event object through the canonical event runtime.
     *
     * Public API: emit(object $event): object
     * Only accepts event objects. Does not support class-string emission.
     *
     * @param object $event Plain event object. No EventInterface required.
     * @return object The same event object after listener invocation.
     */
    function emit(object $event): object
    {
        return GlobalEventListenerState::emitter()->emit($event);
    }
}
