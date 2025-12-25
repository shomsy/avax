<?php

declare(strict_types=1);

namespace Avax\Tests\Core;

use Avax\Database\Core\Container;
use Avax\Database\Core\EventBus;
use Avax\Database\Core\Registry;
use Avax\Database\Kernel;
use Avax\Tests\TestCase;

class KernelTest extends TestCase
{
    public function testKernelIsSingleton() : void
    {
        $instance1 = Kernel::getInstance();
        $instance2 = Kernel::getInstance();

        $this->assertSame($instance1, $instance2);
    }

    public function testKernelResolvesCoreServices() : void
    {
        $container = $this->kernel->getContainer();

        $this->assertInstanceOf(Container::class, $container->resolve('container'));
        $this->assertInstanceOf(Registry::class, $container->resolve('registry'));
        $this->assertInstanceOf(EventBus::class, $container->resolve('events'));
    }

    public function testKernelIsBootstrapped() : void
    {
        $container = $this->kernel->getContainer();

        $this->assertTrue($container->has('config'));
        $this->assertEquals('sqlite', $container->resolve('config')->get('database.default'));
    }
}
