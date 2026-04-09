<?php

declare(strict_types=1);

namespace Avax\Container\Tests\PublicSurface;

use Avax\Container\BindingBuilderInterface;
use Avax\Container\Container;
use Avax\Container\ContainerInterface;
use Avax\Container\ContextBuilderInterface;
use Avax\Container\DependencyInjection\Capability\Resolution\Contracts\ContainerRuntimeInterface;
use Avax\Container\InjectionReport;
use Avax\Container\ScopeManagerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class ContainerPublicSurfaceTest extends TestCase
{
    public function test_container_interface_returns_root_public_contracts() : void
    {
        $this->assertSame(
            expected: BindingBuilderInterface::class,
            actual  : $this->returnType(method: new ReflectionMethod(objectOrMethod: ContainerInterface::class, method: 'bind'))
        );
        $this->assertSame(
            expected: ContextBuilderInterface::class,
            actual  : $this->returnType(method: new ReflectionMethod(objectOrMethod: ContainerInterface::class, method: 'when'))
        );
        $this->assertSame(
            expected: InjectionReport::class,
            actual  : $this->returnType(method: new ReflectionMethod(objectOrMethod: ContainerInterface::class, method: 'inspectInjection'))
        );
        $this->assertSame(
            expected: ScopeManagerInterface::class,
            actual  : $this->returnType(method: new ReflectionMethod(objectOrMethod: ContainerInterface::class, method: 'scopes'))
        );
    }

    private function returnType(ReflectionMethod $method) : string
    {
        return $method->getReturnType()?->getName() ?? '';
    }

    public function test_container_does_not_implement_runtime_interface() : void
    {
        $this->assertFalse(condition: is_a(Container::class, ContainerRuntimeInterface::class, true));
    }

    public function test_container_does_not_expose_runtime_only_methods() : void
    {
        $this->assertFalse(condition: method_exists(Container::class, 'resolveContext'));
        $this->assertFalse(condition: method_exists(Container::class, 'resolve'));
    }
}
