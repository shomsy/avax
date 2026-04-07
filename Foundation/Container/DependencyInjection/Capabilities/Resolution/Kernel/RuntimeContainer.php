<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Resolution\Kernel;

use Avax\Container\BindingBuilderInterface;
use Avax\Container\ContainerInterface;
use Avax\Container\ContextBuilderInterface;
use Avax\Container\InjectionReport;
use Avax\Container\ScopeManagerInterface;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Contracts\ContainerRuntimeInterface;

/**
 * Internal runtime facade that keeps nested resolution chains off the public facade.
 */
final readonly class RuntimeContainer implements ContainerRuntimeInterface
{
    public function __construct(
        private ContainerInterface $container,
        private ContainerKernel $kernel
    ) {}

    public function get(string $id) : mixed
    {
        return $this->container->get(id: $id);
    }

    public function has(string $id) : bool
    {
        return $this->container->has(id: $id);
    }

    public function make(string $abstract, array $parameters = []) : object
    {
        return $this->container->make(abstract: $abstract, parameters: $parameters);
    }

    public function call(callable|string $callable, array $parameters = []) : mixed
    {
        return $this->container->call(callable: $callable, parameters: $parameters);
    }

    public function injectInto(object $target) : object
    {
        return $this->container->injectInto(target: $target);
    }

    public function canInject(object $target) : bool
    {
        return $this->container->canInject(target: $target);
    }

    public function inspectInjection(object|null $target = null) : InjectionReport
    {
        return $this->container->inspectInjection(target: $target);
    }

    public function beginScope() : void
    {
        $this->container->beginScope();
    }

    public function endScope() : void
    {
        $this->container->endScope();
    }

    public function scopes() : ScopeManagerInterface
    {
        return $this->container->scopes();
    }

    public function exportMetrics() : string
    {
        return $this->container->exportMetrics();
    }

    public function bind(string $abstract, mixed $concrete = null) : BindingBuilderInterface
    {
        return $this->container->bind(abstract: $abstract, concrete: $concrete);
    }

    public function singleton(string $abstract, mixed $concrete = null) : BindingBuilderInterface
    {
        return $this->container->singleton(abstract: $abstract, concrete: $concrete);
    }

    public function scoped(string $abstract, mixed $concrete = null) : BindingBuilderInterface
    {
        return $this->container->scoped(abstract: $abstract, concrete: $concrete);
    }

    public function instance(string $abstract, object $instance) : void
    {
        $this->container->instance(abstract: $abstract, instance: $instance);
    }

    public function extend(string $abstract, callable $closure) : void
    {
        $this->container->extend(abstract: $abstract, closure: $closure);
    }

    public function when(string $consumer) : ContextBuilderInterface
    {
        return $this->container->when(consumer: $consumer);
    }

    public function tag(string|array $abstracts, string|array $tags) : void
    {
        $this->container->tag(abstracts: $abstracts, tags: $tags);
    }

    public function resolveContext(KernelContext $context) : mixed
    {
        return $this->kernel->resolveContext(context: $context);
    }
}
