<?php

declare(strict_types=1);

namespace Avax\Database\Events\Contracts;

use Avax\Database\Events\Event;
use Avax\Database\Events\EventSubscriberInterface;

/**
 * The "Radio Command Center" (Event Bus Rulebook).
 *
 * -- what is it?
 * This is an Interface (a contract) that defines how the system should
 * handle "Shouts" (Events). Any object that wants to be the "Event Bus"
 * for the application must follow these rules.
 *
 * -- how to imagine it:
 * Think of the "Dispatcher" in a taxi company. The Dispatcher must be able
 * to:
 * 1. Hear a shout from a taxi (Dispatch).
 * 2. Know which taxi drivers are listening to which channels (Subscribe).
 * 3. Sign up many drivers at once (Register Subscriber).
 *
 * -- why this exists:
 * To make the notification system swappable. We might have a simple
 * Dispatcher today, but tomorrow we might need a more complex one that
 * records all messages to a database. As long as they both follow this
 * rulebook, the rest of the application doesn't care which taxi company
 * we use.
 */
interface EventBusInterface
{
    /**
     * Shout out an event to everyone listening.
     *
     * @param Event $event The "News" piece (e.g., "Connection Opened").
     */
    public function dispatch(Event $event) : void;

    /**
     * Sign up a multi-topic "Subscriber" (a class that listens to many things).
     *
     * @param EventSubscriberInterface $subscriber A helper who has a list of everything they care about.
     */
    public function registerSubscriber(EventSubscriberInterface $subscriber) : void;

    /**
     * Manually sign up a single listener for a specific signal.
     *
     * @param string   $event    The full name of the News Type you want to hear.
     * @param callable $listener The specific code block to run.
     */
    public function subscribe(string $event, callable $listener) : void;
}
