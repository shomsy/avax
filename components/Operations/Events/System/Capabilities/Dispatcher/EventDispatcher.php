<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Capabilities\Dispatcher;

use Avax\Components\Operations\Events\System\Capabilities\Registry\ListenerRegistry;

/**
 * Event dispatcher that coordinates listener execution.
 */
final readonly class EventDispatcher
{
    public function __construct(
        private ListenerRegistry $listenerRegistry,
    ) {
    }

    /**
     * Dispatch an event to all registered listeners.
     */
    public function dispatch(string|object $event, mixed $data = null): object|string
    {
        $eventName = is_object($event) ? $event::class : $event;
        $listeners = $this->listenerRegistry->getListenersFor($eventName);

        foreach ($listeners as $listener) {
            if (is_object($event) && method_exists($event, 'isPropagationStopped') && $event->isPropagationStopped()) {
                break;
            }

            $listener($event, $data);
        }

        return $event;
    }
}
