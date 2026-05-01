<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Container\Capabilities\Resolution\Pipeline\Steps;

use Avax\Components\Application\Container\DependencyInjection\Capability\Resolution\Kernel\KernelContext;
use Avax\Components\Application\Container\DependencyInjection\Capability\Resolution\Pipeline\Events\StepStarted;
use Avax\Components\Application\Container\DependencyInjection\Capability\Resolution\Pipeline\Events\StepSucceeded;
use Avax\Components\Application\Container\DependencyInjection\Capability\Resolution\Pipeline\Steps\CollectDiagnosticsStep;
use Avax\Components\Application\Container\DependencyInjection\Capability\Resolution\Pipeline\Telemetry\StepTelemetryRecorder;
use Avax\Tests\TestCase;

final class CollectDiagnosticsStepTest extends TestCase
{
    public function test_it_collects_diagnostics_and_stores_in_context() : void
    {
        $serviceId = 'test.service';
        $traceId   = 'trace-123';
        $telemetry = new StepTelemetryRecorder();
        $step      = new CollectDiagnosticsStep(telemetry: $telemetry);

        $context = new KernelContext(
            serviceId: $serviceId,
            traceId  : $traceId,
        );

        $telemetry->onStepStarted(event: new StepStarted(
                                             stepClass: 'SomeStep',
                                             timestamp: 1000.0,
                                             serviceId: $serviceId,
                                             traceId  : $traceId,
                                         ));

        $telemetry->onStepSucceeded(event: new StepSucceeded(
                                               stepClass: 'SomeStep',
                                               startedAt: 1000.0,
                                               endedAt  : 1000.05,
                                               duration : 0.05,
                                               serviceId: $serviceId,
                                               traceId  : $traceId,
                                           ));

        $step(context: $context);

        $report = $context->getMeta(namespace: 'diagnostics', key: 'report');
        $this->assertIsArray(actual: $report);
        $this->assertSame(expected: $serviceId, actual: $context->serviceId);
        $this->assertSame(expected: 1, actual: $report['steps_count']);
        $this->assertSame(expected: 50.0, actual: $report['duration_ms']);

        $steps = $context->getMeta(namespace: 'diagnostics', key: 'steps');
        $this->assertArrayHasKey(key: 'SomeStep', array: $steps);
        $this->assertSame(expected: 50.0, actual: $steps['SomeStep']['duration_ms']);
        $this->assertSame(expected: 'success', actual: $steps['SomeStep']['status']);
    }
}
