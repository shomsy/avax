<?php

declare(strict_types=1);

namespace Avax\Container\Features\Actions\Invoke\Core;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Features\Actions\Invoke\Context\InvocationContext;
use Avax\Container\Features\Actions\Invoke\InvocationExecutor;
use Avax\Container\Features\Actions\Resolve\Contracts\DependencyResolverInterface;
use Avax\Container\Features\Core\Contracts\ContainerInternalInterface;
use RuntimeException;

/**
 * Service for invoking callables with automatic dependency resolution.
 *
 * @see docs_md/Features/Actions/Invoke/Core/InvokeAction.md#quick-summary
 */
final class InvokeAction
{
    private InvocationExecutor|null $executor = null;

    public function __construct(
        private ContainerInternalInterface|null      $container,
        private readonly DependencyResolverInterface $resolver
    )
    {
        if ($container !== null) {
            $this->wire($container);
        }
    }

    /**
     * Wire the invocation executor with the container.
     *
     * @param ContainerInternalInterface $container
     * @return void
     * @see docs_md/Features/Actions/Invoke/Core/InvokeAction.md#method-setcontainer
     */
    private function wire(ContainerInternalInterface $container) : void
    {
        $this->executor = new InvocationExecutor(
            container: $container,
            resolver : $this->resolver
        );
    }

    /**
     * @param ContainerInternalInterface $container
     * @return void
     * @see docs_md/Features/Actions/Invoke/Core/InvokeAction.md#method-setcontainer
     */
    public function setContainer(ContainerInternalInterface $container) : void
    {
        $this->container = $container;
        $this->wire($container);
    }

    /**
     * Invoke the target callable with automated dependency resolution.
     *
     * @param callable|string    $target
     * @param array              $parameters
     * @param KernelContext|null $context
     *
     * @return mixed
     * @throws \ReflectionException
     * @throws RuntimeException When the executor is not wired
     * @see docs_md/Features/Actions/Invoke/Core/InvokeAction.md#method-invoke
     */
    public function invoke(
        callable|string    $target,
        array              $parameters = [],
        KernelContext|null $context = null
    ) : mixed
    {
        if ($this->executor === null) {
            throw new RuntimeException('InvokeAction executor not initialized. Ensure container is wired.');
        }

        $invocationContext = new InvocationContext(originalTarget: $target);

        return $this->executor->execute(
            context      : $invocationContext,
            parameters   : $parameters,
            parentContext: $context,
        );
    }
}
