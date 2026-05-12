<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Capabilities\InvokeEventListener;

/**
 * Invokes a single resolved listener against an event object.
 *
 * Listener return values are ignored.
 * Exceptions bubble up by default — no catch-and-ignore.
 */
final class InvokeEventListener
{
    /**
     * Invoke a listener with the given event.
     *
     * @param callable $listener Resolved listener callable.
     * @param object $event Event object.
     */
    public function invoke(callable $listener, object $event): void
    {
        $listener($event);
    }
}
