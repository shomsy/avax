<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\StartSaga;

use Random\RandomException;

/**
 * CreateSagaCorrelationId - creates stable or deterministic correlation ids before saga state is written.
 */
final readonly class CreateSagaCorrelationId
{
    /**
     * @throws RandomException
     */
    public function create(SagaStartCommand $sagaStartCommand) : SagaCorrelationId
    {
        if ($sagaStartCommand->correlationId !== null && $sagaStartCommand->correlationId !== '') {
            return new SagaCorrelationId(value: $sagaStartCommand->correlationId);
        }

        if ($sagaStartCommand->commandKey !== null && $sagaStartCommand->commandKey !== '') {
            return new SagaCorrelationId(value: 'saga-correlation-' . hash(algo: 'xxh128', data: $sagaStartCommand->definitionName . ':' . $sagaStartCommand->commandKey));
        }

        return new SagaCorrelationId(value: 'saga-correlation-' . bin2hex(string: random_bytes(length: 8)));
    }
}
