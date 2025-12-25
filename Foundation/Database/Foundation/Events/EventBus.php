<?php

declare(strict_types=1);

namespace Avax\Database\Events;

use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Central dispatcher for infrastructure and feature-level events.
 *
 * -- intent: facilitate decoupled communication between various database components.
 */
final class EventBus
{
    // Map of event names to their listeners
    private array $listeners = [];

    public function __construct(private readonly LoggerInterface|null $logger = null) {}

    /**
     * Dispatch an event to all registered listeners matching its type.
     *
     * -- intent: automate the broadcast of system signals to interested subscribers.
     *
     * @param Event $event The event object to propagate
     *
     * @return void
     */
    public function dispatch(Event $event) : void
    {
        $name = $event->getName();

        if (! isset($this->listeners[$name])) {
            return;
        }

        foreach ($this->listeners[$name] as $listener) {
            try {
                $listener($event);
            } catch (Throwable $e) {
                $this->logger?->error(
                    message: "Event listener failed for [{$name}]: " . $e->getMessage(),
                    context: ['exception' => $e]
                );
            }
        }
    }

    /**
     * Automatically register all handlers from a subscriber instance.
     *
     * @param EventSubscriberInterface $subscriber
     *
     * @return void
     */
    public function registerSubscriber(EventSubscriberInterface $subscriber) : void
    {
        foreach ($subscriber->getSubscribedEvents() as $event => $method) {
            $this->subscribe(event: $event, listener: [$subscriber, $method]);
        }
    }

    /**
     * Subscribe a specific callback to a named event type.
     *
     * -- intent: register a handler for specific system notifications.
     *
     * @param string   $event    Event technical name or class
     * @param callable $listener Execution logic for the event
     *
     * @return void
     */
    public function subscribe(string $event, callable $listener) : void
    {
        $this->listeners[$event][] = $listener;
    }
}
