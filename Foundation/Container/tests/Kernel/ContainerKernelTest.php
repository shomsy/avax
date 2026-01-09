<?php

declare(strict_types=1);
namespace Avax\Tests\Container\Kernel;

use Avax\Container\Core\Kernel\ContainerKernel;
use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\Contracts\KernelStep;
use Avax\Container\Core\Kernel\ResolutionPipeline;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

final class ContainerKernelTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testResolveDelegatesToPipeline() : void
    {
        $expectedInstance = new stdClass();

        $step = new class($expectedInstance) implements KernelStep {
            public function __construct(private object $instance) {}

            public function __invoke(KernelContext $context) : void
            {
                $context->instance = $this->instance;
            }
        };

        $pipeline = new ResolutionPipeline(steps: [$step]);
        $kernel   = new ContainerKernel(pipeline: $pipeline);

        $result = $kernel->resolve(id: 'test-service');

        $this->assertSame(expected: $expectedInstance, actual: $result);
    }

    /**
     * @throws \Throwable
     */
    public function testResolveThrowsWhenPipelineDoesNotSetInstance() : void
    {
        $step = new class implements KernelStep {
            public function __invoke(KernelContext $context) : void
            {
                // Do nothing - instance remains null
            }
        };

        $pipeline = new ResolutionPipeline(steps: [$step]);
        $kernel   = new ContainerKernel(pipeline: $pipeline);

        $this->expectException(exception: RuntimeException::class);
        $this->expectExceptionMessage(message: "Service 'test-service' not resolved");

        $kernel->resolve(id: 'test-service');
    }

    /**
     * @throws \Throwable
     */
    public function testResolvePassesServiceIdToContext() : void
    {
        $capturedContext = null;

        $step = new class($capturedContext) implements KernelStep {
            public function __construct(public KernelContext|null &$captured) {}

            public function __invoke(KernelContext $context) : void
            {
                $this->captured    = $context;
                $context->instance = new stdClass();
            }
        };

        $pipeline = new ResolutionPipeline(steps: [$step]);
        $kernel   = new ContainerKernel(pipeline: $pipeline);

        $kernel->resolve(id: 'my-service');

        $this->assertNotNull(actual: $capturedContext);
        $this->assertEquals(expected: 'my-service', actual: $capturedContext->serviceId);
        $this->assertIsArray(actual: $capturedContext->metadata);
    }
}