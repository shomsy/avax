<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Foundation;

/**
 * A pre-compiled listener ready for dispatch.
 *
 * Produced by compiling ListenerRegistration objects during boot.
 * Used in the hot path — no reflection, no container resolution needed.
 */
final readonly class CompiledListener
{
    /** @param callable $listener */
    public function __construct(
        public string $eventClass,
        public mixed $listener,
        public int $priority,
        public ListenerSource $source,
        public ListenerExecutionMode $mode,
        public int $order,
    ) {
    }
}
