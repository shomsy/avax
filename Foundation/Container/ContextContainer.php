<?php

declare(strict_types=1);

namespace Avax\Container;

use Avax\Container\Compilation\CompileReport;
use Avax\Container\DependencyInjection\Dependencies\Bindings\DecoratorInterface;
use Avax\Container\DependencyInjection\Dependencies\Bindings\RegisterForTarget;
use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistration;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ServiceResolver;
use Avax\Container\DependencyInjection\Dependencies\Providers\ServiceProviderInterface;
use Avax\Container\DependencyInjection\Injection\Reports\InjectionReport;
use Avax\Container\DependencyInjection\Scopes\ScopeInterface;
use Avax\Container\Observability\RuntimeReport;
use Avax\Container\Runtime\LazyProxy;

/**
 * Context-aware facade over the same underlying container runtime.
 */
final readonly class ContextContainer implements ContainerInterface
{
    /**
     * @param array<string, mixed> $context
     */
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
            callable  : $callable,
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

    /**
     * @param array<int, string|ServiceProviderInterface> $providers
     */
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

    /**
     * @param array<string, mixed> $context
     */
    public function forContext(array $context) : ContainerInterface
    {
        if ($context === []) {
            return $this;
        }

        return new self(
            base    : $this->base,
            resolver: $this->resolver,
            context : array_replace($this->context, $context)
        );
    }
}
