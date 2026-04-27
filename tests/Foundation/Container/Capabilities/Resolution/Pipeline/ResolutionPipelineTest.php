<?php

declare(strict_types=1);

namespace components\Container\Tests\Capability\Resolution\Pipeline;

use components\Container\DependencyInjection\Capability\Resolution\Kernel\KernelContext;
use components\Container\DependencyInjection\Capability\Resolution\Pipeline\Contracts\KernelStep;
use components\Container\DependencyInjection\Capability\Resolution\Pipeline\ResolutionPipeline;
use components\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;
use components\Tests\TestCase;
use RuntimeException;
use Throwable;

final class ResolutionPipelineTest extends TestCase
{
    /** @throws Throwable */
    public function test_pipeline_executes_steps_in_order() : void
    {
        $context = new KernelContext(serviceId: 'test-service');

        $step1 = new class implements KernelStep {
            public function __invoke(KernelContext $context) : void
            {
                $context->setMeta(namespace: 'test', key: 'step1', value: 'executed');
            }
        };

        $step2 = new class implements KernelStep {
            public function __invoke(KernelContext $context) : void
            {
                $context->setMeta(namespace: 'test', key: 'step2', value: 'executed');
            }
        };

        $pipeline = new ResolutionPipeline(steps: [$step1, $step2]);
        $pipeline->run(context: $context);

        $this->assertSame(expected: 'executed', actual: $context->getMeta(namespace: 'test', key: 'step1'));
        $this->assertSame(expected: 'executed', actual: $context->getMeta(namespace: 'test', key: 'step2'));
    }

    public function test_pipeline_fails_with_empty_steps() : void
    {
        $this->expectException(exception: ContainerException::class);
        $this->expectExceptionMessage(message: 'Resolution pipeline cannot be empty');

        new ResolutionPipeline(steps: []);
    }

    public function test_pipeline_validates_step_types() : void
    {
        $this->expectException(exception: ContainerException::class);
        $this->expectExceptionMessage(message: 'Step at index 0 must implement KernelStep interface');

        new ResolutionPipeline(steps: ['not-a-step']);
    }

    /** @throws Throwable */
    public function test_pipeline_handles_step_failure() : void
    {
        $context = new KernelContext(serviceId: 'test-service');

        $failingStep = new class implements KernelStep {
            public function __invoke(KernelContext $context) : void
            {
                throw new RuntimeException(message: 'Step failed');
            }
        };

        $pipeline = new ResolutionPipeline(steps: [$failingStep]);

        $this->expectException(exception: ContainerException::class);
        $this->expectExceptionMessage(message: 'Resolution pipeline failed at step 1');

        $pipeline->run(context: $context);
    }

    public function test_pipeline_count_and_get_steps() : void
    {
        $step1 = $this->createMock(originalClassName: KernelStep::class);
        $step2 = $this->createMock(originalClassName: KernelStep::class);

        $pipeline = new ResolutionPipeline(steps: [$step1, $step2]);

        $this->assertSame(expected: 2, actual: $pipeline->count());
        $this->assertSame(expected: $step1, actual: $pipeline->getStep(index: 0));
        $this->assertSame(expected: $step2, actual: $pipeline->getStep(index: 1));
    }

    public function test_get_step_out_of_bounds() : void
    {
        $pipeline = new ResolutionPipeline(steps: [$this->createMock(originalClassName: KernelStep::class)]);

        $this->expectException(exception: ContainerException::class);
        $this->expectExceptionMessage(message: 'Step index 1 is out of bounds');

        $pipeline->getStep(index: 1);
    }

    public function test_with_step_and_with_step_first() : void
    {
        $step1 = $this->createMock(originalClassName: KernelStep::class);
        $step2 = $this->createMock(originalClassName: KernelStep::class);
        $step3 = $this->createMock(originalClassName: KernelStep::class);

        $pipeline = new ResolutionPipeline(steps: [$step1, $step2]);

        $extended = $pipeline->withStep(step: $step3);
        $this->assertSame(expected: 3, actual: $extended->count());

        $prefixed = $pipeline->withStepFirst(step: $step3);
        $this->assertSame(expected: 3, actual: $prefixed->count());
        $this->assertSame(expected: $step3, actual: $prefixed->getStep(index: 0));
    }
}
