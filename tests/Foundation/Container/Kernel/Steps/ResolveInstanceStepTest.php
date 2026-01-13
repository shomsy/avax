<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Kernel\Steps;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\Steps\ResolveInstanceStep;
use Avax\Container\Features\Actions\Resolve\Contracts\EngineInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

/**
 * PHPUnit test coverage for Container component behavior.
 *
 * @see docs_md/tests/Kernel/Steps/ResolveInstanceStepTest.md#quick-summary
 */
final class ResolveInstanceStepTest extends TestCase
{
    /**
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     */
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

    /**
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     */
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
