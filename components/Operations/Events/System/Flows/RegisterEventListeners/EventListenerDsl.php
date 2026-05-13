<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Flows\RegisterEventListeners;

use Avax\Components\Application\Container\System\Capabilities\ResolveCallable\ResolveCallable;
use Avax\Components\Operations\Events\System\Capabilities\Registry\ListenerRegistry;
use Psr\Container\ContainerInterface;

/**
 * Fluent DSL for registering event listeners.
 *
 * Usage:
 *   onEvent(UserRegistered::class)
 *       ->do(SendWelcomeEmail::class)
 *       ->do(AuditUserRegistration::class, priority: 100);
 */
final class EventListenerDsl
{
    private ResolveCallable $resolveCallable;

    public function __construct(
        private readonly string $eventClass,
        private readonly ListenerRegistry $registry,
        ?ContainerInterface $container = null,
    ) {
        $this->resolveCallable = new ResolveCallable($container);
    }

    /**
     * Register a listener for this event.
     *
     * @param  class-string|callable  $listener
     * @param  int  $priority  Higher = earlier execution. Default 0.
     */
    public function do(string|callable $listener, int $priority = 0): self
    {
        // Use central resolver for class-string listeners.
        $callable = is_string($listener)
            ? $this->resolveCallable->resolve($listener)
            : $listener;

        $this->registry->subscribe($this->eventClass, $callable, $priority); // @phpstan-ignore-line

        return $this;
    }
}
