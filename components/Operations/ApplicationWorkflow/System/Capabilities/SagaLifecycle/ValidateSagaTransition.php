<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaLifecycle;

use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaState\SagaState;
use Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\Saga;

/**
 * Validates saga state transitions before operations.
 */
final readonly class ValidateSagaTransition
{
    /**
     * @throws \RuntimeException when saga is not in a resumable state
     */
    public function assertResumable(Saga $saga, string $sagaId): void
    {
        $state = $saga->getStatus();

        if ($state !== SagaState::Running && $state !== SagaState::Failed) {
            throw new \RuntimeException(
                sprintf('Cannot resume saga in "%s" state', $state->value),
            );
        }
    }
}
