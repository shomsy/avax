<?php

declare(strict_types=1);

namespace Avax\Tests\Core;

use Avax\Database\Core\Container;
use Avax\Database\Core\EventBus;
use Avax\Database\Core\Registry;
use Avax\Database\Events\EventBus;
use Avax\Database\Kernel;
use Avax\Tests\TestCase;

class KernelTest extends TestCase
{
    public function testKernelIsSingleton() : void
    {
        $instance1 = Kernel::getInstance();
        $instance2 = Kernel::getInstance();

        $this->assertSame(expected: $instance1, actual: $instance2);
    }

    public function testKernelResolvesCoreServices() : void
    {
        $container = $this->kernel->getContainer();

        $this->assertInstanceOf(expected: Container::class, actual: $container->resolve('container'));
        $this->assertInstanceOf(expected: Registry::class, actual: $container->resolve('registry'));
        $this->assertInstanceOf(expected: EventBus::class, actual: $container->resolve('events'));
    }

    public function testKernelIsBootstrapped() : void
    {
        $container = $this->kernel->getContainer();

        $this->assertTrue(condition: $container->has('config'));
        $this->assertEquals(expected: 'sqlite', actual: $container->resolve('config')->get('database.default'));
    }
}
