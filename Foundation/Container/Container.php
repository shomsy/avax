<?php

declare(strict_types=1);

namespace Avax\Container;

use Avax\Container\Compilation\CompileReport;
use Avax\Container\DependencyInjection\Dependencies\Bindings\DecoratorInterface;
use Avax\Container\DependencyInjection\Flows\CallFunction;
use Avax\Container\DependencyInjection\Flows\BootProviders;
use Avax\Container\DependencyInjection\Flows\CloseScope;
use Avax\Container\DependencyInjection\Injection\Reports\InjectionReport;
use Avax\Container\DependencyInjection\Flows\OpenScope;
use Avax\Container\DependencyInjection\Flows\RegisterServices;
use Avax\Container\DependencyInjection\Dependencies\Bindings\RegisterForTarget;
use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistration;
use Avax\Container\DependencyInjection\Dependencies\Providers\ServiceProviderInterface;
use Avax\Container\DependencyInjection\Flows\ResolveService;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ServiceResolver;
use Avax\Container\Observability\RuntimeReport;
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

    public function flush() : void
    {
        $this->resolver->flush();
    }

    public function reset() : void
    {
        $this->resolver->reset();
    }

    public function instance(string $abstract, object $instance) : void
    {
        $this->registerServices()->instance(abstract: $abstract, instance: $instance);
    }

    /**
     * @param array<int, string|ServiceProviderInterface> $providers
     */
    public function bootProviders(array $providers) : void
    {
        (new BootProviders(container: $this))->boot(providers: $providers);
    }

    /**
     * @param list<string> $serviceIds
     * @return list<string>
     */
    public function validate(array $serviceIds = []) : array
    {
        return $this->resolver->validate(serviceIds: $serviceIds);
    }

    /**
     * @return array<string, mixed>
     */
    public function describeService(string $id) : array
    {
        return $this->resolver->describeService(id: $id);
    }

    /**
     * @return array<string, mixed>
     */
    public function debugService(string $id) : array
    {
        return $this->resolver->debugService(id: $id);
    }

    /**
     * @return array<string, mixed>
     */
    public function debugPlan(string $id) : array
    {
        return $this->resolver->debugPlan(id: $id);
    }

    /**
     * @return array<string, mixed>
     */
    public function debugTags(string $tag) : array
    {
        return $this->resolver->debugTags(tag: $tag);
    }

    /**
     * @return array<string, string>
     */
    public function debugAliases() : array
    {
        return $this->resolver->debugAliases();
    }

    /**
     * @return array<string, mixed>
     */
    public function debugScope() : array
    {
        return $this->resolver->debugScope();
    }

    public function env(string $key, mixed $default = null) : mixed
    {
        return $this->resolver->env(key: $key, default: $default);
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

    public function compileReport(array $serviceIds = []) : CompileReport|null
    {
        return $this->resolver->compileReport(serviceIds: $serviceIds);
    }

    public function runtimeReport() : RuntimeReport
    {
        return $this->resolver->runtimeReport();
    }

    public function hasAlias(string $alias) : bool
    {
        return $this->resolver->hasAlias(alias: $alias);
    }

    public function isDeferred(string $id) : bool
    {
        return $this->resolver->isDeferred(id: $id);
    }

    public function isLazy(string $id) : bool
    {
        return $this->resolver->isLazy(id: $id);
    }

    public function isCompiled(string $id) : bool
    {
        return $this->resolver->isCompiled(id: $id);
    }

    public function isWarmedUp() : bool
    {
        return $this->resolver->isWarmedUp();
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

    public function decorate(string $abstract, callable|DecoratorInterface|string $decorator) : void
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

    public function defer(string $abstract, mixed $concrete = null) : ServiceRegistration
    {
        return $this->registerServices()->defer(abstract: $abstract, concrete: $concrete);
    }

    public function forContext(array $context) : ContainerInterface
    {
        if ($context === []) {
            return $this;
        }

        return new class($this, $this->resolver, $context) implements ContainerInterface {
            public function __construct(
                private Container $base,
                private ServiceResolver $resolver,
                private array $context
            ) {}

            public function get(string $id) : mixed
            {
                return $this->resolver->getInContext(id: $id, context: $this->context);
            }

            public function has(string $id) : bool
            {
                return $this->resolver->hasInContext(id: $id, context: $this->context);
            }

            public function make(string $abstract, array $parameters = []) : object
            {
                return $this->resolver->makeInContext(id: $abstract, parameters: $parameters, context: $this->context);
            }

            public function call(callable|string $callable, array $parameters = []) : mixed
            {
                return $this->resolver->callInContext(
                    callable : $callable,
                    parameters: $parameters,
                    context   : $this->context
                );
            }

            public function injectInto(object $target) : object
            {
                return $this->resolver->injectIntoInContext(target: $target, context: $this->context);
            }

            public function canInject(object $target) : bool
            {
                return $this->base->canInject(target: $target);
            }

            public function inspectInjection(object $target) : InjectionReport
            {
                return $this->base->inspectInjection(target: $target);
            }

            public function flush() : void
            {
                $this->base->flush();
            }

            public function reset() : void
            {
                $this->base->reset();
            }

            public function bootProviders(array $providers) : void
            {
                $this->base->bootProviders(providers: $providers);
            }

            public function validate(array $serviceIds = []) : array
            {
                return $this->base->validate(serviceIds: $serviceIds);
            }

            public function describeService(string $id) : array
            {
                return $this->base->describeService(id: $id);
            }

            public function debugService(string $id) : array
            {
                return $this->base->debugService(id: $id);
            }

            public function debugPlan(string $id) : array
            {
                return $this->base->debugPlan(id: $id);
            }

            public function debugTags(string $tag) : array
            {
                return $this->base->debugTags(tag: $tag);
            }

            public function debugAliases() : array
            {
                return $this->base->debugAliases();
            }

            public function debugScope() : array
            {
                return $this->base->debugScope();
            }

            public function env(string $key, mixed $default = null) : mixed
            {
                return $this->base->env(key: $key, default: $default);
            }

            public function openScope() : void
            {
                $this->base->openScope();
            }

            public function closeScope() : void
            {
                $this->base->closeScope();
            }

            public function compileContainer(array $serviceIds = []) : void
            {
                $this->base->compileContainer(serviceIds: $serviceIds);
            }

            public function warmCompiled(array $serviceIds = []) : void
            {
                $this->base->warmCompiled(serviceIds: $serviceIds);
            }

            public function flushCompiled() : void
            {
                $this->base->flushCompiled();
            }

            public function rebuildCompiled(array $serviceIds = []) : void
            {
                $this->base->rebuildCompiled(serviceIds: $serviceIds);
            }

            public function compileReport(array $serviceIds = []) : CompileReport|null
            {
                return $this->base->compileReport(serviceIds: $serviceIds);
            }

            public function runtimeReport() : RuntimeReport
            {
                return $this->base->runtimeReport();
            }

            public function hasAlias(string $alias) : bool
            {
                return $this->base->hasAlias(alias: $alias);
            }

            public function isDeferred(string $id) : bool
            {
                return $this->base->isDeferred(id: $id);
            }

            public function isLazy(string $id) : bool
            {
                return $this->base->isLazy(id: $id);
            }

            public function isCompiled(string $id) : bool
            {
                return $this->base->isCompiled(id: $id);
            }

            public function isWarmedUp() : bool
            {
                return $this->base->isWarmedUp();
            }

            public function scopes() : ScopeInterface
            {
                return $this->base->scopes();
            }

            public function tagged(string $tag) : array
            {
                return $this->base->tagged(tag: $tag);
            }

            public function lazy(string $abstract) : LazyProxy
            {
                return $this->resolver->lazyInContext(abstract: $abstract, context: $this->context);
            }

            public function exportMetrics() : string
            {
                return $this->base->exportMetrics();
            }

            public function alias(string $alias, string $abstract) : void
            {
                $this->base->alias(alias: $alias, abstract: $abstract);
            }

            public function bind(string $abstract, mixed $concrete = null) : ServiceRegistration
            {
                return $this->base->bind(abstract: $abstract, concrete: $concrete);
            }

            public function defer(string $abstract, mixed $concrete = null) : ServiceRegistration
            {
                return $this->base->defer(abstract: $abstract, concrete: $concrete);
            }

            public function singleton(string $abstract, mixed $concrete = null) : ServiceRegistration
            {
                return $this->base->singleton(abstract: $abstract, concrete: $concrete);
            }

            public function scoped(string $abstract, mixed $concrete = null) : ServiceRegistration
            {
                return $this->base->scoped(abstract: $abstract, concrete: $concrete);
            }

            public function instance(string $abstract, object $instance) : void
            {
                $this->base->instance(abstract: $abstract, instance: $instance);
            }

            public function extend(string $abstract, callable $closure) : void
            {
                $this->base->extend(abstract: $abstract, closure: $closure);
            }

            public function decorate(string $abstract, callable|DecoratorInterface|string $decorator) : void
            {
                $this->base->decorate(abstract: $abstract, decorator: $decorator);
            }

            public function when(string $consumer) : RegisterForTarget
            {
                return $this->base->when(consumer: $consumer);
            }

            public function tag(string|array $abstracts, string|array $tags) : void
            {
                $this->base->tag(abstracts: $abstracts, tags: $tags);
            }

            public function forContext(array $context) : ContainerInterface
            {
                return $this->base->forContext(array_replace($this->context, $context));
            }
        };
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
