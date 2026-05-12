<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Foundation;

/**
 * An immutable record of a listener registration.
 *
 * Carries all metadata needed to register, compile, and invoke a listener.
 */
final readonly class ListenerRegistration
{
    /** @param callable $listener */
    public function __construct(
        public string $eventClass,
        public mixed $listener,
        public int $priority = 0,
        public ListenerSource $source = ListenerSource::Dsl,
        public ListenerExecutionMode $mode = ListenerExecutionMode::Sync,
        public int $order = 0,
    ) {
    }
}
