<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events;

use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events\Contracts\DispatchStrategyInterface;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events\Contracts\EventBusInterface;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events\Strategy\SyncDispatchStrategy;

/**
 * Central event dispatcher for database lifecycle and telemetry signals.
 *
 * @see /docs/Foundation/Database/Concepts/Telemetry.md
 */
final class EventBus implements EventBusInterface
{
    /** @var array<string, array<int, callable>> A list of everyone signed up for each type of news. */
    private array $listeners = [];

    /**
     * @param  DispatchStrategyInterface  $dispatchStrategy  The logic for HOW to deliver the news (e.g., "Do it now" or "Queue
     *                                                       *                                            it").
     */
    public function __construct(private readonly DispatchStrategyInterface $dispatchStrategy = new SyncDispatchStrategy())
    {
    }

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
        $this->dispatchStrategy->handle(event: $event, listeners: $this->listeners[$name]);
    }

    /**
     * Sign up a multi-topic "Subscriber" (a class that listens to many things).
     *
     * @param  EventSubscriberInterface  $eventSubscriber  A helper object that contains multiple different listeners.
     */
    public function registerSubscriber(EventSubscriberInterface $eventSubscriber): void
    {
        foreach ($eventSubscriber->getSubscribedEvents() as $event => $method) {
            $this->subscribe(event: $event, listener: [$eventSubscriber, $method]);
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
