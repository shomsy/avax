<?php
declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Capabilities\Registry;

/**
 * Registry for event listeners with priority support.
 */
final class ListenerRegistry
{
    /** @var array<string, array<int, array<callable>>> */
    private array $listeners = [];

    /** @var array<string, array<callable>> Sorted cache */
    private array $sorted = [];

    public function subscribe(string $event, callable $listener, int $priority = 0): void
    {
        $this->listeners[$event][$priority][] = $listener;
        unset($this->sorted[$event]);
    }

    public function getListenersFor(string $event): array
    {
        if (isset($this->sorted[$event])) {
            return $this->sorted[$event];
        }

        if (!isset($this->listeners[$event])) {
            return [];
        }

        $eventListeners = $this->listeners[$event];
        krsort($eventListeners);

        $this->sorted[$event] = array_merge(...array_values($eventListeners));

        return $this->sorted[$event];
    }

    public function clear(): void
    {
        $this->listeners = [];
        $this->sorted = [];
    }
}
