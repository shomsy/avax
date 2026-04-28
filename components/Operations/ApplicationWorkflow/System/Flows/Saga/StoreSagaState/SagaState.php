<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState;

/**
 * SagaState - recoverable workflow state used by step execution, completion, compensation, and resume paths.
 */
final readonly class SagaState
{
    /**
     * @param list<string> $completedStepNames
     */
    public function __construct(
        public string      $instanceId,
        public string      $definitionName,
        public string      $correlationId,
        public string|null $currentStepName,
        public string      $status = 'started',
        public array       $completedStepNames = [],
        public int         $version = 0
    ) {}

    public function recordCompletedStep(string $stepName, string|null $nextStepName) : self
    {
        return new self(
            instanceId        : $this->instanceId,
            definitionName    : $this->definitionName,
            correlationId     : $this->correlationId,
            currentStepName   : $nextStepName,
            status            : $nextStepName === null ? 'completed' : 'running',
            completedStepNames: [...$this->completedStepNames, $stepName],
            version           : $this->version + 1
        );
    }

    public function recordFailedStep() : self
    {
        return new self(
            instanceId        : $this->instanceId,
            definitionName    : $this->definitionName,
            correlationId     : $this->correlationId,
            currentStepName   : $this->currentStepName,
            status            : 'failed',
            completedStepNames: $this->completedStepNames,
            version           : $this->version + 1
        );
    }
}
