<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Foundation;

/**
 * A pre-compiled listener ready for dispatch.
 *
 * Produced by compiling ListenerRegistration objects during boot.
 * DSL-sourced listeners store the callable directly.
 * Attribute-sourced listeners store the class-string (resolved at dispatch time).
 */
final readonly class CompiledListener
{
    /**
     * @param callable|class-string $listener Callable for DSL sources, class-string for attribute sources.
     */
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
