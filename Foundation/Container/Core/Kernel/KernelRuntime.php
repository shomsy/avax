<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Features\Actions\Invoke\Core\InvokeAction;
use Avax\Container\Features\Core\Exceptions\ResolutionException;
use Avax\Container\Features\Think\Model\ServicePrototype;

/**
 * Kernel Runtime - Execution layer that delegates to the resolution pipeline.
 *
 * Acts as the execution engine for service resolution, coordinating between
 * the resolution pipeline and invocation system. Handles the core resolution
 * flow while maintaining clean separation from higher-level orchestration, ensuring reliable and consistent dependency injection execution.
 *
 * @see docs_md/Core/Kernel/KernelRuntime.md#quick-summary
 */
final readonly class KernelRuntime
{
    private const string INTERNAL_INJECT = '__inject__';

    /**
     * Initialize runtime with pipeline and invoker.
     *
     * @param ResolutionPipeline $pipeline The resolution pipeline to execute
     * @param InvokeAction $invoker The invocation system for callables
     */
    public function __construct(
        private ResolutionPipeline $pipeline,
        private InvokeAction       $invoker
    ) {}

    /**
     * Resolve a service by its identifier.
     *
     * Initiates standard service resolution through the pipeline, creating a basic context
     * and executing the full resolution process to return a fully constructed service instance.
     *
     * @param string $id Service identifier to resolve
     * @return mixed Resolved service instance
     * @throws ResolutionException If service cannot be resolved
     * @see docs_md/Core/Kernel/KernelRuntime.md#method-get
     */
    public function get(string $id) : mixed
    {
        return $this->resolveContext(new KernelContext(serviceId: $id));
    }

    /**
     * Explicitly resolve with a context.
     *
     * Executes resolution using a pre-configured KernelContext, allowing advanced resolution
     * scenarios with custom metadata, overrides, and execution parameters.
     *
     * @param KernelContext $context Resolution context with metadata
     * @return mixed Resolved service instance
     * @throws ResolutionException If pipeline fails to resolve
     * @throws \Throwable From pipeline execution
     * @see docs_md/Core/Kernel/KernelRuntime.md#method-resolveContext
     */
    public function resolveContext(KernelContext $context) : mixed
    {
        $this->pipeline->run(context: $context);

        if ($context->getInstance() === null) {
            throw new ResolutionException(message: "Service '{$context->serviceId}' not resolved by pipeline at path: {$context->getPath()}");
        }

        return $context->getInstance();
    }

    /**
     * Resolve a service by its identifier with overrides.
     *
     * Creates a new service instance with runtime parameter overrides, bypassing any
     * shared instance caching to ensure unique object creation with custom configuration.
     *
     * @param string $id Service identifier
     * @param array $parameters Override parameters for constructor/method injection
     * @return object Resolved service instance
     * @throws ResolutionException If service cannot be resolved
     * @see docs_md/Core/Kernel/KernelRuntime.md#method-make
     */
    public function make(string $id, array $parameters = []) : object
    {
        return $this->resolveContext(new KernelContext(
            serviceId: $id,
            overrides: $parameters
        ));
    }

    /**
     * Resolve a service prototype.
     *
     * Executes optimized resolution using a pre-analyzed service prototype, bypassing
     * standard analysis for improved performance in frequently resolved services.
     *
     * @param ServicePrototype $prototype Pre-analyzed service blueprint
     * @return mixed Resolved service instance
     * @throws ResolutionException If prototype cannot be resolved
     * @see docs_md/Core/Kernel/KernelRuntime.md#method-resolve
     */
    public function resolve(ServicePrototype $prototype) : mixed
    {
        $ctx = new KernelContext(serviceId: $prototype->class);
        $ctx->setMeta('analysis', 'prototype', $prototype);

        return $this->resolveContext($ctx);
    }

    /**
     * Execute a callable with dependency injection.
     *
     * Invokes a function, method, or closure with automatic dependency injection applied
     * to its parameters, enabling procedural code to participate in the container's resolution system.
     *
     * @param callable|string $callable Callable to execute
     * @param array $parameters Override parameters that take precedence over injection
     * @return mixed Execution result
     * @throws \ReflectionException If callable cannot be analyzed
     * @see docs_md/Core/Kernel/KernelRuntime.md#method-call
     */
    public function call(callable|string $callable, array $parameters = []) : mixed
    {
        return $this->invoker->invoke(target: $callable, parameters: $parameters);
    }

    /**
     * Inject dependencies into an object.
     *
     * Performs dependency injection into an existing object instance that was created
     * outside the container, enabling retrofit injection for legacy objects or deserialization scenarios.
     *
     * @param object $target Object to inject into
     * @return object Object with injected dependencies
     * @throws \Throwable From pipeline execution
     * @see docs_md/Core/Kernel/KernelRuntime.md#method-injectInto
     */
    public function injectInto(object $target) : object
    {
        $ctx = new KernelContext(
            serviceId      : self::INTERNAL_INJECT,
            manualInjection: true
        );
        $ctx->resolvedWith(instance: $target);
        $ctx->setMeta('inject', 'target', true);

        $this->pipeline->run(context: $ctx);

        return $ctx->getInstance();
    }
}
