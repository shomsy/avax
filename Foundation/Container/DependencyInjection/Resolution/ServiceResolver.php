<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Resolution;

use Avax\Container\ContainerInterface;
use Avax\Container\DependencyInjection\Errors\ContainerException;
use Avax\Container\DependencyInjection\Errors\ServiceNotFoundException;
use Avax\Container\DependencyInjection\Injection\InjectMethods;
use Avax\Container\DependencyInjection\Injection\InjectProperties;
use Avax\Container\DependencyInjection\Injection\InjectionReport;
use Avax\Container\DependencyInjection\Observability\ResolutionMetrics;
use Avax\Container\DependencyInjection\Observability\ResolutionTelemetry;
use Avax\Container\DependencyInjection\Observability\ResolutionTimeline;
use Avax\Container\DependencyInjection\Policies\ResolutionPolicy;
use Avax\Container\DependencyInjection\Registrations\ServiceRegistration;
use Avax\Container\DependencyInjection\Registrations\ServiceRegistry;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\ScopedLifetime;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\SharedLifetime;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\TransientLifetime;
use Avax\Container\DependencyInjection\Scopes\ManageScopes;
use Closure;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;
use Throwable;

final class ServiceResolver
{
    private ResolutionTelemetry $telemetry;

    private ContainerInterface|null $container = null;

    public function __construct(
        private readonly ServiceRegistry      $registrations,
        private readonly ManageScopes         $scopes,
        private readonly ResolvePlan          $plan,
        private readonly BuildService         $builder,
        private readonly CreateServiceBlueprint $blueprints,
        private readonly InjectProperties     $injectProperties,
        private readonly InjectMethods        $injectMethods,
        private readonly \Avax\Container\DependencyInjection\Calls\FunctionCaller $caller,
        ResolutionMetrics|null               $metrics = null,
        ResolutionTimeline|null              $timeline = null,
        ResolutionPolicy|null                $policy = null
    ) {
        $this->telemetry = new ResolutionTelemetry(
            metrics : $metrics ?? new ResolutionMetrics,
            timeline: $timeline ?? new ResolutionTimeline
        );
        $this->policy = $policy ?? new ResolutionPolicy;
    }

    private readonly ResolutionPolicy $policy;

    public function setContainer(ContainerInterface $container) : void
    {
        $this->container = $container;
        $this->caller->setResolver(resolver: $this);
    }

    public function registrations() : ServiceRegistry
    {
        return $this->registrations;
    }

    public function scopes() : ManageScopes
    {
        return $this->scopes;
    }

    public function telemetry() : ResolutionTelemetry
    {
        return $this->telemetry;
    }

    public function exportMetrics() : string
    {
        return $this->telemetry->exportMetrics();
    }

    public function has(string $id) : bool
    {
        if ($this->scopes->has(abstract: $id) || $this->registrations->has(abstract: $id)) {
            return true;
        }

        if (! class_exists($id)) {
            return false;
        }

        try {
            return (new ReflectionClass($id))->isInstantiable();
        } catch (Throwable) {
            return false;
        }
    }

    public function get(string $id) : mixed
    {
        return $this->resolveRequest(request: new ResolveRequest(serviceId: $id));
    }

    public function make(string $id, array $parameters = []) : object
    {
        $resolved = $this->resolveRequest(
            request: new ResolveRequest(serviceId: $id, overrides: $parameters)
        );

        if (! is_object($resolved)) {
            throw new ContainerException(message: "Service [{$id}] did not resolve to an object.");
        }

        return $resolved;
    }

    public function resolve(ServiceBlueprint $blueprint) : mixed
    {
        return $this->resolveRequest(request: new ResolveRequest(serviceId: $blueprint->class));
    }

    public function call(callable|string $callable, array $parameters = []) : mixed
    {
        $this->telemetry->metrics()->increment(name: 'container_calls_total');

        return $this->caller->call(target: $callable, parameters: $parameters);
    }

    public function injectInto(object $target) : object
    {
        $blueprint = $this->blueprints->createFor(class: $target::class);
        $request   = new ResolveRequest(serviceId: $target::class, manualInjection: true);

        $this->injectProperties->inject(
            target   : $target,
            blueprint: $blueprint,
            overrides: [],
            resolver : $this,
            request  : $request
        );
        $this->injectMethods->inject(
            target   : $target,
            blueprint: $blueprint,
            overrides: [],
            resolver : $this,
            request  : $request
        );

        $this->telemetry->metrics()->increment(name: 'container_injections_total');

        return $target;
    }

