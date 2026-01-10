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
    public function testStepDelegatesToEngineAndSetsInstance(): void
    {
        $expectedInstance       = new stdClass();
        $expectedInstance->test = 'value';

        $engine = $this->createMock(EngineInterface::class);
        $engine->expects($this->once())
            ->method('resolve')
            ->with($this->isInstanceOf(KernelContext::class))
            ->willReturn($expectedInstance);

        $step    = new ResolveInstanceStep(engine: $engine);
        $context = new KernelContext(serviceId: 'test-service');

        $step(context: $context);

        $this->assertSame($expectedInstance, $context->getInstance());
        $this->assertNotNull($context->getMeta('resolution', 'completed_at'));
    }

    /**
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     */
    public function testStepHandlesEngineExceptions(): void
    {
        $engine = $this->createMock(EngineInterface::class);
        $engine->expects($this->once())
            ->method('resolve')
            ->willThrowException(new RuntimeException('Engine failed'));

        $step    = new ResolveInstanceStep(engine: $engine);
        $context = new KernelContext(serviceId: 'test-service');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Engine failed');

        $step(context: $context);
    }
}
