<?php

declare(strict_types=1);

namespace Avax\Database\Events\Contracts;

use Avax\Database\Events\Event;

/**
 * Technical contract defining the processing strategy for system-wide signals.
 *
 * -- intent:
 * Abstract the execution policy (synchronous, asynchronous, queued, or deferred)
 * for signal distribution, allowing the EventBus to remain agnostic of the
 * physical performance profile required for observer execution.
 *
 * -- invariants:
 * - Implementations must accept a generic Event payload and a collection of listeners.
 * - The strategy must not modify the Event payload during processing.
 *
 * -- boundaries:
 * - Does NOT handle listener discovery (delegated to EventBus).
 * - Does NOT handle error normalization (delegated to listeners or specific strategies).
 */
interface DispatchStrategyInterface
{
    /**
     * Coordinate the technical execution of a collection of observers for a specific signal.
     *
     * -- intent:
     * Physically triggers the provided listeners in accordance with the
     * implementation's performance strategy (e.g., executing all listeners
     * sequentially in the current process).
     *
     * @param  Event  $event  The technical signal payload to be distributed.
     * @param  iterable<callable>  $listeners  The collection of authorized technical handlers/observers to be triggered.
     */
    public function handle(Event $event, iterable $listeners): void;
}