    public function canInject(object $target) : bool
    {
        $report = $this->inspectInjection(target: $target);

        return $report->injectedProperties !== [] || $report->injectedMethods !== [];
    }

    public function inspectInjection(object $target) : InjectionReport
    {
        $blueprint = $this->blueprints->createFor(class: $target::class);

        return new InjectionReport(
            target            : $target,
            injectedProperties: array_map(
                fn($property) => $this->propertyServiceId(property: $property),
                $blueprint->injectableProperties
            ),
            injectedMethods   : array_map(
                fn($method) => array_map(
                    fn($parameter) => $this->parameterServiceId(parameter: $parameter),
                    $method->getParameters()
                ),
                $blueprint->injectableMethods
            ),
            success           : $blueprint->injectableProperties !== [] || $blueprint->injectableMethods !== []
        );
    }

    public function instance(string $abstract, object $instance) : void
    {
        $this->scopes->instance(abstract: $abstract, instance: $instance);
        $this->registrations->instance(abstract: $abstract, instance: $instance);
    }

    public function openScope() : void
    {
        $this->scopes->open();
    }

    public function closeScope() : void
    {
        $this->scopes->close();
    }

    public function beginScope() : void
    {
        $this->openScope();
    }

    public function endScope() : void
    {
        $this->closeScope();
    }

    public function resolveRequest(ResolveRequest $request) : mixed
    {
        $this->telemetry->timeline()->record(
            action   : 'resolve',
            serviceId: $request->serviceId,
            outcome  : 'started'
        );
        $this->telemetry->metrics()->increment(name: 'container_resolve_total');

        try {
            if ($this->scopes->has(abstract: $request->serviceId)) {
                $this->telemetry->timeline()->record(
                    action   : 'resolve',
                    serviceId: $request->serviceId,
                    outcome  : 'cached'
                );
                $this->telemetry->metrics()->increment(name: 'container_resolve_cached_total');

                return $this->scopes->get(abstract: $request->serviceId);
            }

            if (! $request->manualInjection && ! $this->policy->isAllowed(abstract: $request->serviceId)) {
                throw new ContainerException(message: "Resolution blocked for [{$request->serviceId}] by policy.");
            }

            if ($request->contains(serviceId: $request->serviceId) && $request->parent !== null) {
                throw new ContainerException(message: "Circular dependency detected: {$request->getPath()}");
            }

            $registration = $this->registrationFor(request: $request);
            $candidate    = $this->candidateFor(request: $request, registration: $registration);

            if ($candidate === null) {
                throw new ServiceNotFoundException(
                    message: "Service [{$request->serviceId}] is not registered and cannot be autowired."
                );
            }

            $resolved = $this->evaluateCandidate(
                candidate   : $candidate,
                request     : $request,
                registration: $registration
            );

            $resolved = $this->applyExtenders(
                abstract: $request->serviceId,
                instance: $resolved
            );

            if (is_object($resolved)) {
                $this->storeResolved(
                    abstract    : $request->serviceId,
                    instance    : $resolved,
                    registration: $registration
                );
            }

            $this->telemetry->timeline()->record(
                action   : 'resolve',
                serviceId: $request->serviceId,
                outcome  : 'resolved'
            );

            return $resolved;
        } catch (Throwable $exception) {
            $this->telemetry->timeline()->record(
                action   : 'resolve',
                serviceId: $request->serviceId,
                outcome  : 'failed'
            );
            $this->telemetry->metrics()->increment(name: 'container_resolve_failures_total');

            throw $exception;
        }
    }

    private function registrationFor(ResolveRequest $request) : ServiceRegistration|null
    {
        $registration = $this->registrations->get(abstract: $request->serviceId);

        if ($registration !== null) {
            return $registration;
        }

        if (! class_exists($request->serviceId)) {
            return null;
        }

        $blueprint             = $this->blueprints->createFor(class: $request->serviceId);
        $registration          = new ServiceRegistration(abstract: $request->serviceId);
        $registration->concrete = $request->serviceId;
        $registration->lifetime = $blueprint->shared ? SharedLifetime::NAME : TransientLifetime::NAME;

        return $registration;
    }

