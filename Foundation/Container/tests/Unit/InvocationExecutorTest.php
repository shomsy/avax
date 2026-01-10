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
    public function testParameterResolutionUsesParentContext(): void
    {
        $parentContext    = new KernelContext(serviceId: 'root');
        $container        = $this->createMock(ContainerInterface::class);
        $resolver         = $this->createMock(DependencyResolverInterface::class);

        $resolver->expects($this->once())
            ->method('resolveParameters')
            ->with(
                $this->isType('array'),
                $this->identicalTo([]),
                $this->identicalTo($container),
                $this->callback(static function (KernelContext $context) use ($parentContext): bool {
                    return $context->parent === $parentContext;
                })
            )
            ->willReturn([]);

        $executor = new InvocationExecutor(
            container: $container,
            resolver: $resolver
        );

        $result = $executor->execute(
            context: new InvocationContext(originalTarget: static fn(): string => 'ok'),
            parameters: [],
            parentContext: $parentContext
        );

        $this->assertSame('ok', $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testClassAtMethodUsesContainerForResolution(): void
    {
        $parentContext    = new KernelContext(serviceId: 'custom');
        $container        = $this->createMock(ContainerInterface::class);
        $resolver         = $this->createMock(DependencyResolverInterface::class);

        $container->expects($this->once())
            ->method('get')
            ->with(InvocationTarget::class)
            ->willReturn(new InvocationTarget());

        $resolver->expects($this->once())
            ->method('resolveParameters')
            ->with(
                $this->isType('array'),
                $this->identicalTo([]),
                $this->identicalTo($container),
                $this->callback(static function (KernelContext $context) use ($parentContext): bool {
                    return $context->parent === $parentContext;
                })
            )
            ->willReturn(['bob']);

        $executor = new InvocationExecutor(
            container: $container,
            resolver: $resolver
        );

        $result = $executor->execute(
            context: new InvocationContext(originalTarget: InvocationTarget::class . '@greet'),
            parameters: [],
            parentContext: $parentContext
        );

        $this->assertSame('hi bob', $result);
    }
}

final class InvocationTarget
{
    public function greet(string $name): string
    {
        return 'hi ' . $name;
    }
}
