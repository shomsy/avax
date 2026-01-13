<?php

declare(strict_types=1);

namespace Avax\Container\Observe\Metrics\Sink;

/**
 * Contract for recording telemetry data from the container.
 *
 * @see docs/Observe/Metrics/Sink/TelemetrySinkInterface.md#quick-summary
 */
interface TelemetrySinkInterface
{
    /**
     * Records a telemetry event.
     *
     * @param string $abstract   Service identifier/abstract being resolved
     * @param float  $durationMs Resolution duration in milliseconds
     * @param string $strategy   Resolution strategy label (e.g., singleton/scoped/transient)
     *
     * @see docs/Observe/Metrics/Sink/TelemetrySinkInterface.md#method-record
     */
    public function record(string $abstract, float $durationMs, string $strategy) : void;
}
