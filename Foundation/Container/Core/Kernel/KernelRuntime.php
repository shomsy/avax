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
 * Coordinates the actual resolution logic, invoking the pipeline and handling
 * final instance extraction. Acts as the engine room for the ContainerKernel.
 *
 * @see docs/Core/Kernel/KernelRuntime.md#quick-summary
 */
final readonly class KernelRuntime
{
    private const string INTERNAL_INJECT = '__inject__';

    /**
     * Initialize runtime with pipeline and invoker.
     *
     * @param ResolutionPipeline $pipeline The execution pipeline.
     * @param InvokeAction       $invoker  The invocation helper.
     * @see docs/Core/Kernel/KernelRuntime.md#method-__construct
     */
    public function __construct(
        private ResolutionPipeline $pipeline,
        private InvokeAction       $invoker
    ) {}

    /**
     * Resolve a service by its identifier.
     *
     * @param string $id The service identifier.
     * @return mixed The resolved instance.
     * @throws ResolutionException If resolution fails.
     * @see docs/Core/Kernel/KernelRuntime.md#method-get
     */
    public function get(string $id): mixed
    {
        return $this->resolveContext(context: new KernelContext(serviceId: $id));
    }

    /**
     * Explicitly resolve with a context.
     *
     * @param KernelContext $context The resolution state.
     * @return mixed The resolved instance.
     * @throws ResolutionException If pipeline does not resolve the service.
     * @see docs/Core/Kernel/KernelRuntime.md#method-resolvecontext
     */
    public function resolveContext(KernelContext $context): mixed
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
     * @param string $id         The service identifier.
     * @param array  $parameters Constructor/method overrides.
     * @return object The resolved instance.
     * @throws ResolutionException If resolution fails.
     * @see docs/Core/Kernel/KernelRuntime.md#method-make
     */
    public function make(string $id, array $parameters = []): object
    {
        return $this->resolveContext(context: new KernelContext(
            serviceId: $id,
            overrides: $parameters
        ));
    }

    /**
     * Resolve a service prototype.
     *
     * @param ServicePrototype $prototype The pre-analyzed metadata.
     * @return mixed The resolved instance.
     * @throws ResolutionException If resolution fails.
     * @see docs/Core/Kernel/KernelRuntime.md#method-resolve
     */
    public function resolve(ServicePrototype $prototype): mixed
    {
        $ctx = new KernelContext(serviceId: $prototype->class);
        $ctx->setMeta('analysis', 'prototype', $prototype);

        return $this->resolveContext(context: $ctx);
    }

    /**
     * Execute a callable with dependency injection.
     *
     * @param callable|string $callable   The target to invoke.
     * @param array           $parameters Runtime overrides.
     * @return mixed The invocation result.
     * @see docs/Core/Kernel/KernelRuntime.md#method-call
     */
    public function call(callable|string $callable, array $parameters = []): mixed
    {
        return $this->invoker->invoke(target: $callable, parameters: $parameters);
    }

    /**
     * Inject dependencies into an object.
     *
     * @param object $target The instance to hydrate.
     * @return object The hydrated instance.
     * @see docs/Core/Kernel/KernelRuntime.md#method-injectinto
     */
    public function injectInto(object $target): object
    {
        $ctx = new KernelContext(
            serviceId: self::INTERNAL_INJECT,
            manualInjection: true
        );
        $ctx->resolvedWith(instance: $target);
        $ctx->setMeta('inject', 'target', true);

        $this->pipeline->run(context: $ctx);

        return $ctx->getInstance();
    }
}
