<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Resolution\Kernel;

use Avax\Container\InjectionReport;
use Avax\Container\DependencyInjection\Capability\Definitions\Store\DefinitionStore;
use Avax\Container\DependencyInjection\Capability\Definitions\Store\ServiceDefinition;
use Avax\Container\DependencyInjection\Capability\Observability\Telemetry\Telemetry;
use Avax\Container\DependencyInjection\Capability\Prototypes\Model\ServicePrototype;
use Avax\Container\DependencyInjection\Capability\Resolution\Errors\ContainerException;
use Avax\Container\DependencyInjection\Capability\Scopes\Lifetimes\ServiceLifetime;
use Avax\Container\DependencyInjection\Capability\Scopes\ScopeManager;
use Avax\Container\DependencyInjection\Configuration\KernelConfig;
use ReflectionClass;
use Throwable;

/**
 * Internal facade over kernel runtime, state, and shared stores.
 */
final readonly class KernelFacade
{
    public function __construct(
        private DefinitionStore $definitions,
        private KernelConfig $config,
        private KernelRuntime $runtime,
        private KernelState $state
    ) {}

    public function has(string $id) : bool
    {
        if ($this->definitions->has(abstract: $id) || $this->scopes()->has(abstract: $id)) {
            return true;
        }

        if (! class_exists(class: $id)) {
            return false;
        }

        try {
            return (new ReflectionClass(objectOrClass: $id))->isInstantiable();
        } catch (Throwable) {
            return false;
        }
    }

    public function resolveContext(KernelContext $context) : mixed
    {
        if ($this->scopes()->has(abstract: $context->serviceId)) {
            return $this->scopes()->get(abstract: $context->serviceId);
        }

        return $this->runtime->resolveContext(context: $context);
    }

    public function get(string $id) : mixed
    {
        if ($this->scopes()->has(abstract: $id)) {
            return $this->scopes()->get(abstract: $id);
        }

        return $this->runtime->get(id: $id);
    }

    public function instance(string $abstract, object $instance) : void
    {
        $this->scopes()->instance(abstract: $abstract, instance: $instance);

        $definition = $this->definitions->get(abstract: $abstract) ?? new ServiceDefinition(abstract: $abstract);
        $definition->concrete = $instance;
        $definition->lifetime = ServiceLifetime::Singleton;

        $this->definitions->add(definition: $definition);
    }

    public function make(string $id, array $parameters = []) : object
    {
        return $this->runtime->make(id: $id, parameters: $parameters);
    }

    public function call(callable|string $callable, array $parameters = []) : mixed
    {
        return $this->runtime->call(callable: $callable, parameters: $parameters);
    }

    public function injectInto(object $target) : object
    {
        return $this->runtime->injectInto(target: $target);
    }

    public function beginScope() : void
    {
        $this->scopes()->beginScope();
    }

    public function endScope() : void
    {
        $this->scopes()->endScope();
    }

    public function canInject(object $target) : bool
    {
        $report = $this->inspectInjection(target: $target);

        return $report->injectedProperties !== [] || $report->injectedMethods !== [];
    }

    public function inspectInjection(object|null $target = null) : InjectionReport
    {
        if ($target === null) {
            throw new ContainerException(message: 'inspectInjection requires a target object.');
        }

        $prototype = $this->config->prototypeFactory->createFor(class: $target::class);

        $properties = array_map(static fn($property) => $property->type ?? 'mixed', $prototype->injectedProperties);
        $methods    = array_map(
            static fn($method) => array_map(static fn($parameter) => $parameter->type ?? 'mixed', $method->parameters),
            $prototype->injectedMethods
        );

        return new InjectionReport(
            target            : $target,
            injectedProperties: $properties,
            injectedMethods   : $methods,
            success           : $properties !== [] || $methods !== []
        );
    }

    public function exportMetrics() : string
    {
        return $this->telemetry()->exportMetrics();
    }

    public function telemetry() : Telemetry
    {
        return $this->state->getOrInit(
            property: 'telemetry',
            factory : fn() => new Telemetry(metrics: $this->config->metrics)
        );
    }

    public function definitions() : DefinitionStore
    {
        return $this->definitions;
    }

    public function scopes() : ScopeManager
    {
        return $this->config->scopes;
    }

    public function resolve(ServicePrototype $prototype) : mixed
    {
        return $this->runtime->resolve(prototype: $prototype);
    }
}
