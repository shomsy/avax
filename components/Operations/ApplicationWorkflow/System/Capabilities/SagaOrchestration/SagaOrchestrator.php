<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaOrchestration;

use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Compensation\CompensationExecutor;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Idempotency\IdempotencyKey;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Idempotency\IdempotencyStore;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaState\SagaState;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaState\SagaStep;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaStore\SagaStoreInterface;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\StepRunner\StepRunner;
use Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\SagaResult;
use Throwable;

/**
 * Orchestrates saga execution, handling step sequencing, context management,
 * idempotency, and compensation on failure.
 */
final readonly class SagaOrchestrator
{
    public function __construct(
        private StepRunner           $stepRunner,
        private CompensationExecutor $compensationExecutor,
        private SagaStoreInterface   $sagaStore,
    ) {}

    /**
     * @param array<SagaStep>      $steps
     * @param list<string>         $completedStepNames
     * @param array<string, mixed> $context
     * @param array<string, mixed> $stepResults
     */
    public function compensate(
        string   $sagaId,
        array    $steps,
        array    $completedStepNames,
        array    $context,
        array    $stepResults,
        callable $saveStateCallback
    ) : SagaResult
    {
        $compensationResult = $this->compensationExecutor->execute(
            steps         : $steps,
            completedSteps: $completedStepNames,
            context       : $context,
        );

        $finalState = $compensationResult->success ? SagaState::Compensated : SagaState::Failed;
        $saveStateCallback($finalState, $context, $stepResults, $compensationResult->failureReason);

        return new SagaResult(
            success       : $compensationResult->success,
            sagaId        : $sagaId,
            data          : $context,
            completedSteps: $completedStepNames,
            stepResults   : $stepResults,
            failureReason : $compensationResult->failureReason,
        );
    }

    /**
     * @param array<SagaStep>      $steps
     * @param array<string, mixed> $context
     */
    public function execute(
        string   $sagaId,
        array    $steps,
        array    $context,
        callable $saveStateCallback
    ) : SagaResult
    {
        $stepResults    = [];
        $currentContext = $context;

        try {
            foreach ($steps as $index => $step) {
                $idempotencyKey = IdempotencyKey::generate($sagaId, $step->name, (string) $index);

                $result = $this->stepRunner->execute(
                    context       : $currentContext,
                    idempotencyKey: $idempotencyKey,
                    step          : $step,
                );

                $stepResults[$step->name] = $result;

                if (is_array($result)) {
                    $currentContext = array_merge($currentContext, $result);
                }
            }

            $saveStateCallback(SagaState::Completed, $currentContext, $stepResults);

            return new SagaResult(
                success       : true,
                sagaId        : $sagaId,
                data          : $currentContext,
                completedSteps: array_keys($stepResults),
                stepResults   : $stepResults,
            );
        } catch (Throwable $throwable) {
            return $this->handleFailure(
                sagaId            : $sagaId,
                steps             : $steps,
                completedStepNames: array_keys($stepResults),
                context           : $currentContext,
                stepResults       : $stepResults,
                throwable         : $throwable,
                saveStateCallback : $saveStateCallback,
            );
        }
    }

    /**
     * @param array<SagaStep>      $steps
     * @param list<string>         $completedStepNames
     * @param array<string, mixed> $context
     * @param array<string, mixed> $stepResults
     */
    private function handleFailure(
        string    $sagaId,
        array     $steps,
        array     $completedStepNames,
        array     $context,
        array     $stepResults,
        Throwable $throwable,
        callable  $saveStateCallback
    ) : SagaResult
    {
        $compensationResult = $this->compensationExecutor->execute(
            steps         : $steps,
            completedSteps: $completedStepNames,
            context       : $context,
        );

        $finalState = $compensationResult->success ? SagaState::Compensated : SagaState::Failed;
        $saveStateCallback($finalState, $context, $stepResults, $throwable->getMessage());

        return new SagaResult(
            success       : false,
            sagaId        : $sagaId,
            data          : $context,
            completedSteps: $completedStepNames,
            stepResults   : $stepResults,
            failureReason : $throwable->getMessage(),
        );
    }
}
