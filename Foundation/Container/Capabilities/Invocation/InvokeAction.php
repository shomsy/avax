<?php

declare(strict_types=1);

namespace Avax\Container\Capabilities\Invocation;

use Avax\Container\Capabilities\Invocation\CallableInvocation\InvocationContext;
use Avax\Container\Capabilities\Invocation\CallableInvocation\InvocationExecutor;
use Avax\Container\Capabilities\Resolution\Engine\DependencyResolver;
use Avax\Container\Capabilities\Resolution\Kernel\KernelContext;
use Psr\Container\ContainerInterface;
use RuntimeException;

/**
 * Entry unit for invoking callables with container-managed arguments.
 */
final class InvokeAction
{
    private InvocationExecutor|null $executor = null;

    public function __construct(
        private ContainerInterface|null $container,
        private readonly DependencyResolver $resolver
    ) {
        if ($container !== null) {
            $this->wire(container: $container);
        }
    }

    private function wire(ContainerInterface $container) : void
    {
        $this->executor = new InvocationExecutor(
            container: $container,
            resolver : $this->resolver
        );
    }

    public function setContainer(ContainerInterface $container) : void
    {
        $this->container = $container;
        $this->wire(container: $container);
    }

    public function invoke(
        callable|string $target,
        array|null $parameters = null,
        KernelContext|null $context = null
    ) : mixed
    {
        $parameters ??= [];
        if ($this->executor === null) {
            throw new RuntimeException(message: 'InvokeAction executor not initialized. Ensure container is wired.');
        }

        $invocationContext = new InvocationContext(originalTarget: $target);

        return $this->executor->execute(
            context      : $invocationContext,
            parameters   : $parameters,
            parentContext: $context,
        );
    }
}
