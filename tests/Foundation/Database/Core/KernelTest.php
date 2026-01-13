<?php

declare(strict_types=1);

namespace Avax\Tests\Core;

use Avax\Database\Events\EventBus;
use Avax\Database\Kernel;
use Avax\Tests\TestCase;

class KernelTest extends TestCase
{
    public function test_kernel_is_singleton(): void
    {
        $instance1 = Kernel::getInstance();
        $instance2 = Kernel::getInstance();

        $this->assertSame(expected: $instance1, actual: $instance2);
    }

    public function test_kernel_resolves_core_services(): void
    {
        $container = $this->kernel->getContainer();

        $this->assertInstanceOf(expected: Container::class, actual: $container->resolve('container'));
        $this->assertInstanceOf(expected: Registry::class, actual: $container->resolve('registry'));
        $this->assertInstanceOf(expected: EventBus::class, actual: $container->resolve('events'));
    }

    public function test_kernel_is_bootstrapped(): void
    {
        $container = $this->kernel->getContainer();

        $this->assertTrue(condition: $container->has('config'));
        $this->assertEquals(expected: 'sqlite', actual: $container->resolve('config')->get('database.default'));
    }
}
