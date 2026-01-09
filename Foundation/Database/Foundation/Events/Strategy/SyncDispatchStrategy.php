<?php

declare(strict_types=1);

namespace Avax\Database\Events\Strategy;

use Avax\Database\Events\Contracts\DispatchStrategyInterface;
use Avax\Database\Events\Event;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Technical execution engine for the synchronous distribution of system signals.
 *
 * -- intent:
 * Implements a "Blocking Dispatch" strategy where every registered observer
 * is triggered immediately within the same request thread as the producer,
 * ensuring predictable order and immediate state consistency.
 *
 * -- invariants:
 * - Observers must be executed sequentially in the order of registration.
 * - Individual observer failures must be defensively captured to prevent
 *   disruption of the entire dispatch chain.
 * - Failures must be logged if an authorized technical logger is provided.
 *
 * -- boundaries:
 * - Does NOT support parallel execution or background processing.
 * - Only handles the physical invocation of listeners.
 */
final readonly class SyncDispatchStrategy implements DispatchStrategyInterface
{
    /**
     * @param LoggerInterface|null $logger Optional technical logger for capturing observer execution failures.
     */
    public function __construct(private LoggerInterface|null $logger = null) {}

    /**
     * Coordinate the sequential and defensive triggering of all authorized observers.
     *
     * -- intent:
     * Physically iterates through the listener collection, invoking each with
     * the signal payload while providing a safety boundary to isolate
     * cross-observer side-effects.
     *
     * @param Event              $event     The technical signal payload to be distributed.
     * @param iterable<callable> $listeners The collection of authorized technical handlers to be triggered.
     *
     * @return void
     */
    public function handle(Event $event, iterable $listeners) : void
    {
        foreach ($listeners as $listener) {
            try {
                $listener($event);
            } catch (Throwable $e) {
                // Defensive capture: isolate observer failure from the producer's thread.
                $this->logger?->error(
                    message: "Event listener execution failed: " . $e->getMessage(),
                    context: [
                        'event'     => $event::class,
                        'exception' => $e
                    ]
                );
            }
        }
    }
}
