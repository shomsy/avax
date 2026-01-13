<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Kernel\Steps;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\Events\StepStarted;
use Avax\Container\Core\Kernel\Events\StepSucceeded;
use Avax\Container\Core\Kernel\Steps\CollectDiagnosticsStep;
use Avax\Container\Core\Kernel\StepTelemetryRecorder;
use PHPUnit\Framework\TestCase;

/**
 * Test coverage for CollectDiagnosticsStep.
 */
final class CollectDiagnosticsStepTest extends TestCase
{
    public function test_it_collects_diagnostics_and_stores_in_context() : void
    {
        $serviceId = 'test.service';
        $traceId   = 'trace-123';
        $telemetry = new StepTelemetryRecorder;
        $step      = new CollectDiagnosticsStep(telemetry: $telemetry);

        // Pass traceId in constructor
        $context = new KernelContext(
            serviceId: $serviceId,
            traceId  : $traceId
        );

        // Simulate some step telemetry
        $telemetry->onStepStarted(event: new StepStarted(
            stepClass: 'SomeStep',
            timestamp: 1000.0,
            serviceId: $serviceId,
            traceId  : $traceId
        ));

        $telemetry->onStepSucceeded(event: new StepSucceeded(
            stepClass: 'SomeStep',
            startedAt: 1000.0,
            endedAt  : 1000.05,
            duration : 0.05,
            serviceId: $serviceId,
            traceId  : $traceId
        ));

        // Invoke the diagnostics step
        $step(context: $context);

        // Verify report metadata
        $report = $context->getMeta(namespace: 'diagnostics', key: 'report');
        $this->assertIsArray(actual: $report);
        $this->assertEquals(expected: $serviceId, actual: $context->serviceId);
        $this->assertEquals(expected: 1, actual: $report['steps_count']);
        $this->assertEquals(expected: 50.0, actual: $report['duration_ms']); // 0.05s = 50ms

        // Verify detailed step metadata
        $steps = $context->getMeta(namespace: 'diagnostics', key: 'steps');
        $this->assertArrayHasKey(key: 'SomeStep', array: $steps);
        $this->assertEquals(expected: 50.0, actual: $steps['SomeStep']['duration_ms']);
        $this->assertEquals(expected: 'success', actual: $steps['SomeStep']['status']);
    }
}
