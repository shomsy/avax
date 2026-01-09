<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel;

use Avax\Container\Core\Kernel\Contracts\StepTelemetry;
use Avax\Container\Core\Kernel\Events\StepFailed;
use Avax\Container\Core\Kernel\Events\StepStarted;
use Avax\Container\Core\Kernel\Events\StepSucceeded;

/**
 * Step Telemetry Collector - Collects Step Execution Metrics
 *
 * Implements the Observer pattern to collect telemetry data from pipeline step execution.
 * Stores metrics in a structured format for diagnostics and monitoring, enabling performance analysis and debugging of resolution processes.
 *
 * @see docs_md/Core/Kernel/StepTelemetryCollector.md#quick-summary
 */
final class StepTelemetryCollector implements StepTelemetry
{
    /** @var array<string, array> Collected step metrics indexed by step class */
    private array $stepMetrics = [];

    /**
     * Handle step started event.
     *
     * Records the initiation of a pipeline step execution, capturing timing and context information
     * for performance monitoring and diagnostics.
     *
     * @param StepStarted $event The step started event with execution details
     * @return void
     * @see docs_md/Core/Kernel/StepTelemetryCollector.md#method-onStepStarted
     */
    public function onStepStarted(StepStarted $event) : void
    {
        $traceId                                                           = $event->traceId ?? 'default';
        $this->stepMetrics[$traceId][$event->serviceId][$event->stepClass] = [
            'started_at' => $event->timestamp,
            'service_id' => $event->serviceId,
            'status'     => 'running'
        ];
    }

    /**
     * Handle step succeeded event.
     *
     * Records the successful completion of a pipeline step, updating timing and status information.
     *
     * @param StepSucceeded $event The step succeeded event with completion details
     * @return void
     * @see docs_md/Core/Kernel/StepTelemetryCollector.md#method-onStepSucceeded
     */
    public function onStepSucceeded(StepSucceeded $event) : void
    {
        $traceId = $event->traceId ?? 'default';
        if (isset($this->stepMetrics[$traceId][$event->serviceId][$event->stepClass])) {
            $this->stepMetrics[$traceId][$event->serviceId][$event->stepClass]['ended_at'] = $event->endedAt;
            $this->stepMetrics[$traceId][$event->serviceId][$event->stepClass]['duration'] = $event->duration;
            $this->stepMetrics[$traceId][$event->serviceId][$event->stepClass]['status']   = 'success';
        }
    }

    /**
     * Handle step failed event.
     *
     * Records the failure of a pipeline step, capturing timing, duration, and error information
     * for debugging and monitoring purposes.
     *
     * @param StepFailed $event The step failed event with failure details
     * @return void
     * @see docs_md/Core/Kernel/StepTelemetryCollector.md#method-onStepFailed
     */
    public function onStepFailed(StepFailed $event) : void
    {
        $traceId = $event->traceId ?? 'default';
        if (isset($this->stepMetrics[$traceId][$event->serviceId][$event->stepClass])) {
            $this->stepMetrics[$traceId][$event->serviceId][$event->stepClass]['ended_at'] = $event->endedAt;
            $this->stepMetrics[$traceId][$event->serviceId][$event->stepClass]['duration'] = $event->duration;
            $this->stepMetrics[$traceId][$event->serviceId][$event->stepClass]['status']   = 'failed';
            $this->stepMetrics[$traceId][$event->serviceId][$event->stepClass]['error']    = [
                'message' => $event->exception->getMessage(),
                'type'    => $event->exception::class
            ];
        }
    }

    /**
     * Get collected step metrics.
     *
     * Retrieves stored telemetry data for pipeline step executions, optionally filtered by trace ID.
     *
     * @param string|null $traceId Optional trace identifier to filter results
     * @return array Step metrics data organized by trace, service, and step
     * @see docs_md/Core/Kernel/StepTelemetryCollector.md#method-getStepMetrics
     */
    public function getStepMetrics(string|null $traceId = null) : array
    {
        if ($traceId !== null) {
            return $this->stepMetrics[$traceId] ?? [];
        }

        return $this->stepMetrics;
    }

    /**
     * Return metrics as a serializable array.
     *
     * Provides a structured, serializable representation of all collected telemetry data
     * including traces and summary statistics.
     *
     * @return array Structured telemetry data with traces and summary information
     * @see docs_md/Core/Kernel/StepTelemetryCollector.md#method-asArray
     */
    public function asArray() : array
    {
        return [
            'traces'  => $this->stepMetrics,
            'summary' => [
                'total_duration' => $this->getTotalDuration(),
                'start_time'     => $this->getPipelineStartTime(),
            ]
        ];
    }

    /**
     * Calculate total pipeline duration.
     *
     * Computes the total time span from the earliest step start to the latest step end
     * across all collected telemetry data.
     *
     * @return float Total duration in seconds, or 0.0 if no data available
     * @see docs_md/Core/Kernel/StepTelemetryCollector.md#method-getTotalDuration
     */
    public function getTotalDuration() : float
    {
        $flattened = [];
        foreach ($this->stepMetrics as $traceMetrics) {
            foreach ($traceMetrics as $serviceMetrics) {
                foreach ($serviceMetrics as $metrics) {
                    $flattened[] = $metrics;
                }
            }
        }

        if (empty($flattened)) {
            return 0.0;
        }

        $starts = array_column($flattened, 'started_at');
        $ends   = array_filter(array_column($flattened, 'ended_at'));

        if (empty($starts) || empty($ends)) {
            return 0.0;
        }

        return max($ends) - min($starts);
    }

    /**
     * Get pipeline start time.
     *
     * Determines the earliest recorded step start time across all telemetry data,
     * useful for calculating relative timings and pipeline analysis.
     *
     * @return float|null Earliest step start time, or null if no data available
     * @see docs_md/Core/Kernel/StepTelemetryCollector.md#method-getPipelineStartTime
     */
    public function getPipelineStartTime() : float|null
    {
        $starts = [];
        foreach ($this->stepMetrics as $traceMetrics) {
            foreach ($traceMetrics as $serviceMetrics) {
                foreach ($serviceMetrics as $metrics) {
                    $starts[] = $metrics['started_at'];
                }
            }
        }

        return empty($starts) ? null : min($starts);
    }
}
