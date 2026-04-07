<?php

declare(strict_types=1);

namespace Avax\Container;

use Avax\Container\DependencyInjection\Flows\CallFunction;
use Avax\Container\DependencyInjection\Flows\CloseScope;
use Avax\Container\DependencyInjection\Injection\Reports\InjectionReport;
use Avax\Container\DependencyInjection\Flows\OpenScope;
use Avax\Container\DependencyInjection\Flows\RegisterServices;
use Avax\Container\DependencyInjection\Dependencies\Bindings\RegisterForTarget;
use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistration;
use Avax\Container\DependencyInjection\Flows\ResolveService;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ServiceResolver;
use Avax\Container\DependencyInjection\Scopes\ScopeInterface;
use Avax\Container\Runtime\LazyProxy;

/**
 * Stable public facade for the container component.
 */
final readonly class Container implements ContainerInterface
{
    public function __construct(
        private ServiceResolver $resolver
    ) {}

    public function get(string $id) : mixed
    {
        return $this->resolveService()->get(id: $id);
    }

    public function has(string $id) : bool
    {
        return $this->resolver->has(id: $id);
    }

    public function make(string $abstract, array $parameters = []) : object
    {
        return $this->resolveService()->make(abstract: $abstract, parameters: $parameters);
    }

    public function call(callable|string $callable, array $parameters = []) : mixed
    {
        return $this->callFunction()->call(target: $callable, parameters: $parameters);
    }

    public function injectInto(object $target) : object
    {
        return $this->resolver->injectInto(target: $target);
    }

    public function instance(string $abstract, object $instance) : void
    {
        $this->registerServices()->instance(abstract: $abstract, instance: $instance);
    }

    public function openScope() : void
    {
        $this->openScopeFlow()->open();
    }

    public function closeScope() : void
    {
        $this->closeScopeFlow()->close();
    }

    public function compileContainer(array $serviceIds = []) : void
    {
        $this->resolver->compileContainer(serviceIds: $serviceIds);
    }

    public function warmCompiled(array $serviceIds = []) : void
    {
        $this->resolver->warmCompiled(serviceIds: $serviceIds);
    }

    public function flushCompiled() : void
    {
        $this->resolver->flushCompiled();
    }

    public function rebuildCompiled(array $serviceIds = []) : void
    {
        $this->resolver->rebuildCompiled(serviceIds: $serviceIds);
    }

    public function canInject(object $target) : bool
    {
        $report = $this->inspectInjection(target: $target);

        return ! empty($report->injectedProperties) || ! empty($report->injectedMethods);
    }

    public function inspectInjection(object $target) : InjectionReport
    {
        return $this->resolver->inspectInjection(target: $target);
    }

    public function scopes() : ScopeInterface
    {
        return $this->resolver->scopes();
    }

    public function exportMetrics() : string
    {
        return $this->resolver->exportMetrics();
    }

    public function alias(string $alias, string $abstract) : void
    {
        $this->registerServices()->alias(alias: $alias, abstract: $abstract);
    }

    public function bind(string $abstract, mixed $concrete = null) : ServiceRegistration
    {
        return $this->registerServices()->bind(abstract: $abstract, concrete: $concrete);
    }

    public function singleton(string $abstract, mixed $concrete = null) : ServiceRegistration
    {
        return $this->registerServices()->singleton(abstract: $abstract, concrete: $concrete);
    }

    public function scoped(string $abstract, mixed $concrete = null) : ServiceRegistration
    {
        return $this->registerServices()->scoped(abstract: $abstract, concrete: $concrete);
    }

    public function when(string $consumer) : RegisterForTarget
    {
        return $this->registerServices()->when(consumer: $consumer);
    }

    public function extend(string $abstract, callable $closure) : void
    {
        $this->registerServices()->extend(abstract: $abstract, closure: $closure);
    }

    public function decorate(string $abstract, callable|object|string $decorator) : void
    {
        $this->registerServices()->decorate(abstract: $abstract, decorator: $decorator);
    }

    public function tag(string|array $abstracts, string|array $tags) : void
    {
        $this->registerServices()->tag(abstracts: $abstracts, tags: $tags);
    }

    public function tagged(string $tag) : array
    {
        return $this->resolver->tagged(tag: $tag);
    }

    public function lazy(string $abstract) : LazyProxy
    {
        return $this->resolver->lazy(abstract: $abstract);
    }

    private function registerServices() : RegisterServices
    {
        return new RegisterServices(resolver: $this->resolver);
    }

    private function resolveService() : ResolveService
    {
        return new ResolveService(resolver: $this->resolver);
    }

    private function callFunction() : CallFunction
    {
        return new CallFunction(resolver: $this->resolver);
    }

    private function openScopeFlow() : OpenScope
    {
        return new OpenScope(resolver: $this->resolver);
    }

    private function closeScopeFlow() : CloseScope
    {
        return new CloseScope(resolver: $this->resolver);
    }
}
