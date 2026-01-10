<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Kernel\Steps;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\Events\StepStarted;
use Avax\Container\Core\Kernel\Events\StepSucceeded;
use Avax\Container\Core\Kernel\Steps\CollectDiagnosticsStep;
use Avax\Container\Core\Kernel\StepTelemetryCollector;
use PHPUnit\Framework\TestCase;

/**
 * Test coverage for CollectDiagnosticsStep.
 */
final class CollectDiagnosticsStepTest extends TestCase
{
    public function testItCollectsDiagnosticsAndStoresInContext(): void
    {
        $serviceId = 'test.service';
        $traceId   = 'trace-123';
        $telemetry = new StepTelemetryCollector();
        $step      = new CollectDiagnosticsStep($telemetry);

        // Pass traceId in constructor
        $context   = new KernelContext(
            serviceId: $serviceId,
            traceId: $traceId
        );

        // Simulate some step telemetry
        $telemetry->onStepStarted(new StepStarted(
            stepClass: 'SomeStep',
            timestamp: 1000.0,
            serviceId: $serviceId,
            traceId: $traceId
        ));

        $telemetry->onStepSucceeded(new StepSucceeded(
            stepClass: 'SomeStep',
            startedAt: 1000.0,
            endedAt: 1000.05,
            duration: 0.05,
            serviceId: $serviceId,
            traceId: $traceId
        ));

        // Invoke the diagnostics step
        $step($context);

        // Verify report metadata
        $report = $context->getMeta('diagnostics', 'report');
        $this->assertIsArray($report);
        $this->assertEquals($serviceId, $context->serviceId);
        $this->assertEquals(1, $report['steps_count']);
        $this->assertEquals(50.0, $report['duration_ms']); // 0.05s = 50ms

        // Verify detailed step metadata
        $steps = $context->getMeta('diagnostics', 'steps');
        $this->assertArrayHasKey('SomeStep', $steps);
        $this->assertEquals(50.0, $steps['SomeStep']['duration_ms']);
        $this->assertEquals('success', $steps['SomeStep']['status']);
    }
}
