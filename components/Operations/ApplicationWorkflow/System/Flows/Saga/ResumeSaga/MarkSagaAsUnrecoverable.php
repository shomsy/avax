<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ResumeSaga;

use InvalidArgumentException;

enum SagaRecoveryAction: string
{
    case RETRY   = 'retry';
    case COMPENSATE = 'compensate';
    case ABANDON = 'abandon';
    case MANUAL  = 'manual';
}

final readonly class MarkSagaAsUnrecoverable
{
    public function __construct(
        private SagaRecoveryAction $action,
    ) {}

    public function describeResponsibility(): string
    {
        return 'marks a saga as unrecoverable when recovery rules reject it.';
    }

    public function mark(array $sagaData, array $failureReasons): SagaRecoveryResult
    {
        if (empty($failureReasons)) {
            throw new InvalidArgumentException(message: 'Failure reasons cannot be empty.');
        }

        $shouldAbandon = $this->shouldAbandon(reasons: $failureReasons);

        return new SagaRecoveryResult(
            sagaId     : $sagaData['id'],
            recoverable: ! $shouldAbandon,
            action     : $shouldAbandon ? SagaRecoveryAction::ABANDON : $this->action,
            reason     : implode('; ', $failureReasons),
        );
    }

    private function shouldAbandon(array $reasons): bool
    {
        $maxRetries = 3;
        $retryCount = 0;

        foreach ($reasons as $reason) {
            if (str_contains($reason, 'max_retries')) {
                $retryCount++;
            }
        }

        return $retryCount >= $maxRetries;
    }

    public function toMetadata(): array
    {
        return ['recovery_action' => $this->action->value];
    }
}

final readonly class SagaRecoveryResult
{
    public function __construct(
        public string $sagaId,
        public bool $recoverable,
        public SagaRecoveryAction $action,
        public string $reason,
    ) {}
}
