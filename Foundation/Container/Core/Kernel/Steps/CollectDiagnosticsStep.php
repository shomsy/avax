<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Steps;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\Contracts\KernelStep;
use Avax\Container\Core\Kernel\StepTelemetryRecorder;

/**
 * Collect Diagnostics Step - Final Pipeline Telemetry
 *
 * This final step records high-level diagnostic data about the resolution,
 * including instance types and pipeline completion timestamps, providing
 * insights into the container's health and performance.
 *
 * @see     docs/Core/Kernel/Steps/CollectDiagnosticsStep.md#quick-summary
 */
final readonly class CollectDiagnosticsStep implements KernelStep
{
    /**
     * @param StepTelemetryRecorder $telemetry Collector for recording pipeline metrics.
     *
     * @see docs/Core/Kernel/Steps/CollectDiagnosticsStep.md#method-__construct
     */
    public function __construct(
        private StepTelemetryRecorder $telemetry
    ) {}

    /**
     * Finalize resolution diagnostics and record pipeline completion.
     *
     * @param KernelContext $context The resolution context.
     *
     * @see docs/Core/Kernel/Steps/CollectDiagnosticsStep.md#method-__invoke
     */
    public function __invoke(KernelContext $context) : void
    {
        $traceId  = $context->traceId;
        $instance = $context->getInstance();

        // Retrieve raw metrics from collector
        $rawMetrics = $this->telemetry->getStepMetrics(traceId: $traceId);
        $totalTime  = $this->telemetry->getTotalDuration(); // Note: This might be global, but for single request it works

        // Format step timings for diagnostics
        $stepTimings = $this->formatStepTimings(stepMetrics: $rawMetrics, serviceId: $context->serviceId);

        // Record high-level report card
        $context->setMeta(namespace: 'diagnostics', key: 'report', value: [
            'resolved'      => $context->isResolved(),
            'instance_type' => match (true) {
                $instance === null   => 'null',
                is_object($instance) => $instance::class,
                default              => gettype(value: $instance)
            },
            'depth'         => $context->depth,
            'duration_ms'   => round(num: $totalTime * 1000, precision: 4),
            'steps_count'   => count(value: $stepTimings),
            'path'          => $context->getPath(),
        ]);

        // Detailed step breakdown
        $context->setMeta(namespace: 'diagnostics', key: 'steps', value: $stepTimings);

        // Record completion timestamp
        $context->setMeta(namespace: 'pipeline', key: 'completed_at', value: microtime(as_float: true));
    }

    /**
     * Normalize step metrics to a consistent shape.
     *
     * @param array  $stepMetrics Raw telemetry collector metrics.
     * @param string $serviceId   Current service ID to select per-service metrics.
     *
     * @return array Normalized step timing data.
     *
     * @see docs/Core/Kernel/Steps/CollectDiagnosticsStep.md#method-formatsteptimings
     */
    private function formatStepTimings(array $stepMetrics, string $serviceId) : array
    {
        // StepTelemetryRecorder stores as $stepMetrics[$traceId][$serviceId][$stepClass]
        // But getStepMetrics($traceId) returns $stepMetrics[$traceId]
        $serviceMetrics = $stepMetrics[$serviceId] ?? [];
        $formatted      = [];

        foreach ($serviceMetrics as $stepClass => $metrics) {
            $formatted[$stepClass] = [
                'duration_ms' => isset($metrics['duration']) ? round(num: $metrics['duration'] * 1000, precision: 4) : 0,
                'status'      => $metrics['status'] ?? 'unknown',
                'started_at'  => $metrics['started_at'] ?? 0,
                'ended_at'    => $metrics['ended_at'] ?? 0,
                'error'       => $metrics['error'] ?? null,
            ];
        }

        return $formatted;
    }
}
