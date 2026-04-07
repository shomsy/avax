<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Capabilities\Invocation\CallableInvocation;

use Avax\Container\DependencyInjection\Capabilities\Invocation\CallableInvocation\InvocationContext;
use Avax\Container\DependencyInjection\Capabilities\Invocation\CallableInvocation\InvocationExecutor;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Contracts\ContainerRuntimeInterface;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Engine\DependencyResolver;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Kernel\KernelContext;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use stdClass;

final class InvocationExecutorTest extends TestCase
{
    /** @throws ReflectionException */
    public function test_parameter_resolution_uses_parent_context() : void
    {
        $parentContext = new KernelContext(serviceId: 'root');
        $container     = $this->createMock(ContainerRuntimeInterface::class);

        $container->expects(invocationRule: $this->once())
            ->method(constraint: 'resolveContext')
            ->with($this->callback(static function (KernelContext $context) use ($parentContext) : bool {
                return $context->parent === $parentContext && $context->serviceId === stdClass::class;
            }))
            ->willReturn(value: new stdClass);

        $executor = new InvocationExecutor(
            container: $container,
            resolver : new DependencyResolver
        );

        $result = $executor->execute(
            context      : new InvocationContext(originalTarget: static fn(stdClass $service) : string => 'ok'),
            parameters   : [],
            parentContext: $parentContext
        );

        $this->assertSame(expected: 'ok', actual: $result);
    }

    /** @throws ReflectionException */
    public function test_class_at_method_uses_container_for_resolution() : void
    {
        $parentContext = new KernelContext(serviceId: 'custom');
        $container     = $this->createMock(ContainerRuntimeInterface::class);

        $container->expects(invocationRule: $this->once())
            ->method(constraint: 'get')
            ->with(InvocationTarget::class)
            ->willReturn(value: new InvocationTarget);

        $container->expects(invocationRule: $this->once())
            ->method(constraint: 'resolveContext')
            ->with($this->callback(static function (KernelContext $context) use ($parentContext) : bool {
                return $context->parent?->parent === $parentContext || $context->parent === $parentContext;
            }))
            ->willReturn(value: new stdClass);

        $executor = new InvocationExecutor(
            container: $container,
            resolver : new DependencyResolver
        );

        $result = $executor->execute(
            context      : new InvocationContext(originalTarget: InvocationTarget::class . '@greet'),
            parameters   : [],
            parentContext: $parentContext
        );

        $this->assertSame(expected: 'hi', actual: $result);
    }
}

final class InvocationTarget
{
    public function greet(stdClass $service) : string
    {
        return 'hi';
    }
}
