<?php

declare(strict_types=1);

namespace Avax\Container;

use Avax\Container\DependencyInjection\CallFunction;
use Avax\Container\DependencyInjection\CloseScope;
use Avax\Container\DependencyInjection\Injection\InjectionReport;
use Avax\Container\DependencyInjection\OpenScope;
use Avax\Container\DependencyInjection\RegisterServices;
use Avax\Container\DependencyInjection\Registrations\RegisterForTarget;
use Avax\Container\DependencyInjection\Registrations\ServiceRegistration;
use Avax\Container\DependencyInjection\ResolveService;
use Avax\Container\DependencyInjection\Resolution\ServiceResolver;
use Avax\Container\DependencyInjection\Scopes\ScopeInterface;

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

    public function beginScope() : void
    {
        $this->openScope()->open();
    }

    public function endScope() : void
    {
        $this->closeScope()->close();
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

    public function tag(string|array $abstracts, string|array $tags) : void
    {
        $this->registerServices()->tag(abstracts: $abstracts, tags: $tags);
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

    private function openScope() : OpenScope
    {
        return new OpenScope(resolver: $this->resolver);
    }

    private function closeScope() : CloseScope
    {
        return new CloseScope(resolver: $this->resolver);
    }
}
