<?php

declare(strict_types=1);

namespace Avax\Container\Tests\PublicSurface;

use Avax\Container\BindingBuilderInterface;
use Avax\Container\Container;
use Avax\Container\ContainerInterface;
use Avax\Container\ContextBuilderInterface;
use Avax\Container\InjectionReport;
use Avax\Container\ScopeManagerInterface;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Contracts\ContainerRuntimeInterface;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class ContainerPublicSurfaceTest extends TestCase
{
    public function test_container_interface_returns_root_public_contracts() : void
    {
        $this->assertSame(
            expected: BindingBuilderInterface::class,
            actual  : $this->returnType(new ReflectionMethod(ContainerInterface::class, 'bind'))
        );
        $this->assertSame(
            expected: ContextBuilderInterface::class,
            actual  : $this->returnType(new ReflectionMethod(ContainerInterface::class, 'when'))
        );
        $this->assertSame(
            expected: InjectionReport::class,
            actual  : $this->returnType(new ReflectionMethod(ContainerInterface::class, 'inspectInjection'))
        );
        $this->assertSame(
            expected: ScopeManagerInterface::class,
            actual  : $this->returnType(new ReflectionMethod(ContainerInterface::class, 'scopes'))
        );
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

    private function returnType(ReflectionMethod $method) : string
    {
        return $method->getReturnType()?->getName() ?? '';
    }
}
