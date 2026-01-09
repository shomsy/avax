<?php

declare(strict_types=1);
namespace Avax\Container\Core\Kernel\Contracts;

use Avax\Container\Core\Kernel\Events\StepFailed;
use Avax\Container\Core\Kernel\Events\StepStarted;
use Avax\Container\Core\Kernel\Events\StepSucceeded;

/**
 * Step Telemetry Interface - Observer Pattern for Step Events
 *
 * Defines the contract for collecting telemetry data from pipeline step execution.
 * Implementations can collect metrics, logs, traces, or other observability data.
 *
 * @see docs_md/Core/Kernel/Contracts/StepTelemetry.md#quick-summary
 */
interface StepTelemetry
{
    /**
     * Handle step started event.
     *
     * Called when a pipeline step begins execution.
     * Useful for timing, logging, or tracing step initiation.
     *
     * @param StepStarted $event The step started event with context
     * @return void
     * @see docs_md/Core/Kernel/Contracts/StepTelemetry.md#method-onstepstarted
     */
    public function onStepStarted(StepStarted $event): void;

    /**
     * Handle step succeeded event.
     *
     * Called when a pipeline step completes successfully.
     * Useful for metrics collection, success logging, or performance tracking.
     *
     * @param StepSucceeded $event The step succeeded event with results
     * @return void
     * @see docs_md/Core/Kernel/Contracts/StepTelemetry.md#method-onstepsucceeded
     */
    public function onStepSucceeded(StepSucceeded $event): void;

    /**
     * Handle step failed event.
     *
     * Called when a pipeline step throws an exception.
     * Critical for error tracking, alerting, and debugging failed resolutions.
     *
     * @param StepFailed $event The step failed event with error details
     * @return void
     * @see docs_md/Core/Kernel/Contracts/StepTelemetry.md#method-onstepfailed
     */
    public function onStepFailed(StepFailed $event): void;
}
