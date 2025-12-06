<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Events\Adapters;

use Avax\HTTP\Session\Features\Events\SessionEvent;
use Avax\HTTP\Session\Features\Events\SessionEventBus;

/**
 * LoggingEventBus
 *
 * EventBus adapter that logs all events.
 *
 * This adapter wraps another EventBus and logs all dispatched events
 * using the application logger. Perfect for debugging and audit trails.
 *
 * Enterprise Rules:
 * - Decorator pattern: Wraps another EventBus.
 * - Logging: All events logged for observability.
 * - Pass-through: Delegates to wrapped bus.
 *
 * Usage:
 *   $bus = new LoggingEventBus(new InMemoryEventBus());
 *
 * @package Avax\HTTP\Session\Features\Events\Adapters
 */
final readonly class LoggingEventBus implements SessionEventBus
{
    /**
     * LoggingEventBus Constructor.
     *
     * @param SessionEventBus $innerBus The wrapped event bus.
     */
    public function __construct(
        private SessionEventBus $innerBus
    ) {}

    /**
     * Dispatch a session event.
     *
     * Logs the event and delegates to the inner bus.
     *
     * @param SessionEvent $event The event to dispatch.
     *
     * @return void
     */
    public function dispatch(SessionEvent $event): void
    {
        // Log the event if logger is available.
        logger()?->debug(
            message: 'Session event dispatched',
            context: $event->toArray()
        );

        // Delegate to inner bus.
        $this->innerBus->dispatch($event);
    }
}
