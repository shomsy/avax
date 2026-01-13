<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Unit;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Features\Actions\Invoke\Context\InvocationContext;
use Avax\Container\Features\Actions\Invoke\InvocationExecutor;
use Avax\Container\Features\Actions\Resolve\Contracts\DependencyResolverInterface;
use Avax\Container\Features\Core\Contracts\ContainerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * PHPUnit test coverage for Container component behavior.
 *
 * @see docs_md/tests/Unit/InvocationExecutorTest.md#quick-summary
 */
final class InvocationExecutorTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function test_parameter_resolution_uses_parent_context() : void
    {
        $parentContext = new KernelContext(serviceId: 'root');
        $container     = $this->createMock(ContainerInterface::class);
        $resolver      = $this->createMock(DependencyResolverInterface::class);

        $resolver->expects(invocationRule: $this->once())
            ->method(constraint: 'resolveParameters')
            ->with(
                $this->isType(type: 'array'),
                $this->identicalTo(value: []),
                $this->identicalTo(value: $container),
                $this->callback(callback: static function (KernelContext $context) use ($parentContext) : bool {
                    return $context->parent === $parentContext;
                })
            )
            ->willReturn(value: []);

        $executor = new InvocationExecutor(
            container: $container,
            resolver : $resolver
        );

        $result = $executor->execute(
            context      : new InvocationContext(originalTarget: static fn() : string => 'ok'),
            parameters   : [],
            parentContext: $parentContext
        );

        $this->assertSame(expected: 'ok', actual: $result);
    }

    /**
     * @throws ReflectionException
     */
    public function test_class_at_method_uses_container_for_resolution() : void
    {
        $parentContext = new KernelContext(serviceId: 'custom');
        $container     = $this->createMock(ContainerInterface::class);
        $resolver      = $this->createMock(DependencyResolverInterface::class);

        $container->expects(invocationRule: $this->once())
            ->method(constraint: 'get')
            ->with(InvocationTarget::class)
            ->willReturn(value: new InvocationTarget);

        $resolver->expects(invocationRule: $this->once())
            ->method(constraint: 'resolveParameters')
            ->with(
                $this->isType(type: 'array'),
                $this->identicalTo(value: []),
                $this->identicalTo(value: $container),
                $this->callback(callback: static function (KernelContext $context) use ($parentContext) : bool {
                    return $context->parent === $parentContext;
                })
            )
            ->willReturn(value: ['bob']);

        $executor = new InvocationExecutor(
            container: $container,
            resolver : $resolver
        );

        $result = $executor->execute(
            context      : new InvocationContext(originalTarget: InvocationTarget::class . '@greet'),
            parameters   : [],
            parentContext: $parentContext
        );

        $this->assertSame(expected: 'hi bob', actual: $result);
    }
}

final class InvocationTarget
{
    public function greet(string $name) : string
    {
        return 'hi ' . $name;
    }
}
