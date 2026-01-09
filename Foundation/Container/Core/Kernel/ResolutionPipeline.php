<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\Contracts\KernelStep;
use Avax\Container\Core\Kernel\Contracts\TerminalKernelStep;
use Avax\Container\Features\Core\Exceptions\ContainerException;
use Throwable;

/**
 * Resolution Pipeline - Orchestrates Service Resolution Steps
 *
 * Executes a sequence of resolution steps in order, passing a shared context
 * through each step. Each step performs a specific operation on the context,
 * either transforming the instance or enriching metadata.
 *
 * The pipeline follows the Pipeline pattern, allowing for modular, testable
 * resolution logic with clear separation of concerns. Steps can be reordered,
 * replaced, or conditionally executed based on configuration.
 *
 * Pipeline execution is atomic - if any step fails, the entire resolution fails.
 * This ensures consistency and allows for proper error handling and rollback.
 *
 * @package Avax\Container\Core\Kernel
 * @see docs_md/Core/Kernel/ResolutionPipeline.md#quick-summary
 */
final readonly class ResolutionPipeline
{
    /** @var KernelStep[] Array of steps to execute in order */
    private array $steps;

    /**
     * Create a new resolution pipeline.
     *
     * @param KernelStep[]                 $steps     Steps to execute in sequence
     * @param Contracts\StepTelemetry|null $telemetry Optional telemetry collector
     *
     * @throws ContainerException If steps array is empty or contains invalid steps
     */
    public function __construct(
        array                                $steps,
        private Contracts\StepTelemetry|null $telemetry = null
    )
    {
        if (empty($steps)) {
            throw new ContainerException(message: 'Resolution pipeline cannot be empty');
        }

        foreach ($steps as $index => $step) {
            if (! $step instanceof KernelStep) {
                $type = is_object($step) ? get_class($step) : gettype($step);
                throw new ContainerException(
                    message: sprintf('Step at index %d must implement KernelStep interface, got %s', $index, $type)
                );
            }
        }

        $this->steps = array_values($steps); // Re-index array
    }

    /**
     * Execute the resolution pipeline.
     *
     * Runs all steps in sequence, passing the context through each step with comprehensive
     * telemetry collection and error handling. Each step transforms the context until
     * resolution is complete or a terminal step stops execution.
     *
     * @param KernelContext $context Initial resolution context containing service request details
     * @return void
     * @throws ContainerException If pipeline configuration is invalid
     * @throws Throwable If any step fails during execution (wrapped with pipeline context)
     * @see docs_md/Core/Kernel/ResolutionPipeline.md#method-run
     */
    public function run(KernelContext $context) : void
    {
        $pipelineStartTime = microtime(true);
        $context->setMeta('pipeline', 'start', $pipelineStartTime);

        try {
            foreach ($this->steps as $stepIndex => $step) {
                $stepClass     = get_class($step);
                $stepStartTime = microtime(true);

                // Emit step started event
                $this->telemetry?->onStepStarted(new Events\StepStarted(
                    stepClass: $stepClass,
                    timestamp: $stepStartTime,
                    serviceId: $context->serviceId,
                    traceId  : $context->traceId
                ));

                try {
                    $step(context: $context);

                    $stepEndTime  = microtime(true);
                    $stepDuration = $stepEndTime - $stepStartTime;

                    // Emit step succeeded event
                    $this->telemetry?->onStepSucceeded(new Events\StepSucceeded(
                        stepClass: $stepClass,
                        startedAt: $stepStartTime,
                        endedAt  : $stepEndTime,
                        duration : $stepDuration,
                        serviceId: $context->serviceId,
                        traceId  : $context->traceId
                    ));

                    // If this is a terminal step and context is resolved, stop the pipeline
                    if ($step instanceof TerminalKernelStep && $context->isResolved()) {
                        break;
                    }
                } catch (Throwable $e) {
                    $stepEndTime  = microtime(true);
                    $stepDuration = $stepEndTime - $stepStartTime;

                    // Emit step failed event
                    $this->telemetry?->onStepFailed(new Events\StepFailed(
                        stepClass: $stepClass,
                        startedAt: $stepStartTime,
                        endedAt  : $stepEndTime,
                        duration : $stepDuration,
                        serviceId: $context->serviceId,
                        exception: $e,
                        traceId  : $context->traceId
                    ));

                    // Add pipeline context to the exception
                    $message = sprintf(
                        'Resolution pipeline failed at step %d (%s) for service "%s": %s',
                        $stepIndex + 1,
                        $stepClass,
                        $context->serviceId,
                        $e->getMessage()
                    );

                    throw new ContainerException(message: $message, code: 0, previous: $e);
                }
            }
        } finally {
            // Finalize pipeline metadata even on failure
            $pipelineEndTime = microtime(true);
            $context->setMeta('pipeline', 'completed_at', $pipelineEndTime);
            $context->setMeta('pipeline', 'duration_ms', max(1, ($pipelineEndTime - $pipelineStartTime) * 1000));
            $context->setMeta('pipeline', 'status', $context->isResolved() ? 'success' : 'failed');

            if ($this->telemetry instanceof StepTelemetryCollector) {
                $context->setMeta('telemetry', 'step_count', count($this->steps));
            }
        }
    }

    /**
     * Get the number of steps in the pipeline.
     *
     * Returns the total count of resolution steps configured in this pipeline,
     * useful for validation, debugging, and pipeline analysis.
     *
     * @return int Number of steps in the pipeline
     * @see docs_md/Core/Kernel/ResolutionPipeline.md#method-count
     */
    public function count() : int
    {
        return count($this->steps);
    }

    /**
     * Get a specific step by index.
     *
     * Retrieves a step from the pipeline by its zero-based index position,
     * enabling inspection and testing of individual pipeline components.
     *
     * @param int $index Step index (0-based)
     * @return KernelStep The step at the specified index
     * @throws ContainerException If index is out of bounds
     * @see docs_md/Core/Kernel/ResolutionPipeline.md#method-getStep
     */
    public function getStep(int $index) : KernelStep
    {
        if (! isset($this->steps[$index])) {
            throw new ContainerException(
                message: sprintf('Step index %d is out of bounds (pipeline has %d steps)', $index, count($this->steps))
            );
        }

        return $this->steps[$index];
    }

    /**
     * Create a new pipeline with an additional step appended.
     *
     * Returns an immutable copy of this pipeline with the specified step added to the end,
     * enabling fluent pipeline construction without modifying the original.
     *
     * @param KernelStep $step Step to append to the pipeline
     * @return self New pipeline with the additional step
     * @see docs_md/Core/Kernel/ResolutionPipeline.md#method-withStep
     */
    public function withStep(KernelStep $step) : self
    {
        return new self(steps: [...$this->steps, $step]);
    }

    /**
     * Create a new pipeline with a step prepended.
     *
     * Returns an immutable copy of this pipeline with the specified step added to the beginning,
     * useful for adding high-priority steps like caching or validation.
     *
     * @param KernelStep $step Step to prepend to the pipeline
     * @return self New pipeline with the step at the beginning
     * @see docs_md/Core/Kernel/ResolutionPipeline.md#method-withStepFirst
     */
    public function withStepFirst(KernelStep $step) : self
    {
        return new self(steps: [$step, ...$this->steps]);
    }

    /**
     * Get string representation for debugging.
     *
     * Provides a human-readable summary of the pipeline structure showing
     * the step count and sequence of step classes for debugging purposes.
     *
     * @return string Pipeline summary with step count and sequence
     * @see docs_md/Core/Kernel/ResolutionPipeline.md#method-__toString
     */
    public function __toString() : string
    {
        $stepClasses = array_map(static fn($step) => get_class($step), $this->steps);

        return sprintf(
            'ResolutionPipeline{steps=%d, sequence=%s}',
            count($this->steps),
            implode(' -> ', $stepClasses)
        );
    }
}
