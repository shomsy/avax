<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ResumeSaga;

final readonly class SagaRecoveryResult
{
    public function __construct(
        public string $sagaId,
        public bool $recoverable,
        public SagaRecoveryAction $action,
        public string $reason,
    ) {}
}
