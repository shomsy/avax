<?php

declare(strict_types=1);

namespace Avax\Database\Events;

/**
 * The "Subscription Form" (Event Subscriber Rulebook).
 *
 * -- what is it?
 * This is an Interface (a contract) for classes that want to listen to 
 * multiple different things at once. Instead of signing up one-by-one, 
 * a "Subscriber" provides a full list of everything it's interested in.
 *
 * -- how to imagine it:
 * Think of the "Back Page" of a magazine where you check off which topics 
 * you want to get updates for. You say "Send me the Sports news to my 
 * home phone, and the Weather news to my email."
 *
 * -- why this exists:
 * To keep things organized. If you have a class that handles "Logging", it 
 * might want to listen to 5 different database events. Instead of manually 
 * signing it up 5 times, it just says "Here is my list of 5 things I care about".
 */
interface EventSubscriberInterface
{
    /**
     * Get the list of all events this class wants to hear about.
     *
     * @return array<string, string> A map where the "News Type" is the key and your "Handler Method" is the value.
     */
    public function getSubscribedEvents(): array;
}
