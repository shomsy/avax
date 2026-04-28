<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ResumeSaga;

enum SagaRecoveryStrategy: string
{
    case RETRY      = 'retry';
    case COMPENSATE = 'compensate';
    case SKIP       = 'skip';
    case ABANDON    = 'abandon';
}

final readonly class SagaRecoveryPlan
{
    public function __construct(
        public SagaRecoveryStrategy $strategy,
        public int                  $maxRetries,
        public int                  $retryDelayMs,
        public bool                 $allowSkipSteps
    ) {}

    public static function standard() : self
    {
        return new self(
            strategy      : SagaRecoveryStrategy::RETRY,
            maxRetries    : 3,
            retryDelayMs  : 1000,
            allowSkipSteps: false
        );
    }

    public static function aggressive() : self
    {
        return new self(
            strategy      : SagaRecoveryStrategy::COMPENSATE,
            maxRetries    : 5,
            retryDelayMs  : 500,
            allowSkipSteps: true
        );
    }

    public function describeResponsibility() : string
    {
        return 'plans saga recovery including strategy, retries, and delay.';
    }

    public function canRetry(int $attempt) : bool
    {
        return $this->strategy === SagaRecoveryStrategy::RETRY
            && $attempt < $this->maxRetries;
    }

    public function calculateDelay(int $attempt) : int
    {
        return $this->retryDelayMs * (2 ** $attempt);
    }

    public function toMetadata() : array
    {
        return [
            'strategy'         => $this->strategy->value,
            'max_retries'      => $this->maxRetries,
            'retry_delay_ms'   => $this->retryDelayMs,
            'allow_skip_steps' => $this->allowSkipSteps,
        ];
    }
}