    private function candidateFor(ResolveRequest $request, ServiceRegistration|null $registration) : mixed
    {
        $consumer = $request->parent?->serviceId ?? $request->consumer;
        if ($consumer !== null) {
            $contextual = $this->registrations->getContextualMatch(
                consumer: $consumer,
                needs   : $request->serviceId
            );
            if ($contextual !== null) {
                return $contextual;
            }
        }

        return $registration?->concrete;
    }

    private function evaluateCandidate(mixed $candidate, ResolveRequest $request, ServiceRegistration|null $registration) : mixed
    {
        if (is_object($candidate) && ! ($candidate instanceof Closure)) {
            return $candidate;
        }

        if ($candidate instanceof Closure) {
            return $this->invokeFactory(
                factory  : $candidate,
                overrides: array_merge($registration?->arguments ?? [], $request->overrides)
            );
        }

        if (is_string($candidate)) {
            if ($candidate !== $request->serviceId && $this->registrations->has(abstract: $candidate)) {
                return $this->resolveRequest(request: $request->child(serviceId: $candidate));
            }

            $resolved = $this->builder->build(
                class    : $candidate,
                resolver : $this,
                overrides: array_merge($registration?->arguments ?? [], $request->overrides),
                request  : $request
            );
            $blueprint = $this->blueprints->createFor(class: $candidate);

            $this->injectProperties->inject(
                target   : $resolved,
                blueprint: $blueprint,
                overrides: $request->overrides,
                resolver : $this,
                request  : $request
            );
            $this->injectMethods->inject(
                target   : $resolved,
                blueprint: $blueprint,
                overrides: $request->overrides,
                resolver : $this,
                request  : $request
            );

            return $resolved;
        }

        return $candidate;
    }

    private function invokeFactory(Closure $factory, array $overrides = []) : mixed
    {
        $reflection = new \ReflectionFunction($factory);
        $arguments  = [];

        if ($reflection->getNumberOfParameters() >= 1) {
            $arguments[] = $this->container ?? $this;
        }
        if ($reflection->getNumberOfParameters() >= 2) {
            $arguments[] = $overrides;
        }

        return $factory(...$arguments);
    }

    private function applyExtenders(string $abstract, mixed $instance) : mixed
    {
        foreach ($this->registrations->getExtenders(abstract: $abstract) as $extender) {
            if (! $extender instanceof Closure) {
                continue;
            }

            $reflection = new \ReflectionFunction($extender);
            $arguments  = [];

            if ($reflection->getNumberOfParameters() >= 1) {
                $arguments[] = $instance;
            }
            if ($reflection->getNumberOfParameters() >= 2) {
                $arguments[] = $this->container ?? $this;
            }

            $extended = $extender(...$arguments);
            if ($extended !== null) {
                $instance = $extended;
            }
        }

        return $instance;
    }

    private function storeResolved(string $abstract, object $instance, ServiceRegistration|null $registration) : void
    {
        $lifetime = $registration?->lifetime ?? TransientLifetime::NAME;

        if ($lifetime === SharedLifetime::NAME) {
            $this->scopes->instance(abstract: $abstract, instance: $instance);
            return;
        }

        if ($lifetime === ScopedLifetime::NAME) {
            $this->scopes->set(abstract: $abstract, instance: $instance);
        }
    }

    private function propertyServiceId(\ReflectionProperty $property) : string|null
    {
        $attributes = $property->getAttributes(
            \Avax\Container\DependencyInjection\Injection\Attributes\Inject::class
        );
        if ($attributes !== []) {
            $inject = $attributes[0]->newInstance();
            if (is_string($inject->abstract) && $inject->abstract !== '') {
                return $inject->abstract;
            }
        }

        $type = $property->getType();
        if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
            return $type->getName();
        }
        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $namedType) {
                if ($namedType instanceof ReflectionNamedType && ! $namedType->isBuiltin()) {
                    return $namedType->getName();
                }
            }
        }

        return null;
    }

    private function parameterServiceId(\ReflectionParameter $parameter) : string|null
    {
        $attributes = $parameter->getAttributes(
            \Avax\Container\DependencyInjection\Injection\Attributes\Inject::class
        );
        if ($attributes !== []) {
            $inject = $attributes[0]->newInstance();
            if (is_string($inject->abstract) && $inject->abstract !== '') {
                return $inject->abstract;
            }
        }

        $type = $parameter->getType();
        if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
            return $type->getName();
        }
        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $namedType) {
                if ($namedType instanceof ReflectionNamedType && ! $namedType->isBuiltin()) {
                    return $namedType->getName();
                }
            }
        }

        return null;
    }
}
