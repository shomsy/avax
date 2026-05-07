<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\Capabilities\SagaStore;

use Avax\Components\Operations\ApplicationWorkflow\System\System\Capabilities\SagaState\SagaState;
use Avax\Components\Operations\ApplicationWorkflow\System\System\PublicSurface\Saga;

/**
 * Simple in-memory saga store for testing and development.
 */
final class InMemorySagaStore implements SagaStoreInterface
{
    /**
     * @var array<string, Saga>
     */
    private array $sagas = [];

    public function save(Saga $saga) : void
    {
        $this->sagas[$saga->getId()] = $saga;
    }

    public function findById(string $id) : ?Saga
    {
        return $this->sagas[$id] ?? null;
    }

    public function updateStatus(Saga $saga, SagaState $sagaState) : void
    {
        $saga->setStatus($sagaState);
        $this->sagas[$saga->getId()] = $saga;
    }

    public function delete(string $id) : void
    {
        unset($this->sagas[$id]);
    }

    /**
     * Clear all stored sagas (useful for testing).
     */
    public function clear() : void
    {
        $this->sagas = [];
    }

    /**
     * Get the count of stored sagas.
     */
    public function count() : int
    {
        return count($this->sagas);
    }
}
