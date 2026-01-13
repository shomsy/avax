<?php

declare(strict_types=1);

namespace Avax\Database\Events;

use Avax\Database\Events\Contracts\DispatchStrategyInterface;
use Avax\Database\Events\Contracts\EventBusInterface;
use Avax\Database\Events\Strategy\SyncDispatchStrategy;

/**
 * Central event dispatcher for database lifecycle and telemetry signals.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/Concepts/Telemetry.md
 */
final class EventBus implements EventBusInterface
{
    /** @var array<string, array<int, callable>> A list of everyone signed up for each type of news. */
    private array $listeners = [];

    /**
     * @param  DispatchStrategyInterface  $strategy  The logic for HOW to deliver the news (e.g., "Do it now" or "Queue
     *                                               it").
     */
    public function __construct(
        private readonly DispatchStrategyInterface $strategy = new SyncDispatchStrategy
    ) {}

    /**
     * Broadcast an event to all registered listeners.
     */
    public function dispatch(Event $event): void
    {
        $name = $event->getName();

        // If no one is listening to this channel, we don't do anything.
        if (! isset($this->listeners[$name])) {
            return;
        }

        // We hand the job over to the "Strategy" (e.g., our Delivery Driver).
        $this->strategy->handle(event: $event, listeners: $this->listeners[$name]);
    }

    /**
     * Sign up a multi-topic "Subscriber" (a class that listens to many things).
     *
     * @param  EventSubscriberInterface  $subscriber  A helper object that contains multiple different listeners.
     */
    public function registerSubscriber(EventSubscriberInterface $subscriber): void
    {
        foreach ($subscriber->getSubscribedEvents() as $event => $method) {
            $this->subscribe(event: $event, listener: [$subscriber, $method]);
        }
    }

    /**
     * Register a listener for a specific event type.
     *
     * @param  string  $event  Event class name.
     * @param  callable  $listener  Callback to invoke.
     */
    public function subscribe(string $event, callable $listener): void
    {
        $this->listeners[$event][] = $listener;
    }
}
