<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\PublicSurface;

use Avax\Components\Operations\Events\System\Capabilities\Dispatcher\EventDispatcher;
use Avax\Components\Operations\Events\System\Capabilities\Registry\ListenerRegistry;

/**
 * Public surface for the Events component.
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

    public function dispatch(string|object $event, mixed $data = null): void
    {
        $this->eventDispatcher->dispatch($event, $data);
    }

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
}
