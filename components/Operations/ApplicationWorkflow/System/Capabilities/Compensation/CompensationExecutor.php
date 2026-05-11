<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Compensation;

use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaState\SagaStep;
use Throwable;

/**
 * Result of a compensation execution.
 */
final readonly class CompensationResult
{
    public function __construct(
        public bool    $success,
        public array   $compensatedSteps,
        public array   $failedSteps,
        public string|null $failureReason = null,
    ) {}
}

/**
 * Executes compensation steps in reverse order of completed steps.
 */
final class CompensationExecutor
{
    /**
     * Run compensation for all completed steps in reverse order.
     *
     * @param array<SagaStep>    $steps          All saga steps
     * @param array<int, string> $completedSteps Names of completed steps (in order)
     * @param mixed              $context        The saga context
     *
     * @return CompensationResult The result of compensation execution
     */
    public function execute(
        array $steps,
        array $completedSteps,
        mixed $context,
    ) : CompensationResult
    {
        if ($completedSteps === []) {
            return new CompensationResult(
                success         : true,
                compensatedSteps: [],
                failedSteps     : [],
            );
        }

        // Build a map of step name to step object
        $stepMap = [];
        foreach ($steps as $step) {
            $stepMap[$step->name] = $step;
        }

        $compensated   = [];
        $failed        = [];
        $failureReason = null;

        // Run compensation in REVERSE order of completed steps
        $reversedCompleted = array_reverse($completedSteps);

        foreach ($reversedCompleted as $stepName) {
            $step = $stepMap[$stepName] ?? null;

            if ($step === null) {
                continue;
            }

            if (! $step->hasCompensation()) {
                $compensated[] = $stepName;

                continue;
            }

            try {
                $step->compensate($context);
                $compensated[] = $stepName;
            } catch (Throwable $e) {
                $failed[]      = $stepName;
                $failureReason = sprintf(
                    'Compensation failed for step "%s": %s',
                    $stepName,
                    $e->getMessage(),
                );
                // Continue compensating other steps even if one fails
            }
        }

        return new CompensationResult(
            success         : $failed === [],
            compensatedSteps: $compensated,
            failedSteps     : $failed,
            failureReason   : $failureReason,
        );
    }
}
