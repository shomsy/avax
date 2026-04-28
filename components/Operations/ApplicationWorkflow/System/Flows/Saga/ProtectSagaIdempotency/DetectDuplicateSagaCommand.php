<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ProtectSagaIdempotency;

/**
 * DetectDuplicateSagaCommand - checks whether a saga command key has already produced a result.
 */
final readonly class DetectDuplicateSagaCommand
{
    /**
     * @param array<string, SagaCommandResult> $results
     */
    public function detect(array $results, SagaCommandKey $key) : bool
    {
        return array_key_exists(key: $key->value, array: $results);
    }
}
