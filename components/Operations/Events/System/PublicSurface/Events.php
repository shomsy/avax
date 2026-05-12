<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\PublicSurface;

use Avax\Components\Operations\Events\System\Capabilities\Dispatcher\EventDispatcher;
use Avax\Components\Operations\Events\System\Capabilities\Registry\ListenerRegistry;
use Avax\Components\Operations\Events\System\Foundation\EventEmitter;

/**
 * Public surface for the Events component.
 *
 * Provides both legacy string-based dispatch (via EventDispatcher)
 * and modern object-only dispatch (via EventEmitter).
 *
 * For userland code, prefer the global emit() function:
 *   emit(new UserRegistered($userId));
 *
 * Not:
 *   emit(UserRegistered::class);  // NOT supported
 */
final readonly class Events implements EventsInterface
{
    private ListenerRegistry $listenerRegistry;

    private EventDispatcher $eventDispatcher;

    public function __construct()
    {
        $this->listenerRegistry = new ListenerRegistry();
        $this->eventDispatcher = new EventDispatcher($this->listenerRegistry);
    }

    /**
     * Dispatch an event through the legacy EventDispatcher.
     *
     * Supports both string event names and event objects for backward compatibility.
     * For new code, use the global emit() function which accepts only event objects.
     *
     * @param string|object $event
     * @param mixed $data
     */
    public function dispatch(string|object $event, mixed $data = null): void
    {
        $this->eventDispatcher->dispatch($event, $data);
    }

    /**
     * Register a listener for an event.
     */
    public function listen(string $event, callable $listener, int $priority = 0): void
    {
        $this->listenerRegistry->subscribe($event, $listener, $priority);
    }

    public function flush(): void
    {
        $this->listenerRegistry->clear();
    }

    public function forget(string $event): void
    {
        $this->listenerRegistry->remove($event);
    }

    public function hasListeners(string $event): bool
    {
        return $this->listenerRegistry->hasListeners($event);
    }

    public function listenerCount(string $event): int
    {
        return $this->listenerRegistry->listenerCount($event);
    }

    /**
     * Create an EventEmitter wired to a pre-compiled registry.
     *
     * This is called during bootstrap after CompileEventListeners produces
     * a frozen CompiledListenerRegistry.
     */
    public static function createEmitter(EventEmitter $emitter): void
    {
        // Wire into global state for emit() global function.
        \Avax\Components\Operations\Events\System\Foundation\GlobalEventListenerState::setEmitter($emitter);
    }
}
