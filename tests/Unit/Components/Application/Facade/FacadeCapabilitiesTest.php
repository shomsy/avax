<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Facade;

use Avax\Components\Application\Facade\System\Foundation\Facade;
use Avax\Components\Application\Facade\System\Foundation\FacadeInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use RuntimeException;

final class FacadeCapabilitiesTest extends TestCase
{
    public function test_facade_interface(): void
    {
        $this->assertTrue(interface_exists(FacadeInterface::class));
    }

    public function test_facade_class(): void
    {
        $this->assertTrue(class_exists(Facade::class));
    }

    public function test_facade_is_abstract(): void
    {
        $reflection = new ReflectionClass(Facade::class);
        $this->assertTrue($reflection->isAbstract());
    }

    public function test_facade_has_get_facade_accessor(): void
    {
        $reflection = new ReflectionClass(Facade::class);
        $this->assertTrue($reflection->hasMethod('getFacadeAccessor'));
    }

    public function test_facade_has_clear_resolved_instance(): void
    {
        $reflection = new ReflectionClass(Facade::class);
        $this->assertTrue($reflection->hasMethod('clearResolvedInstance'));
    }

    public function test_facade_has_clear_all_resolved_instances(): void
    {
        $reflection = new ReflectionClass(Facade::class);
        $this->assertTrue($reflection->hasMethod('clearAllResolvedInstances'));
    }

    public function test_facade_has_set_container(): void
    {
        $reflection = new ReflectionClass(Facade::class);
        $this->assertTrue($reflection->hasMethod('setContainer'));
    }

    public function test_facade_has_fake(): void
    {
        $reflection = new ReflectionClass(Facade::class);
        $this->assertTrue($reflection->hasMethod('fake'));
    }

    public function test_facade_has_resolve_instance(): void
    {
        $reflection = new ReflectionClass(Facade::class);
        $this->assertTrue($reflection->hasMethod('resolveInstance'));
    }

    public function test_facade_has_call_static(): void
    {
        $reflection = new ReflectionClass(Facade::class);
        $this->assertTrue($reflection->hasMethod('__callStatic'));
    }

    public function test_it_throws_when_no_container_is_set() : void
    {
        TestFacade::clearAllResolvedInstances();
        TestFacade::setContainerMock(null); // Force clear container just in case

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("No container available to resolve facade accessor 'test.service'");

        TestFacade::doSomething();
    }

    public function test_it_resolves_from_container() : void
    {
        $container = $this->createMock(ContainerInterface::class);
        $service   = new TestService();

        $container->expects($this->once())
            ->method('get')
            ->with('test.service')
            ->willReturn($service);

        TestFacade::setContainer($container);
        TestFacade::clearAllResolvedInstances();

        $result = TestFacade::doSomething();
        $this->assertSame('done', $result);
    }

    public function test_it_caches_resolved_instances() : void
    {
        $container = $this->createMock(ContainerInterface::class);
        $service   = new TestService();

        $container->expects($this->once()) // Only called ONCE due to caching
        ->method('get')
            ->with('test.service')
            ->willReturn($service);

        TestFacade::setContainer($container);
        TestFacade::clearAllResolvedInstances();

        $result1 = TestFacade::doSomething();
        $result2 = TestFacade::doSomething(); // Should hit cache

        $this->assertSame('done', $result1);
        $this->assertSame('done', $result2);
    }

    public function test_it_throws_when_resolved_instance_is_null() : void
    {
        $container = $this->createMock(ContainerInterface::class);

        $container->expects($this->once())
            ->method('get')
            ->with('test.service')
            ->willReturn(null);

        TestFacade::setContainer($container);
        TestFacade::clearAllResolvedInstances();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Facade accessor 'test.service' could not be resolved.");

        TestFacade::doSomething();
    }

    public function test_fake_replaces_resolved_instance() : void
    {
        $fakeService = new class {
            public function doSomething() : string
            {
                return 'faked';
            }
        };

        TestFacade::clearAllResolvedInstances();
        TestFacade::fake($fakeService);

        $result = TestFacade::doSomething();
        $this->assertSame('faked', $result);
    }

    public function test_clear_resolved_instance_removes_cache() : void
    {
        $container = $this->createMock(ContainerInterface::class);
        $service   = new TestService();

        $container->expects($this->exactly(2)) // Called TWICE because cache is cleared
        ->method('get')
            ->with('test.service')
            ->willReturn($service);

        TestFacade::setContainer($container);
        TestFacade::clearAllResolvedInstances();

        TestFacade::doSomething();
        TestFacade::clearResolvedInstance();
        TestFacade::doSomething();
    }
}

class TestService
{
    public function doSomething() : string
    {
        return 'done';
    }
}

class TestFacade extends Facade
{
    protected static string $accessor = 'test.service';

    // Helper for testing to bypass container caching issues globally
    public static function setContainerMock(?ContainerInterface $container) : void
    {
        static::$container = $container;
    }
}
