<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Steps;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\Contracts\KernelStep;
use Avax\Container\Core\Kernel\StepTelemetryCollector;

/**
 * Collect Diagnostics Step - Metrics and Telemetry Collection
 *
 * @see docs_md/Core/Kernel/Steps/CollectDiagnosticsStep.md#quick-summary
 */
final readonly class CollectDiagnosticsStep implements KernelStep
{
    /**
     * @param StepTelemetryCollector $telemetryCollector Collector providing step timing metrics
     */
    public function __construct(
        private StepTelemetryCollector $telemetryCollector
    ) {}

    /**
     * Collect diagnostic metrics and store them on the context.
     *
     * @param KernelContext $context
     * @return void
     * @see docs_md/Core/Kernel/Steps/CollectDiagnosticsStep.md#method-__invoke
     */
    public function __invoke(KernelContext $context): void
    {
        $stepMetrics = $this->telemetryCollector->getStepMetrics(traceId: $context->traceId);
        $totalDuration = $this->telemetryCollector->getTotalDuration();
        $pipelineStart = $this->telemetryCollector->getPipelineStartTime();

        // Store diagnostics in metadata
        $context->setMeta('diagnostics', 'service_id', $context->serviceId);
        $context->setMeta('diagnostics', 'duration_ms', max(1, $totalDuration * 1000));

        // Handle instance type carefully (could be literal)
        $instanceType = match (true) {
            $context->getInstance() === null => 'null',
            is_object($context->getInstance()) => get_class($context->getInstance()),
            default => gettype($context->getInstance())
        };

        $context->setMeta('diagnostics', 'instance_type', $instanceType);
        $context->setMeta('diagnostics', 'step_timings', $this->formatStepTimings($stepMetrics, $context->serviceId));
        $context->setMeta('diagnostics', 'timestamp', time());
        $context->setMeta('diagnostics', 'success', $context->isResolved());

        // Add final metadata
        $context->setMeta('diagnostics', 'collected', true);
        $context->setMeta('pipeline', 'completed_at', microtime(true));
        $context->setMeta('pipeline', 'started_at', $pipelineStart);
    }

    /**
     * Format raw step metrics into a consistent structure.
     *
     * @param array  $stepMetrics Raw metrics from telemetry collector
     * @param string $serviceId   Service identifier
     * @return array
     * @see docs_md/Core/Kernel/Steps/CollectDiagnosticsStep.md#method-formatsteptimings
     */
    private function formatStepTimings(array $stepMetrics, string $serviceId): array
    {
        $formatted = [];
        $source = $stepMetrics[$serviceId] ?? $stepMetrics;

        foreach ($source as $stepClass => $metrics) {
            $formatted[$stepClass] = [
                'duration_ms' => ($metrics['duration'] ?? 0) * 1000,
                'status' => $metrics['status'] ?? 'unknown',
                'started_at' => $metrics['started_at'] ?? null,
                'ended_at' => $metrics['ended_at'] ?? null,
                'error' => $metrics['error'] ?? null,
            ];
        }

        return $formatted;
    }
}
