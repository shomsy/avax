<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Integration;

use Avax\Container\Container;
use Avax\Container\Core\ContainerBuilder;
use Avax\Container\Core\ContainerKernel;
use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Features\Actions\Resolve\Engine;
use Avax\Container\Observe\Trace\ResolutionTrace;
use Avax\Container\Observe\Trace\TraceObserverInterface;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use stdClass;

/**
 * @see docs/tests/Integration/ResolutionFlowTest.md#quick-summary
 */
final class ResolutionFlowTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function test_trace_includes_evaluate_and_instantiate_stages() : void
    {
        $container = (new ContainerBuilder)->build(cacheDir: sys_get_temp_dir(), debug: false);
        $engine    = $this->extractEngine(container: $container);

        $observer = new class implements TraceObserverInterface {
            public ResolutionTrace|null $trace = null;

            public function record(ResolutionTrace $trace) : void
            {
                $this->trace = $trace;
            }
        };

        $context = new KernelContext(serviceId: stdClass::class);
        $result  = $engine->resolve(context: $context, traceObserver: $observer);

        $this->assertInstanceOf(expected: stdClass::class, actual: $result);
        $this->assertNotNull(actual: $observer->trace);

        $stages = array_column($observer->trace?->toArray() ?? [], 'stage');
        $this->assertContains(needle: 'evaluate', haystack: $stages);
        $this->assertContains(needle: 'instantiate', haystack: $stages);
    }

    private function extractEngine(Container $container) : Engine
    {
        $kernelProp = new ReflectionProperty(class: Container::class, property: 'kernel');
        $kernelProp->setAccessible(accessible: true);
        /** @var ContainerKernel $kernel */
        $kernel = $kernelProp->getValue(object: $container);

        $configProp = new ReflectionProperty(class: ContainerKernel::class, property: 'config');
        $configProp->setAccessible(accessible: true);

        return $configProp->getValue(object: $kernel)->engine;
    }
}
