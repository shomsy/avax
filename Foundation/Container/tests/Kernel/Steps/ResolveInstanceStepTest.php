<?php

declare(strict_types=1);

namespace Avax\Tests\Container\Kernel\Steps;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\Steps\ResolveInstanceStep;
use Avax\Container\Features\Actions\Resolve\Contracts\EngineInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

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

        $this->assertSame($expectedInstance, $context->instance);
        $this->assertArrayHasKey('resolution', $context->metadata);
        $this->assertSame('engine_instantiation', $context->metadata['resolution']['strategy']);
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
