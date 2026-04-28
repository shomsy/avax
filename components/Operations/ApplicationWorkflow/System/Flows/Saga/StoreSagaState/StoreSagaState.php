<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState;

use ArrayAccess;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga\SagaInstance;
use Countable;
use DateTimeImmutable;
use DateTimeInterface;
use IteratorAggregate;
use Random\RandomException;
use Traversable;

final readonly class SagaState implements ArrayAccess, Countable, IteratorAggregate
{
    private array       $events;
    private array       $data;
    private string|null $currentStep;
    private int         $currentStepIndex;
    private array       $stepResults;

    public function __construct(
        array|null  $events = null,
        array|null  $data = null,
        string|null $currentStep = null,
        int|null    $currentStepIndex = null,
        array       $stepResults = []
    )
    {
        $events                 ??= [];
        $data                   ??= [];
        $currentStepIndex       ??= 0;
        $this->events           = $events;
        $this->data             = $data;
        $this->currentStep      = $currentStep;
        $this->currentStepIndex = $currentStepIndex;
        $this->stepResults      = $stepResults;
    }

    public static function empty(array $initialData = []) : self
    {
        return new self(
            events: [],
            data  : $initialData
        );
    }

    public static function fromInstance(SagaInstance $instance) : self
    {
        return new self(
            events          : [],
            data            : $instance->data,
            currentStep     : $instance->currentStepName,
            currentStepIndex: $instance->currentStepIndex,
            stepResults     : $instance->stepResults
        );
    }

    public function appendEvent(SagaEvent $event) : self
    {
        $events   = $this->events;
        $events[] = $event;

        $data = $this->data;
        if ($event->type === 'step_completed' && isset($event->payload['data'])) {
            $data = array_merge($data, $event->payload['data']);
        }

        return new self(
            events          : $events,
            data            : $data,
            currentStep     : $this->currentStep,
            currentStepIndex: $this->currentStepIndex,
            stepResults     : $this->stepResults
        );
    }

    public function getEvents() : array
    {
        return $this->events;
    }

    public function getData() : array
    {
        return $this->data;
    }

    public function get(string $key) : mixed
    {
        return $this->data[$key] ?? null;
    }

    public function has(string $key) : bool
    {
        return isset($this->data[$key]);
    }

    public function getStepResults() : array
    {
        return $this->stepResults;
    }

    public function getStepResult(string $stepName) : array|null
    {
        return $this->stepResults[$stepName] ?? null;
    }

    public function offsetGet(mixed $offset) : mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetExists(mixed $offset) : bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetSet(mixed $offset, mixed $value) : void
    {
        throw new RuntimeException('SagaState is readonly.');
    }

    public function offsetUnset(mixed $offset) : void
    {
        throw new RuntimeException('SagaState is readonly.');
    }

    public function count() : int
    {
        return count($this->events);
    }

    public function getIterator() : Traversable
    {
        foreach ($this->events as $event) {
            yield $event;
        }
    }
}

final readonly class SagaEvent
{
    public string            $id;
    public string            $type;
    public string            $sagaId;
    public string            $sagaName;
    public string|null       $stepName;
    public array             $payload;
    public DateTimeImmutable $occurredAt;

    private function __construct(
        string            $id,
        string            $type,
        string            $sagaId,
        string            $sagaName,
        string|null       $stepName = null,
        array|null        $payload = null,
        DateTimeImmutable $occurredAt
    )
    {
        $payload          ??= [];
        $this->id         = $id;
        $this->type       = $type;
        $this->sagaId     = $sagaId;
        $this->sagaName   = $sagaName;
        $this->stepName   = $stepName;
        $this->payload    = $payload;
        $this->occurredAt = $occurredAt;
    }

    public static function started(
        string $sagaId,
        string $sagaName,
        array  $initialData = []
    ) : self
    {
        return self::create(sagaId: $sagaId, sagaName: $sagaName, type: 'saga_started', payload: $initialData);
    }

    public static function create(
        string      $sagaId,
        string      $sagaName,
        string      $type,
        array|null  $payload = null,
        string|null $stepName = null
    ) : self
    {
        $payload ??= [];

        return new self(
            id        : self::generateId(),
            type      : $type,
            sagaId    : $sagaId,
            sagaName  : $sagaName,
            stepName  : $stepName,
            payload   : $payload,
            occurredAt: new DateTimeImmutable()
        );
    }

    /**
     * @throws RandomException
     */
    private static function generateId() : string
    {
        return sprintf('evt_%s_%s', date('YmdHis'), bin2hex(random_bytes(6)));
    }

    public static function stepCompleted(
        string $sagaId,
        string $sagaName,
        string $stepName,
        array  $output = []
    ) : self
    {
        return self::create(sagaId: $sagaId, sagaName: $sagaName, type: 'step_completed', payload: $output, stepName: $stepName);
    }

    public static function stepFailed(
        string $sagaId,
        string $sagaName,
        string $stepName,
        string $error
    ) : self
    {
        return self::create(sagaId: $sagaId, sagaName: $sagaName, type: 'step_failed', payload: ['error' => $error], stepName: $stepName);
    }

    public static function completed(
        string $sagaId,
        string $sagaName,
        array  $finalData = []
    ) : self
    {
        return self::create(sagaId: $sagaId, sagaName: $sagaName, type: 'saga_completed', payload: $finalData);
    }

    public static function compensated(
        string $sagaId,
        string $sagaName
    ) : self
    {
        return self::create(sagaId: $sagaId, sagaName: $sagaName, type: 'saga_compensated');
    }

    public function toArray() : array
    {
        return [
            'id'          => $this->id,
            'type'        => $this->type,
            'saga_id'     => $this->sagaId,
            'saga_name'   => $this->sagaName,
            'step_name'   => $this->stepName,
            'payload'     => $this->payload,
            'occurred_at' => $this->occurredAt->format(format: DateTimeInterface::ISO8601),
        ];
    }
}

final readonly class StoreSagaState
{
    private array $stores;

    public function __construct(array $stores = [])
    {
        $this->stores = $stores;
    }

    public static function inMemory() : self
    {
        return new self(stores: ['default' => []]);
    }

    public function save(SagaInstance $instance) : void
    {
        $store                   = $this->getStore(name: 'default');
        $store[$instance->id]    = $instance;
        $this->stores['default'] = $store;
    }

    private function getStore(string $name) : array
    {
        return $this->stores[$name] ?? [];
    }

    public function load(string $id) : SagaInstance|null
    {
        $store = $this->getStore(name: 'default');

        return $store[$id] ?? null;
    }

    public function appendEvent(string $sagaId, SagaEvent $event) : void
    {
        $key   = "events_{$sagaId}";
        $store = $this->getStore(name: $key);

        if (! isset($store['events'])) {
            $store['events'] = [];
        }
        $store['events'][] = $event;

        $this->stores[$key] = $store;
    }

    public function getEvents(string $sagaId) : array
    {
        $key   = "events_{$sagaId}";
        $store = $this->getStore(name: $key);

        return $store['events'] ?? [];
    }

    public function exists(string $id) : bool
    {
        $store = $this->getStore(name: 'default');

        return isset($store[$id]);
    }

    public function delete(string $id) : void
    {
        $store = $this->getStore(name: 'default');
        unset($store[$id]);
        $this->stores['default'] = $store;
    }
}