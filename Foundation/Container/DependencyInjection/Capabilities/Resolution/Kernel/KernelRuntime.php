<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Resolution\Kernel;

use Avax\Container\DependencyInjection\Capabilities\Invocation\InvokeAction;
use Avax\Container\DependencyInjection\Capabilities\Prototypes\Model\ServicePrototype;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Errors\ResolutionException;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Pipeline\ResolutionPipeline;

/**
 * Execution layer that runs the resolution pipeline and callable invocation.
 */
final readonly class KernelRuntime
{
    private const string INTERNAL_INJECT = '__inject__';

    public function __construct(
        private ResolutionPipeline $pipeline,
        private InvokeAction       $invoker
    ) {}

    public function get(string $id) : mixed
    {
        return $this->resolveContext(context: new KernelContext(serviceId: $id));
    }

    public function resolveContext(KernelContext $context) : mixed
    {
        $this->pipeline->run(context: $context);

        if ($context->getInstance() === null) {
            throw new ResolutionException(
                message: "Service '{$context->serviceId}' not resolved by pipeline at path: {$context->getPath()}"
            );
        }

        return $context->getInstance();
    }

    public function make(string $id, array $parameters = []) : object
    {
        return $this->resolveContext(context: new KernelContext(
            serviceId: $id,
            overrides: $parameters
        ));
    }

    public function resolve(ServicePrototype $prototype) : mixed
    {
        $context = new KernelContext(serviceId: $prototype->class);
        $context->setMeta(namespace: 'analysis', key: 'prototype', value: $prototype);

        return $this->resolveContext(context: $context);
    }

    public function call(callable|string $callable, array $parameters = []) : mixed
    {
        return $this->invoker->invoke(target: $callable, parameters: $parameters);
    }

    public function injectInto(object $target) : object
    {
        $context = new KernelContext(
            serviceId      : self::INTERNAL_INJECT,
            manualInjection: true
        );
        $context->resolvedWith(instance: $target);
        $context->setMeta(namespace: 'inject', key: 'target', value: true);

        $this->pipeline->run(context: $context);

        return $context->getInstance();
    }
}
