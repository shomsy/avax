<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Capability\Resolution\Engine;

use Avax\Container\Container;
use Avax\Container\DependencyInjection\Capability\Observability\Trace\ResolutionTrace;
use Avax\Container\DependencyInjection\Capability\Observability\Trace\TraceObserverInterface;
use Avax\Container\DependencyInjection\Capability\Resolution\Engine\ResolutionEngine;
use Avax\Container\DependencyInjection\Capability\Resolution\Kernel\ContainerKernel;
use Avax\Container\DependencyInjection\Capability\Resolution\Kernel\KernelContext;
use Avax\Container\DependencyInjection\Configuration\ContainerBuilder;
use Avax\Tests\TestCase;
use ReflectionProperty;
use stdClass;

final class ResolutionEngineTraceTest extends TestCase
{
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

        $stages = array_column(array: $observer->trace?->toArray() ?? [], column_key: 'stage');
        $this->assertContains(needle: 'evaluate', haystack: $stages);
        $this->assertContains(needle: 'instantiate', haystack: $stages);
    }

    private function extractEngine(Container $container) : ResolutionEngine
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
