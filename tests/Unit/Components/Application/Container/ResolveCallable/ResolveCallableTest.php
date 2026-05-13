<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Container\ResolveCallable;

use Avax\Components\Application\Container\System\Capabilities\ResolveCallable\CallableResolutionFailed;
use Avax\Components\Application\Container\System\Capabilities\ResolveCallable\ResolveCallable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class ResolveCallableTest extends TestCase
{
    #[Test]
    public function resolves_closure(): void
    {
        $resolver = new ResolveCallable();
        $closure = static fn (string $msg): string => "Hello {$msg}";

        $callable = $resolver->resolve($closure);
        self::assertSame('Hello World', $callable('World'));
    }

    #[Test]
    public function resolves_invokable_object(): void
    {
        $resolver = new ResolveCallable();
        $handler = new InvokableHandler();

        $callable = $resolver->resolve($handler);
        self::assertSame('handled', $callable('input'));
    }

    #[Test]
    public function resolves_invokable_class_string(): void
    {
        $resolver = new ResolveCallable();

        $callable = $resolver->resolve(InvokableHandler::class);
        self::assertSame('handled', $callable('input'));
    }

    #[Test]
    public function rejects_non_invokable_class_string(): void
    {
        $resolver = new ResolveCallable();

        $this->expectException(CallableResolutionFailed::class);
        $this->expectExceptionMessage('is not invokable');

        $resolver->resolve(NonInvokableClass::class);
    }

    #[Test]
    public function uses_container_first_when_provided(): void
    {
        $containerWithDependency = new ContainerWithDependency();
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')
            ->with(HandlerWithDependency::class)
            ->willReturn($containerWithDependency);

        $resolver = new ResolveCallable($container);

        $callable = $resolver->resolve(HandlerWithDependency::class);
        self::assertSame('dep-value', $callable('input'));
    }

    #[Test]
    public function falls_back_to_zero_arg_instantiation_when_container_cannot_resolve(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')
            ->willThrowException(new \RuntimeException('Not found'));

        $resolver = new ResolveCallable($container);

        $callable = $resolver->resolve(InvokableHandler::class);
        self::assertSame('handled', $callable('input'));
    }

    #[Test]
    public function falls_back_to_zero_arg_instantiation_when_no_container(): void
    {
        $resolver = new ResolveCallable();

        $callable = $resolver->resolve(InvokableHandler::class);
        self::assertSame('handled', $callable('input'));
    }

    #[Test]
    public function bubbles_constructor_failure(): void
    {
        $resolver = new ResolveCallable();

        $this->expectException(CallableResolutionFailed::class);
        $this->expectExceptionMessage('Cannot instantiate');

        $resolver->resolve(HandlerWithRequiredDependency::class);
    }

    #[Test]
    public function bubbles_resolve_failure_for_nonexistent_class(): void
    {
        $resolver = new ResolveCallable();

        $this->expectException(CallableResolutionFailed::class);
        $this->expectExceptionMessage('class does not exist');

        // @phpstan-ignore argument.type
        $resolver->resolve('NonExistentClass');
    }

    #[Test]
    public function invoke_resolves_and_calls_in_one_step(): void
    {
        $resolver = new ResolveCallable();

        $result = $resolver->invoke(InvokableHandler::class, ['test']);
        self::assertSame('handled', $result);
    }
}

final class InvokableHandler
{
    public function __invoke(string $input): string
    {
        return 'handled';
    }
}

final class NonInvokableClass
{
}

final class ContainerWithDependency
{
    public function __invoke(string $input): string
    {
        return 'dep-value';
    }
}

final class HandlerWithDependency
{
    public function __invoke(string $input): string
    {
        return 'dep-value';
    }
}

final class HandlerWithRequiredDependency
{
    public function __construct(object $dep)
    {
    }
}
