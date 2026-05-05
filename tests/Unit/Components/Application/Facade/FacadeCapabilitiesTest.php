<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Facade;

use Avax\Components\Application\Facade\System\Foundation\Facade;
use Avax\Components\Application\Facade\System\Foundation\FacadeInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class FacadeCapabilitiesTest extends TestCase
{
    public function testFacadeInterface() : void
    {
        $this->assertTrue(interface_exists(FacadeInterface::class));
    }

    public function testFacadeClass() : void
    {
        $this->assertTrue(class_exists(Facade::class));
    }

    public function testFacadeIsAbstract() : void
    {
        $reflection = new ReflectionClass(Facade::class);
        $this->assertTrue($reflection->isAbstract());
    }

    public function testFacadeHasGetFacadeAccessor() : void
    {
        $reflection = new ReflectionClass(Facade::class);
        $this->assertTrue($reflection->hasMethod('getFacadeAccessor'));
    }

    public function testFacadeHasClearResolvedInstance() : void
    {
        $reflection = new ReflectionClass(Facade::class);
        $this->assertTrue($reflection->hasMethod('clearResolvedInstance'));
    }

    public function testFacadeHasClearAllResolvedInstances() : void
    {
        $reflection = new ReflectionClass(Facade::class);
        $this->assertTrue($reflection->hasMethod('clearAllResolvedInstances'));
    }

    public function testFacadeHasSetContainer() : void
    {
        $reflection = new ReflectionClass(Facade::class);
        $this->assertTrue($reflection->hasMethod('setContainer'));
    }

    public function testFacadeHasFake() : void
    {
        $reflection = new ReflectionClass(Facade::class);
        $this->assertTrue($reflection->hasMethod('fake'));
    }

    public function testFacadeHasResolveInstance() : void
    {
        $reflection = new ReflectionClass(Facade::class);
        $this->assertTrue($reflection->hasMethod('resolveInstance'));
    }

    public function testFacadeHasCallStatic() : void
    {
        $reflection = new ReflectionClass(Facade::class);
        $this->assertTrue($reflection->hasMethod('__callStatic'));
    }
}