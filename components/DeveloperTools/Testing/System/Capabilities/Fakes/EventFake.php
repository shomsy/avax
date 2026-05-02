<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Testing\System\Capabilities\Fakes;

use PHPUnit\Framework\Assert;

/**
 * EventFake - captures dispatched events for assertions in tests.
 * Replaces the real event dispatcher during testing.
 */
class EventFake
{
    /** @var array<string, list<array{event: string, data: mixed}>> */
    private array $dispatched = [];

    /** @var array<string, list<callable>> */
    private array $listeners = [];

    public function dispatch(string|object $event, mixed $data = null): void
    {
        $eventName = is_object($event) ? $event::class : $event;

        if (! isset($this->dispatched[$eventName])) {
            $this->dispatched[$eventName] = [];
        }

        $this->dispatched[$eventName][] = [
            'event' => $eventName,
            'data' => $data,
        ];

        // Call registered listeners
        if (isset($this->listeners[$eventName])) {
            foreach ($this->listeners[$eventName] as $listener) {
                $listener($event, $data);
            }
        }
    }

    public function listen(string $event, callable $listener): void
    {
        if (! isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        $this->listeners[$event][] = $listener;
    }

    public function flush(): void
    {
        $this->dispatched = [];
        $this->listeners = [];
    }

    /**
     * Assert that an event was dispatched.
     */
    public function assertDispatched(string $event): void
    {
        Assert::assertTrue(
            $this->hasDispatched($event),
            sprintf('The expected [%s] event was not dispatched.', $event),
        );
    }

    /**
     * Check if an event was dispatched.
     */
    public function hasDispatched(string $event): bool
    {
        return isset($this->dispatched[$event]) && (isset($this->dispatched[$event]) && $this->dispatched[$event] !== []);
    }

    /**
     * Assert that an event was not dispatched.
     */
    public function assertNotDispatched(string $event): void
    {
        Assert::assertFalse(
            $this->hasDispatched($event),
            sprintf('The unexpected [%s] event was dispatched.', $event),
        );
    }

    /**
     * Assert that an event was dispatched N times.
     */
    public function assertDispatchedTimes(string $event, int $times = 1): void
    {
        $count = $this->dispatchedCount($event);

        Assert::assertSame(
            $times,
            $count,
            sprintf('The [%s] event was dispatched %d times instead of %d times.', $event, $count, $times),
        );
    }

    /**
     * Get the count of times an event was dispatched.
     */
    public function dispatchedCount(string $event): int
    {
        return count($this->dispatched[$event] ?? []);
    }

    /**
     * Assert that an event was dispatched with specific data.
     */
    public function assertDispatchedWith(string $event, mixed $expectedData): void
    {
        Assert::assertTrue(
            $this->hasDispatchedWith($event, $expectedData),
            sprintf('The [%s] event was not dispatched with the expected data.', $event),
        );
    }

    /**
     * Check if an event was dispatched with specific data.
     */
    public function hasDispatchedWith(string $event, mixed $expectedData): bool
    {
        if (! $this->hasDispatched($event)) {
            return false;
        }

        foreach ($this->dispatched[$event] as $dispatched) {
            if ($dispatched['data'] === $expectedData) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all dispatched events.
     *
     * @return array<string, list<array{event: string, data: mixed}>>
     */
    public function dispatched(): array
    {
        return $this->dispatched;
    }
}
