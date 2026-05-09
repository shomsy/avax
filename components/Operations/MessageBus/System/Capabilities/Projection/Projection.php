<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\Capabilities\Projection;

use Avax\Components\Operations\MessageBus\System\Capabilities\Envelope\MessageEnvelope;
use Closure;

/**
 * Event projection: transforms event envelopes into read model state.
 *
 * Each projection registers handlers for specific event types and
 * builds a read model by applying events sequentially.
 */
final class Projection
{
    /** @var array<string, Closure> */
    private array $handlers = [];

    /** @var array<string, mixed> */
    private array $state = [];

    /**
     * Register a handler for an event type.
     *
     * @param Closure(MessageEnvelope): void $handler
     */
    public function on(string $eventType, Closure $handler) : self
    {
        $this->handlers[$eventType] = $handler;

        return $this;
    }

    /**
     * Apply a single event envelope to the projection.
     */
    public function apply(MessageEnvelope $event) : void
    {
        if (!isset($this->handlers[$event->type])) {
            return;
        }

        ($this->handlers[$event->type])($event);
    }

    /**
     * Apply multiple event envelopes in order.
     *
     * @param list<MessageEnvelope> $events
     */
    public function applyAll(array $events) : void
    {
        foreach ($events as $event) {
            $this->apply($event);
        }
    }

    /**
     * Get the current read model state.
     *
     * @return array<string, mixed>
     */
    public function state() : array
    {
        return $this->state;
    }

    /**
     * Update a specific state key.
     */
    public function setState(string $key, mixed $value) : void
    {
        $this->state[$key] = $value;
    }

    /**
     * Get a specific state value.
     */
    public function getState(string $key, mixed $default = null) : mixed
    {
        return $this->state[$key] ?? $default;
    }

    /**
     * Reset the projection state.
     */
    public function reset() : void
    {
        $this->state = [];
    }
}
