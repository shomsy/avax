<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Events;

/**
 * SessionEventBus Interface
 *
 * Contract for dispatching session-related events.
 *
 * This interface enables observability, audit logging, and plugin
 * integrations without hard-coding dependencies on specific loggers
 * or monitoring systems.
 *
 * Enterprise Rules:
 * - Decoupling: No direct logger dependencies in domain code.
 * - Extensibility: Multiple listeners can subscribe to events.
 * - Testability: Easy to mock for testing.
 *
 * Usage:
 *   $eventBus->dispatch(new SessionStartedEvent(...));
 *
 * @package Avax\HTTP\Session\Features\Events
 */
interface SessionEventBus
{
    /**
     * Dispatch a session event.
     *
     * Notifies all registered listeners about the event.
     *
     * @param SessionEvent $event The event to dispatch.
     *
     * @return void
     */
    public function dispatch(SessionEvent $event): void;
}
