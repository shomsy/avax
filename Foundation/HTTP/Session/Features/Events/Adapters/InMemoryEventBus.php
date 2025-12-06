<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Events\Adapters;

use Avax\HTTP\Session\Features\Events\SessionEvent;
use Avax\HTTP\Session\Features\Events\SessionEventBus;

/**
 * InMemoryEventBus
 *
 * In-memory implementation of SessionEventBus.
 *
 * This adapter stores listeners in memory and dispatches events
 * synchronously. Perfect for development, testing, and simple applications.
 *
 * Enterprise Rules:
 * - Synchronous: Events dispatched immediately.
 * - Type-safe: Listeners receive specific event types.
 * - Testable: Easy to verify event dispatching.
 *
 * Usage:
 *   $bus = new InMemoryEventBus();
 *   $bus->listen(SessionStartedEvent::class, fn($e) => logger()->info($e->toArray()));
 *   $bus->dispatch(SessionStartedEvent::create('abc123'));
 *
 * @package Avax\HTTP\Session\Features\Events\Adapters
 */
final class InMemoryEventBus implements SessionEventBus
{
    /**
     * Registered event listeners.
     *
     * @var array<string, array<callable>>
     */
    private array $listeners = [];

    /**
     * Register a listener for a specific event type.
     *
     * @param string   $eventClass The event class name.
     * @param callable $listener   The listener callback.
     *
     * @return void
     */
    public function listen(string $eventClass, callable $listener): void
    {
        // Initialize listeners array for this event type if not exists.
        if (!isset($this->listeners[$eventClass])) {
            $this->listeners[$eventClass] = [];
        }

        // Add listener to the list.
        $this->listeners[$eventClass][] = $listener;
    }

    /**
     * Dispatch a session event.
     *
     * Notifies all registered listeners for this event type.
     *
     * @param SessionEvent $event The event to dispatch.
     *
     * @return void
     */
    public function dispatch(SessionEvent $event): void
    {
        // Get event class name.
        $eventClass = $event::class;

        // Check if there are listeners for this event type.
        if (!isset($this->listeners[$eventClass])) {
            return;
        }

        // Call each listener with the event.
        foreach ($this->listeners[$eventClass] as $listener) {
            $listener($event);
        }
    }

    /**
     * Remove all listeners for a specific event type.
     *
     * @param string $eventClass The event class name.
     *
     * @return void
     */
    public function forget(string $eventClass): void
    {
        unset($this->listeners[$eventClass]);
    }

    /**
     * Remove all listeners.
     *
     * @return void
     */
    public function flush(): void
    {
        $this->listeners = [];
    }
}
