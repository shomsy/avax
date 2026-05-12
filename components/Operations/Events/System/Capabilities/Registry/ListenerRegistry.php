<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Capabilities\Registry;

use Avax\Components\Operations\Events\System\Foundation\ListenerRegistration;

/**
 * Registry for event listeners with priority support.
 */
final class ListenerRegistry
{
    /** @var array<string, array<int, array<callable>>> */
    private array $listeners = [];

    /** @var array<string, array<callable>> Sorted cache */
    private array $sorted = [];

    private int $registrationOrder = 0;

    public function subscribe(string $event, callable $listener, int $priority = 0): void
    {
        $this->listeners[$event][$priority][] = $listener;
        unset($this->sorted[$event]);
    }

    /**
     * Register a typed listener registration.
     */
    public function register(ListenerRegistration $registration): void
    {
        $this->listeners[$registration->eventClass][$registration->priority][] = $registration->listener;
        unset($this->sorted[$registration->eventClass]);
    }

    public function getListenersFor(string $event): array
    {
        if (isset($this->sorted[$event])) {
            return $this->sorted[$event];
        }

        if (! isset($this->listeners[$event])) {
            return [];
        }

        $eventListeners = $this->listeners[$event];
        krsort($eventListeners);

        $this->sorted[$event] = array_merge(...array_values($eventListeners));

        return $this->sorted[$event];
    }

    /**
     * Alias for getListenersFor — matches PSR-14 listener provider naming.
     *
     * @return array<int, callable>
     */
    public function listenersFor(string $eventClass): array
    {
        return $this->getListenersFor($eventClass);
    }

    public function clear(): void
    {
        $this->listeners = [];
        $this->sorted = [];
    }

    public function remove(string $event): void
    {
        unset($this->listeners[$event], $this->sorted[$event]);
    }

    public function hasListeners(string $event): bool
    {
        return isset($this->listeners[$event]) && (isset($this->listeners[$event]) && $this->listeners[$event] !== []);
    }

    public function listenerCount(string $event): int
    {
        if (! isset($this->listeners[$event])) {
            return 0;
        }

        return array_sum(array_map(count(...), $this->listeners[$event]));
    }
}
