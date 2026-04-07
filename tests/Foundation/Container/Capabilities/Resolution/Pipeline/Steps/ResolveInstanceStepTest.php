<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Capabilities\Resolution\Pipeline\Steps;

use Avax\Container\Capabilities\Resolution\Engine\EngineInterface;
use Avax\Container\Capabilities\Resolution\Kernel\KernelContext;
use Avax\Container\Capabilities\Resolution\Pipeline\Steps\ResolveInstanceStep;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

final class ResolveInstanceStepTest extends TestCase
{
    public function test_step_delegates_to_engine_and_sets_instance() : void
    {
        $expectedInstance       = new stdClass;
        $expectedInstance->test = 'value';

        $engine = $this->createMock(EngineInterface::class);
        $engine->expects(invocationRule: $this->once())
            ->method(constraint: 'resolve')
            ->with($this->isInstanceOf(className: KernelContext::class))
            ->willReturn(value: $expectedInstance);

        $step    = new ResolveInstanceStep(engine: $engine);
        $context = new KernelContext(serviceId: 'test-service');

        $step(context: $context);

        $this->assertSame(expected: $expectedInstance, actual: $context->getInstance());
        $this->assertNotNull(actual: $context->getMeta(namespace: 'resolution', key: 'completed_at'));
    }

    public function test_step_handles_engine_exceptions() : void
    {
        $engine = $this->createMock(EngineInterface::class);
        $engine->expects(invocationRule: $this->once())
            ->method(constraint: 'resolve')
            ->willThrowException(exception: new RuntimeException(message: 'Engine failed'));

        $step    = new ResolveInstanceStep(engine: $engine);
        $context = new KernelContext(serviceId: 'test-service');

        $this->expectException(exception: RuntimeException::class);
        $this->expectExceptionMessage(message: 'Engine failed');

        $step(context: $context);
    }
}
