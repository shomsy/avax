<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Container\PublicSurface;

use Avax\Components\Application\Container\BindingBuilderInterface;
use Avax\Components\Application\Container\Container;
use Avax\Components\Application\Container\ContainerInterface;
use Avax\Components\Application\Container\ContextBuilderInterface;
use Avax\Components\Application\Container\DependencyInjection\Capability\Resolution\Contracts\ContainerRuntimeInterface;
use Avax\Components\Application\Container\DI\Capabilities\Execution\Injection\Reports\InjectionReport;
use Avax\Components\Application\Container\ScopeManagerInterface;
use Avax\Tests\TestCase;
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
        $this->assertFalse(condition: is_a(object_or_class: Container::class, class: ContainerRuntimeInterface::class, allow_string: true));
    }

    public function test_container_does_not_expose_runtime_only_methods() : void
    {
        $this->assertFalse(condition: method_exists(object_or_class: Container::class, method: 'resolveContext'));
        $this->assertFalse(condition: method_exists(object_or_class: Container::class, method: 'resolve'));
    }
}
