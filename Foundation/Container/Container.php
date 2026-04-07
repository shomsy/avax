<?php

declare(strict_types=1);

namespace Avax\Container;

use Avax\Container\DependencyInjection\Capability\Resolution\Errors\ContainerException;
use Avax\Container\DependencyInjection\Capability\Resolution\Kernel\ContainerKernel;
use Avax\Container\DependencyInjection\Flow\BeginScope\BeginScope;
use Avax\Container\DependencyInjection\Flow\EndScope\EndScope;
use Avax\Container\DependencyInjection\Flow\InvokeCallable\InvokeCallable;
use Avax\Container\DependencyInjection\Flow\RegisterBindings\RegisterBindings;
use Avax\Container\DependencyInjection\Flow\ResolveService\ResolveService;

/**
 * Stable public facade for the container component.
 */
final readonly class Container implements ContainerInterface
{
    public function __construct(
        private ContainerKernel $kernel
    ) {}

    public function get(string $id) : mixed
    {
        return $this->resolveService()->get(id: $id);
    }

    public function has(string $id) : bool
    {
        return $this->kernel->has(id: $id);
    }

    public function make(string $abstract, array $parameters = []) : object
    {
        return $this->resolveService()->make(abstract: $abstract, parameters: $parameters);
    }

    public function call(callable|string $callable, array $parameters = []) : mixed
    {
        return $this->invokeCallable()->call(target: $callable, parameters: $parameters);
    }

    public function injectInto(object $target) : object
    {
        return $this->kernel->injectInto(target: $target);
    }

    public function instance(string $abstract, object $instance) : void
    {
        $this->registerBindings()->instance(abstract: $abstract, instance: $instance);
    }

    public function beginScope() : void
    {
        $this->beginScopeFlow()->begin();
    }

    public function endScope() : void
    {
        $this->endScopeFlow()->end();
    }

    public function canInject(object $target) : bool
    {
        $report = $this->inspectInjection(target: $target);

        return ! empty($report->injectedProperties) || ! empty($report->injectedMethods);
    }

    public function inspectInjection(object|null $target = null) : InjectionReport
    {
        if ($target === null) {
            throw new ContainerException(message: 'inspectInjection requires a target object.');
        }

        return $this->kernel->inspectInjection(target: $target);
    }

    public function scopes() : ScopeManagerInterface
    {
        return $this->kernel->scopes();
    }

    public function exportMetrics() : string
    {
        return $this->kernel->exportMetrics();
    }

    public function bind(string $abstract, mixed $concrete = null) : BindingBuilderInterface
    {
        return $this->registerBindings()->bind(abstract: $abstract, concrete: $concrete);
    }

    public function singleton(string $abstract, mixed $concrete = null) : BindingBuilderInterface
    {
        return $this->registerBindings()->singleton(abstract: $abstract, concrete: $concrete);
    }

    public function scoped(string $abstract, mixed $concrete = null) : BindingBuilderInterface
    {
        return $this->registerBindings()->scoped(abstract: $abstract, concrete: $concrete);
    }

    public function when(string $consumer) : ContextBuilderInterface
    {
        return $this->registerBindings()->when(consumer: $consumer);
    }

    public function extend(string $abstract, callable $closure) : void
    {
        $this->registerBindings()->extend(abstract: $abstract, closure: $closure);
    }

    public function tag(string|array $abstracts, string|array $tags) : void
    {
        $this->registerBindings()->tag(abstracts: $abstracts, tags: $tags);
    }

    private function registerBindings() : RegisterBindings
    {
        return new RegisterBindings(kernel: $this->kernel);
    }

    private function resolveService() : ResolveService
    {
        return new ResolveService(kernel: $this->kernel);
    }

    private function invokeCallable() : InvokeCallable
    {
        return new InvokeCallable(kernel: $this->kernel);
    }

    private function beginScopeFlow() : BeginScope
    {
        return new BeginScope(kernel: $this->kernel);
    }

    private function endScopeFlow() : EndScope
    {
        return new EndScope(kernel: $this->kernel);
    }
}
