<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\Capabilities\SagaStore;

use Avax\Components\Operations\ApplicationWorkflow\System\System\Capabilities\SagaState\SagaState;
use Avax\Components\Operations\ApplicationWorkflow\System\System\PublicSurface\Saga;

/**
 * Interface for persisting and retrieving saga instances.
 */
interface SagaStoreInterface
{
    /**
     * Save a saga instance to the store.
     */
    public function save(Saga $saga) : void;

    /**
     * Find a saga instance by its ID.
     */
    public function findById(string $id) : ?Saga;

    /**
     * Update the status of a saga instance.
     */
    public function updateStatus(Saga $saga, SagaState $sagaState) : void;

    /**
     * Delete a saga instance from the store.
     */
    public function delete(string $id) : void;
}
