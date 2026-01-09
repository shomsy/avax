<?php

declare(strict_types=1);
namespace Avax\Container\Observe\Metrics\Sink;

/**
 * Null telemetry sink that performs no recording when telemetry is disabled.
 *
 * @see docs_md/Observe/Metrics/Sink/NullTelemetrySink.md#quick-summary
 */
class NullTelemetrySink implements TelemetrySinkInterface
{
    /**
     * {@inheritDoc}
     *
     * @see docs_md/Observe/Metrics/Sink/NullTelemetrySink.md#method-record
     */
    public function record(string $abstract, float $durationMs, string $strategy) : void
    {
        // No-op
    }
}
