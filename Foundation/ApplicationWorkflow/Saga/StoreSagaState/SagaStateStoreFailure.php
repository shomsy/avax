<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\StoreSagaState;

use RuntimeException;

/**
 * SagaStateStoreFailure - reports missing or conflicting saga state.
 */
final class SagaStateStoreFailure extends RuntimeException
{
    public static function missingState(string $instanceId) : self
    {
        return new self(message: "Saga state {$instanceId} does not exist.");
    }

    public static function concurrentUpdate(string $instanceId) : self
    {
        return new self(message: "Saga state {$instanceId} was changed by another writer.");
    }
}
