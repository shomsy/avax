<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Foundation;

/**
 * Canonical compiled listener registry.
 *
 * Merges DSL registrations and #[ListensTo] attribute declarations
 * into one frozen registry. Runtime dispatch reads this only —
 * no attribute reflection in the hot path.
 *
 * In-memory only for V5.7. Disk persistence is ROADMAP.
 */
final class CompiledListenerRegistry
{
    /** @var array<string, list<CompiledListener>> */
    private array $listeners = [];

    private bool $frozen = false;

    /**
     * Register a compiled listener.
     *
     * @throws \LogicException If registry is frozen.
     */
    public function add(CompiledListener $listener): void
    {
        if ($this->frozen) {
            throw new \LogicException('Cannot register listener after CompiledListenerRegistry is frozen.');
        }

        $this->listeners[$listener->eventClass][] = $listener;
    }

    /**
     * Return listeners for the given event class, sorted by priority descending.
     * Same-priority listeners preserve registration order.
     *
     * @param  class-string|object  $eventClass
     * @return list<CompiledListener>
     */
    public function getListenersFor(string|object $eventClass): array
    {
        $class = is_object($eventClass) ? $eventClass::class : $eventClass;

        if (! isset($this->listeners[$class])) {
            return [];
        }

        $sorted = $this->listeners[$class];

        // Stable sort by priority descending.
        usort($sorted, static fn (CompiledListener $a, CompiledListener $b): int => $b->priority <=> $a->priority);

        return $sorted;
    }

    /**
     * Freeze the registry. No further registrations allowed.
     */
    public function freeze(): void
    {
        $this->frozen = true;
    }

    /**
     * Check if the registry is frozen.
     */
    public function isFrozen(): bool
    {
        return $this->frozen;
    }

    /**
     * Check if any listeners are registered for the event.
     *
     * @param  class-string|object  $eventClass
     */
    public function hasListeners(string|object $eventClass): bool
    {
        $class = is_object($eventClass) ? $eventClass::class : $eventClass;

        return isset($this->listeners[$class]) && $this->listeners[$class] !== [];
    }

    /**
     * Return all registered event class names.
     *
     * @return list<string>
     */
    public function getEventClasses(): array
    {
        return array_keys($this->listeners);
    }

    /**
     * Total listener count across all events.
     */
    public function totalListenerCount(): int
    {
        $count = 0;
        foreach ($this->listeners as $eventListeners) {
            $count += count($eventListeners);
        }

        return $count;
    }

    /**
     * Reset for testing purposes.
     *
     * @internal
     */
    public function reset(): void
    {
        $this->listeners = [];
        $this->frozen = false;
    }
}
