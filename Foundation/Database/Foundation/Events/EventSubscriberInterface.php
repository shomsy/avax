<?php

declare(strict_types=1);

namespace Avax\Database\Events;

/**
 * Defines the contract for external classes that wish to subscribe to multiple events.
 *
 * -- intent: enable organized, class-based handling of multiple distinct events.
 */
interface EventSubscriberInterface
{
    /**
     * Retrieve the mapping of event types to their handling methods in this class.
     *
     * -- intent: declare interest in specific system signals for automated registration.
     *
     * @return array<string, string> Map of event name to method name
     */
    public function getSubscribedEvents() : array;
}
