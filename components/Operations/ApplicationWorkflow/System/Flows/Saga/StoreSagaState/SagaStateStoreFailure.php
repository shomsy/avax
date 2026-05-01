<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState;

use RuntimeException;

/**
 * SagaStateStoreFailure - reports missing or conflicting saga state.
 */
final class SagaStateStoreFailure extends RuntimeException
{
    public static function missingState(string $instanceId): self
    {
        return new self(message: sprintf('Saga state %s does not exist.', $instanceId));
    }

    public static function concurrentUpdate(string $instanceId): self
    {
        return new self(message: sprintf('Saga state %s was changed by another writer.', $instanceId));
    }
}
