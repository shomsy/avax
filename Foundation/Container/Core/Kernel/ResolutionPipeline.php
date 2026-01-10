<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\Contracts\KernelStep;
use Avax\Container\Core\Kernel\Contracts\StepTelemetry;
use Avax\Container\Core\Kernel\Events\StepFailed;
use Avax\Container\Core\Kernel\Events\StepStarted;
use Avax\Container\Core\Kernel\Events\StepSucceeded;
use Avax\Container\Features\Core\Exceptions\ContainerException;
use Throwable;

/**
 * Resolution Pipeline - Sequential Execution Engine
 *
 * Orchestrates the execution of multiple KernelSteps to resolve a service.
 * Manages the flow, telemetry via StepTelemetry observer, and error handling for the entire resolution lifecycle,
 * ensuring each step has the opportunity to transform the resolution context.
 *
 * @see docs/Core/Kernel/ResolutionPipeline.md#quick-summary
 * @internal This class is not intended for public usage.
 */
final readonly class ResolutionPipeline
{
    /** @var KernelStep[] Sequence of steps to execute */
    private array $steps;

    /**
     * Initialize the pipeline with steps and telemetry.
     *
     * @param array               $steps     List of KernelStep implementations
     * @param StepTelemetry|null  $telemetry Optional telemetry observer
     * @throws ContainerException If a step does not implement KernelStep or if steps are empty
     * @see docs/Core/Kernel/ResolutionPipeline.md#method-__construct
     */
    public function __construct(
        array               $steps,
        private StepTelemetry|null $telemetry = null
    ) {
        if (empty($steps)) {
            throw new ContainerException(message: 'Resolution pipeline cannot be empty');
        }

        foreach ($steps as $index => $step) {
            if (! $step instanceof KernelStep) {
                throw new ContainerException(message: sprintf('Step at index %d must implement KernelStep interface, %s given.', $index, is_object($step) ? $step::class : gettype($step)));
            }
        }

        $this->steps = $steps;
    }

    /**
     * Execute the resolution pipeline.
     *
     * @param KernelContext $context The resolution state to process.
     * @return void
     * @throws ContainerException If execution fails catastrophically.
     * @throws Throwable From any individual step.
     * @see docs/Core/Kernel/ResolutionPipeline.md#method-run
     */
    public function run(KernelContext $context): void
    {
        $pipelineStartTime = microtime(as_float: true);

        foreach ($this->steps as $index => $step) {
            $stepStartTime = microtime(as_float: true);

            // Notify telemetry about step initiation
            $this->telemetry?->onStepStarted(new StepStarted(
                stepClass: $step::class,
                timestamp: $stepStartTime,
                serviceId: $context->serviceId,
                traceId: $context->traceId
            ));

            try {
                $step(context: $context);

                $stepEndTime = microtime(as_float: true);
                $duration    = round(($stepEndTime - $stepStartTime) * 1000, 4);

                $context->setMetaOnce(namespace: 'telemetry', key: 'step_timings', value: []);
                $timings           = $context->getMeta(namespace: 'telemetry', key: 'step_timings');
                $timings[$step::class] = $duration;
                $context->putMeta(namespace: 'telemetry', key: 'step_timings', value: $timings);

                // Notify telemetry about successful step completion
                $this->telemetry?->onStepSucceeded(new StepSucceeded(
                    stepClass: $step::class,
                    startedAt: $stepStartTime,
                    endedAt: $stepEndTime,
                    duration: $duration / 1000,
                    serviceId: $context->serviceId,
                    traceId: $context->traceId
                ));
            } catch (Throwable $e) {
                $stepEndTime = microtime(as_float: true);
                $duration    = round(($stepEndTime - $stepStartTime) * 1000, 4);

                // Notify telemetry about step failure
                $this->telemetry?->onStepFailed(new StepFailed(
                    stepClass: $step::class,
                    startedAt: $stepStartTime,
                    endedAt: $stepEndTime,
                    duration: $duration / 1000,
                    serviceId: $context->serviceId,
                    exception: $e,
                    traceId: $context->traceId
                ));

                // Re-wrap non-container exceptions if necessary, or just throw if it's already a ContainerException
                // But the test expects "Resolution pipeline failed at step X"
                if (!($e instanceof ContainerException)) {
                    throw new ContainerException(
                        message: sprintf('Resolution pipeline failed at step %d: %s', $index + 1, $e->getMessage()),
                        previous: $e
                    );
                }

                throw $e;
            }
        }

        $pipelineEndTime = microtime(as_float: true);
        $totalDuration   = round(($pipelineEndTime - $pipelineStartTime) * 1000, 4);

        $context->setMeta(namespace: 'telemetry', key: 'duration_ms', value: $totalDuration);
    }

    /**
     * Get the number of steps in the pipeline.
     *
     * @return int
     * @see docs/Core/Kernel/ResolutionPipeline.md#method-count
     */
    public function count(): int
    {
        return count(value: $this->steps);
    }

    /**
     * Get a specific step by index.
     *
     * @param int $index
     * @return KernelStep
     * @throws ContainerException If index is out of bounds
     * @see docs/Core/Kernel/ResolutionPipeline.md#method-getstep
     */
    public function getStep(int $index): KernelStep
    {
        if (! isset($this->steps[$index])) {
            throw new ContainerException(message: sprintf('Step index %d is out of bounds', $index));
        }

        return $this->steps[$index];
    }

    /**
     * Create a new pipeline with an additional step at the end.
     *
     * @param KernelStep $step
     * @return self
     * @see docs/Core/Kernel/ResolutionPipeline.md#method-withstep
     */
    public function withStep(KernelStep $step): self
    {
        $steps = $this->steps;
        $steps[] = $step;

        return new self(steps: $steps, telemetry: $this->telemetry);
    }

    /**
     * Create a new pipeline with an additional step at the beginning.
     *
     * @param KernelStep $step
     * @return self
     * @see docs/Core/Kernel/ResolutionPipeline.md#method-withstepfirst
     */
    public function withStepFirst(KernelStep $step): self
    {
        $steps = $this->steps;
        array_unshift($steps, $step);

        return new self(steps: $steps, telemetry: $this->telemetry);
    }

    /**
     * Get all steps in the pipeline.
     *
     * @return KernelStep[]
     * @see docs/Core/Kernel/ResolutionPipeline.md#method-getsteps
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    /**
     * String representation of the pipeline sequence.
     *
     * @return string
     * @see docs/Core/Kernel/ResolutionPipeline.md#method-__tostring
     */
    public function __toString(): string
    {
        $stepNames = array_map(static fn($s) => $s::class, $this->steps);

        return 'ResolutionPipeline[' . implode(separator: ' -> ', array: $stepNames) . ']';
    }
}
