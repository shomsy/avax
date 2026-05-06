<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Facade;

use Avax\Components\Application\Facade\System\Foundation\Facade;
use Avax\Components\Application\Facade\System\Foundation\FacadeInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

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
}
