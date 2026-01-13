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
 * Entry-point action for executing callables with container-managed dependencies.
 *
 * This class serves as the public facade for the invocation subsystem. It
 * manages the lifecycle of the {@see InvocationExecutor} and provides a
 * simplified interface for the Kernel and developers to "Call" functions,
 * closures, or methods while having their parameters automatically fulfilled.
 *
 * @see     docs/Features/Actions/Invoke/Core/InvokeAction.md
 */
final class InvokeAction
{
    /** @var InvocationExecutor|null The internal execution engine. */
    private InvocationExecutor|null $executor = null;

    /**
     * Initializes the action with essential collaborators.
     *
     * @param ContainerInternalInterface|null $container The container facade for resolution.
     * @param DependencyResolverInterface     $resolver  The parameter resolver for finding argument values.
     */
    public function __construct(
        private ContainerInternalInterface|null      $container,
        private readonly DependencyResolverInterface $resolver
    )
    {
        if ($container !== null) {
            $this->wire(container: $container);
        }
    }

    /**
     * Initialize the internal executor with the provided container.
     *
     * @param ContainerInternalInterface $container The application container.
     */
    private function wire(ContainerInternalInterface $container) : void
    {
        $this->executor = new InvocationExecutor(
            container: $container,
            resolver : $this->resolver
        );
    }

    /**
     * Wire (or re-wire) the container reference for the invocation engine.
     *
     * @param ContainerInternalInterface $container The application container instance.
     *
     * @see docs/Features/Actions/Invoke/Core/InvokeAction.md#method-setcontainer
     */
    public function setContainer(ContainerInternalInterface $container) : void
    {
        $this->container = $container;
        $this->wire(container: $container);
    }

    /**
     * Invoke the target callable with automated dependency resolution.
     *
     * @param callable|string      $target     Generic PHP callable or "Class@method" string.
     * @param array<string, mixed> $parameters Manual argument overrides (Name => Value).
     * @param KernelContext|null   $context    Current resolution context for lifecycle tracking.
     *
     * @return mixed The result of the execution.
     *
     * @throws \ReflectionException If the target cannot be reflected.
     * @throws RuntimeException If the internal engine is not ready.
     *
     * @see docs/Features/Actions/Invoke/Core/InvokeAction.md#method-invoke
     */
    public function invoke(
        callable|string    $target,
        array|null         $parameters = null,
        KernelContext|null $context = null
    ) : mixed
    {
        $parameters ??= [];
        if ($this->executor === null) {
            throw new RuntimeException(message: 'InvokeAction executor not initialized. Ensure container is wired.');
        }

        // 1. Create a specialized invocation context for this call
        $invocationContext = new InvocationContext(originalTarget: $target);

        // 2. Delegate execution to the dedicated executor
        return $this->executor->execute(
            context      : $invocationContext,
            parameters   : $parameters,
            parentContext: $context,
        );
    }
}
