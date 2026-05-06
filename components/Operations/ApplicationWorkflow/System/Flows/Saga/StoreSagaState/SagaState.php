<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState;

use ArrayAccess;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga\SagaInstance;
use Countable;
use IteratorAggregate;
use RuntimeException;
use Traversable;

final readonly class SagaState implements ArrayAccess, Countable, IteratorAggregate
{
    private array $events;

    private array $data;

    private int $currentStepIndex;

    public function __construct(
        ?array $events = null,
        ?array $data = null,
        private ?string $currentStep = null,
        ?int $currentStepIndex = null,
        private array $stepResults = [],
    ) {
        $events ??= [];
        $data ??= [];
        $currentStepIndex ??= 0;
        $this->events = $events;
        $this->data = $data;
        $this->currentStepIndex = $currentStepIndex;
    }

    public static function empty(array $initialData = []): self
    {
        return new self(
            events: [],
            data  : $initialData,
        );
    }

    public static function fromInstance(SagaInstance $sagaInstance): self
    {
        return new self(
            events          : [],
            data            : $sagaInstance->data,
            currentStep     : $sagaInstance->currentStepName,
            currentStepIndex: $sagaInstance->currentStepIndex,
            stepResults     : $sagaInstance->stepResults,
        );
    }

    public function appendEvent(SagaEvent $sagaEvent): self
    {
        $events = $this->events;
        $events[] = $sagaEvent;

        $data = $this->data;
        if ($sagaEvent->type === 'step_completed' && isset($sagaEvent->payload['data'])) {
            $data = array_merge($data, $sagaEvent->payload['data']);
        }

        return new self(
            events          : $events,
            data            : $data,
            currentStep     : $this->currentStep,
            currentStepIndex: $this->currentStepIndex,
            stepResults     : $this->stepResults,
        );
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function getStepResults(): array
    {
        return $this->stepResults;
    }

    public function getStepResult(string $stepName): ?array
    {
        return $this->stepResults[$stepName] ?? null;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new RuntimeException('SagaState is readonly.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new RuntimeException('SagaState is readonly.');
    }

    public function count(): int
    {
        return count($this->events);
    }

    public function getIterator(): Traversable
    {
        foreach ($this->events as $event) {
            yield $event;
        }
    }
}
