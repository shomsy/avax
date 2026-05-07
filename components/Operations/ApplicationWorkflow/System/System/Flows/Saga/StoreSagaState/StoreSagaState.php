<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\StoreSagaState;

use Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\StartSaga\SagaInstance;

final readonly class StoreSagaState
{
    public function __construct(private array $stores = []) {}

    public static function inMemory() : self
    {
        return new self(stores: ['default' => []]);
    }

    public function save(SagaInstance $sagaInstance) : void
    {
        $store                    = $this->getStore(name: 'default');
        $store[$sagaInstance->id] = $sagaInstance;
        $this->stores['default']  = $store;
    }

    private function getStore(string $name) : array
    {
        return $this->stores[$name] ?? [];
    }

    public function load(string $id) : ?SagaInstance
    {
        $store = $this->getStore(name: 'default');

        return $store[$id] ?? null;
    }

    public function appendEvent(string $sagaId, SagaEvent $sagaEvent) : void
    {
        $key   = 'events_' . $sagaId;
        $store = $this->getStore(name: $key);

        if (! isset($store['events'])) {
            $store['events'] = [];
        }

        $store['events'][] = $sagaEvent;

        $this->stores[$key] = $store;
    }

    public function getEvents(string $sagaId) : array
    {
        $key   = 'events_' . $sagaId;
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
