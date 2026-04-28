<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Capabilities\Dispatcher;

use Avax\Components\Operations\Events\System\Capabilities\ListenerRegistry\ListenerRegistry;

/**
 * Event dispatcher that coordinates listener execution.
 */
final readonly class EventDispatcher
{
    public function __construct(
        private ListenerRegistry $registry
    ) {}

    /**
     * Dispatch an event to all registered listeners.
     * Supports both string-named events and object-based events.
     */
    public function dispatch(string|object $event, mixed $data = null) : object|string
    {
        $eventName = is_object($event) ? $event::class : $event;
        $listeners = $this->registry->getListenersFor($eventName);

        foreach ($listeners as $listener) {
            if (is_object($event) && method_exists($event, 'isPropagationStopped') && $event->isPropagationStopped()) {
                break;
            }

            $listener($event, $data);
        }

        return $event;
    }
}
