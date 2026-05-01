<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\TestingFakes;

/**
 * Fake event dispatcher for testing.
 *
 * Records all dispatched events and allows assertions.
 */
final class EventFake
{
    /**
     * @var array<string, int>
     */
    private array $dispatched = [];

    /**
     * @var array<string, list<array<string, mixed>>>
     */
    private array $dispatchedPayloads = [];

    private bool $preventingRealDispatch = true;

    /**
     * @param array<string, mixed> $payload
     */
    public function dispatch(string $event, array $payload = []) : void
    {
        $this->dispatched[$event]           = ($this->dispatched[$event] ?? 0) + 1;
        $this->dispatchedPayloads[$event][] = $payload;
    }

    public function assertDispatched(string $event, int|null $times = null) : self
    {
        if (! $this->wasDispatched($event)) {
            throw new TestingFakeException(
                sprintf(
                    "Event '%s' was not dispatched. Expected: dispatched, Actual: not dispatched",
                    $event,
                ),
            );
        }

        if ($times !== null && $this->dispatched[$event] !== $times) {
            throw new TestingFakeException(
                sprintf(
                    "Event '%s' was dispatched %d times, expected %d",
                    $event,
                    $this->dispatched[$event],
                    $times,
                ),
            );
        }

        return $this;
    }

    public function wasDispatched(string $event) : bool
    {
        return isset($this->dispatched[$event]);
    }

    public function assertNotDispatched(string $event) : self
    {
        if ($this->wasDispatched($event)) {
            throw new TestingFakeException(
                sprintf(
                    "Event '%s' was dispatched unexpectedly. Dispatched %d times",
                    $event,
                    $this->dispatched[$event],
                ),
            );
        }

        return $this;
    }

    public function assertNothingDispatched() : self
    {
        if (! empty($this->dispatched)) {
            throw new TestingFakeException(
                sprintf(
                    "Expected no events to be dispatched, but %d event(s) were: %s",
                    count($this->dispatched),
                    implode(', ', array_keys($this->dispatched)),
                ),
            );
        }

        return $this;
    }

    public function dispatchCount(string $event) : int
    {
        return $this->dispatched[$event] ?? 0;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function payloads(string $event) : array
    {
        return $this->dispatchedPayloads[$event] ?? [];
    }

    /**
     * @return array<string, int>
     */
    public function dispatched() : array
    {
        return $this->dispatched;
    }

    public function preventRealDispatch() : self
    {
        $this->preventingRealDispatch = true;

        return $this;
    }

    public function allowRealDispatch() : self
    {
        $this->preventingRealDispatch = false;

        return $this;
    }

    public function isPreventingRealDispatch() : bool
    {
        return $this->preventingRealDispatch;
    }

    public function clear() : void
    {
        $this->dispatched         = [];
        $this->dispatchedPayloads = [];
    }
}
