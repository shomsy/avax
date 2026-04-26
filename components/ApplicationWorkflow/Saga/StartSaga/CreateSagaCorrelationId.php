<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\StartSaga;

use Random\RandomException;

/**
 * CreateSagaCorrelationId - creates stable or deterministic correlation ids before saga state is written.
 */
final readonly class CreateSagaCorrelationId
{
    /**
     * @throws RandomException
     */
    public function create(SagaStartCommand $command) : SagaCorrelationId
    {
        if ($command->correlationId !== null && $command->correlationId !== '') {
            return new SagaCorrelationId(value: $command->correlationId);
        }

        if ($command->commandKey !== null && $command->commandKey !== '') {
            return new SagaCorrelationId(value: 'saga-correlation-' . hash(algo: 'xxh128', data: $command->definitionName . ':' . $command->commandKey));
        }

        return new SagaCorrelationId(value: 'saga-correlation-' . bin2hex(string: random_bytes(length: 8)));
    }
}
