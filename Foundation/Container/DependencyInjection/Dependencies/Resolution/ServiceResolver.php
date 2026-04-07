<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Dependencies\Resolution;

use Avax\Container\Compilation\CompileContainer;
use Avax\Container\ContainerInterface;
use Avax\Container\Configuration\ContainerSettings;
use Avax\Container\Errors\ContainerException;
use Avax\Container\Errors\ServiceNotFoundException;
use Avax\Container\DependencyInjection\Dependencies\Blueprints\CreateServiceBlueprint;
use Avax\Container\DependencyInjection\Dependencies\Blueprints\ServiceBlueprint;
use Avax\Container\DependencyInjection\Injection\Methods\InjectMethods;
use Avax\Container\DependencyInjection\Injection\Properties\InjectProperties;
use Avax\Container\DependencyInjection\Injection\Reports\InjectionReport;
use Avax\Container\Observability\ResolutionMetrics;
use Avax\Container\Observability\ResolutionTelemetry;
use Avax\Container\Observability\ResolutionTimeline;
use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistration;
use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistry;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\ScopedLifetime;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\SharedLifetime;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\TransientLifetime;
use Avax\Container\DependencyInjection\Scopes\ManageScopes;
use Avax\Container\Runtime\HotPathInliner;
use Avax\Container\Runtime\LazyProxy;
use Closure;
use Throwable;

final class ServiceResolver
{
    private ResolutionTelemetry $telemetry;

    private ContainerInterface|null $container = null;

    private readonly ResolutionPolicy $policy;

    private readonly CompileContainer|null $compiler;

    private readonly HotPathInliner $inliner;

    private int $compiledRevision = -1;

    public function __construct(
        private readonly ServiceRegistry        $registrations,
        private readonly ManageScopes           $scopes,
        private readonly BuildService           $builder,
        private readonly CreateServiceBlueprint $blueprints,
        private readonly InjectProperties       $injectProperties,
        private readonly InjectMethods          $injectMethods,
        private readonly \Avax\Container\DependencyInjection\Injection\Invocation\FunctionCaller $caller,
        ResolutionMetrics|null                 $metrics = null,
        ResolutionTimeline|null                $timeline = null,
        ResolutionPolicy|null                  $policy = null,
        CompileContainer|null                  $compiler = null,
        HotPathInliner|null                    $inliner = null
    ) {
        $this->telemetry = new ResolutionTelemetry(
            metrics : $metrics ?? new ResolutionMetrics,
            timeline: $timeline ?? new ResolutionTimeline
        );
        $this->policy = $policy ?? new ResolutionPolicy;
        $this->compiler = $compiler;
        $this->inliner = $inliner ?? new HotPathInliner;
    }

    public function setContainer(ContainerInterface $container) : void
    {
        $this->container = $container;
        $this->caller->setResolver(resolver: $this);
    }

    public function registrations() : ServiceRegistry
    {
        return $this->registrations;
    }

    public function settings() : ContainerSettings
    {
        $settings = $this->registrations->get(abstract: ContainerSettings::class);
        if ($settings !== null && is_object($settings->concrete) && $settings->concrete instanceof ContainerSettings) {
            return $settings->concrete;
        }

        return new ContainerSettings;
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

    public function flush() : void
    {
        $this->blueprints->flush();
        $this->compiler?->flush();
        $this->inliner->detach();
        $this->registrations->flush();
        $this->scopes->terminate();
        $this->caller->clearCache();
        $this->telemetry->reset();
        $this->compiledRevision = -1;
    }

    public function reset() : void
    {
        $this->flush();
    }

    /**
     * @param list<string> $serviceIds
     * @return list<string>
     */
    public function validate(array $serviceIds = []) : array
    {
        $issues = [];

        foreach ($this->classesForValidation(serviceIds: $serviceIds) as $class) {
            try {
                $blueprint = $this->blueprints->createFor(class: $class);
                if (! $blueprint->instantiable) {
                    $issues[] = "Service [{$class}] is not instantiable.";
                }
            } catch (Throwable $throwable) {
                $issues[] = "Service [{$class}] cannot be analyzed: {$throwable->getMessage()}";
            }
        }

        foreach ($this->registrations->allAliases() as $alias => $target) {
            if (! $this->registrations->has(abstract: $target)) {
                $issues[] = "Alias [{$alias}] points to missing service [{$target}].";
            }
        }

        foreach ($this->registrations->all() as $abstract => $registration) {
            if ($registration->concrete === null) {
                $issues[] = "Service [{$abstract}] has no concrete target.";
            }
        }

        return array_values(array_unique($issues));
    }

    /**
     * @return array<string, mixed>
     */
    public function describeService(string $id) : array
    {
        $resolved = $this->registrations->resolveAlias(abstract: $id);
        $registration = $this->registrations->get(abstract: $resolved);
        $blueprint = $resolved !== '' && class_exists($resolved) ? $this->blueprints->createFor(class: $resolved) : null;

        return [
            'id' => $id,
            'resolvedId' => $resolved,
            'registered' => $registration !== null,
            'concrete' => is_object($registration?->concrete)
                ? $registration?->concrete::class
                : $registration?->concrete,
            'lifetime' => $registration?->lifetime,
            'deferred' => $registration?->deferred ?? false,
            'tags' => $registration?->tags ?? [],
            'aliases' => array_keys(array_filter(
                $this->registrations->allAliases(),
                static fn(string $target) : bool => $target === $resolved
            )),
            'blueprint' => $blueprint !== null ? [
                'instantiable' => $blueprint->instantiable,
                'shared' => $blueprint->shared,
                'constructor' => $blueprint->constructor?->parameters ?? [],
                'injectableProperties' => $blueprint->injectableProperties,
                'injectableMethods' => $blueprint->injectableMethods,
                'fingerprint' => $blueprint->fingerprint,
            ] : null,
            'compiled' => $this->inliner->has(serviceId: $resolved),
        ];
    }

    public function debugService(string $id) : array
    {
        return $this->describeService(id: $id);
    }

    /**
     * @return array<string, mixed>
     */
    public function debugPlan(string $id) : array
    {
        $resolved = $this->registrations->resolveAlias(abstract: $id);
        if ($resolved === '' || ! class_exists($resolved)) {
            return ['id' => $id, 'resolvedId' => $resolved, 'constructor' => null, 'methods' => []];
        }

        $blueprint = $this->blueprints->createFor(class: $resolved);

        return [
            'id' => $id,
            'resolvedId' => $resolved,
            'constructor' => $blueprint->constructor?->parameters ?? [],
            'methods' => $blueprint->injectableMethods,
            'properties' => $blueprint->injectableProperties,
            'shared' => $blueprint->shared,
            'deferred' => $this->registrations->get(abstract: $resolved)?->deferred ?? false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function debugTags(string $tag) : array
    {
        $ids = $this->registrations->getTaggedIds(tag: $tag);

        return [
            'tag' => $tag,
            'ids' => $ids,
            'services' => array_map(
                fn(string $id) => $this->describeService(id: $id),
                $ids
            ),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function debugAliases() : array
    {
        return $this->registrations->allAliases();
    }

    /**
     * @return array<string, mixed>
     */
    public function debugScope() : array
    {
        $snapshot = $this->scopes->snapshot();

        return [
            'shared' => $snapshot['shared'],
            'scoped' => $snapshot['scoped'],
        ];
    }

    public function env(string $key, mixed $default = null) : mixed
    {
        return $this->settings()->env(key: $key, default: $default);
    }

    public function has(string $id) : bool
    {
        $id = $this->registrations->resolveAlias(abstract: $id);

        if ($this->scopes->has(abstract: $id) || $this->registrations->has(abstract: $id)) {
            return true;
        }

        if (! class_exists($id)) {
            return false;
        }

        try {
            return $this->blueprints->createFor(class: $id)->instantiable;
        } catch (Throwable) {
            return false;
        }
    }

    public function get(string $id) : mixed
    {
        return $this->resolveRequest(request: new ResolveRequest(serviceId: $this->registrations->resolveAlias(abstract: $id)));
    }

    /**
     * @param array<string, mixed> $context
     */
    public function getInContext(string $id, array $context) : mixed
    {
        return $this->resolveRequest(
            request: (new ResolveRequest(serviceId: $this->registrations->resolveAlias(abstract: $id)))->withContext(context: $context)
        );
    }

    public function make(string $id, array $parameters = []) : object
    {
        $resolved = $this->resolveRequest(
            request: new ResolveRequest(
                serviceId: $this->registrations->resolveAlias(abstract: $id),
                overrides: $parameters
            )
        );

        if (! is_object($resolved)) {
            throw new ContainerException(message: "Service [{$id}] did not resolve to an object.");
        }

        return $resolved;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function makeInContext(string $id, array $parameters, array $context) : object
    {
        $resolved = $this->resolveRequest(
            request: (new ResolveRequest(
                serviceId: $this->registrations->resolveAlias(abstract: $id),
                overrides: $parameters
            ))->withContext(context: $context)
        );

        if (! is_object($resolved)) {
            throw new ContainerException(message: "Service [{$id}] did not resolve to an object.");
        }

        return $resolved;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function hasInContext(string $id, array $context) : bool
    {
        $request = (new ResolveRequest(serviceId: $this->registrations->resolveAlias(abstract: $id)))->withContext(context: $context);

        return $this->has(id: $request->serviceId);
    }

    public function call(callable|string $callable, array $parameters = []) : mixed
    {
        $this->telemetry->metrics()->increment(name: 'container_calls_total');

        return $this->caller->call(target: $callable, parameters: $parameters);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function callInContext(callable|string $callable, array $parameters, array $context) : mixed
    {
        $this->telemetry->metrics()->increment(name: 'container_calls_total');

        return $this->caller->call(
            target    : $callable,
            parameters: $parameters,
            request   : (new ResolveRequest(serviceId: $this->callableName(callable: $callable)))->withContext(context: $context)
        );
    }

    public function injectInto(object $target) : object
    {
        return $this->injectTarget(
            target : $target,
            request: new ResolveRequest(serviceId: $target::class, manualInjection: true)
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public function injectIntoInContext(object $target, array $context) : object
    {
        return $this->injectTarget(
            target : $target,
            request: (new ResolveRequest(serviceId: $target::class, manualInjection: true))->withContext(context: $context)
        );
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
                static fn(array $property) => $property['serviceId'],
                $blueprint->injectableProperties
            ),
            injectedMethods   : array_map(
                static fn(array $method) => array_map(
                    static fn(array $parameter) => $parameter['serviceId'],
                    $method['plan']->parameters
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
        $this->scopes->openScope();
    }

    public function closeScope() : void
    {
        $this->scopes->closeScope();
    }

    /**
     * @param list<string> $serviceIds
     */
    public function compileContainer(array $serviceIds = []) : void
    {
        $classes = $this->classesForWarmup(serviceIds: $serviceIds);
        $this->blueprints->warm(classes: $classes);

        if ($this->compiler !== null) {
            $this->inliner->attach($this->compiler->compile(serviceIds: $serviceIds));
            $this->compiledRevision = $this->registrations->revision();
        }

        $this->telemetry->metrics()->increment(name: 'container_compile_total');
    }

    /**
     * @param list<string> $serviceIds
     */
    public function warmCompiled(array $serviceIds = []) : void
    {
        $this->compileContainer(serviceIds: $serviceIds);
        $this->telemetry->metrics()->increment(name: 'container_compiled_warmups_total');
    }

    public function flushCompiled() : void
    {
        $this->blueprints->flush();
        $this->compiler?->flush();
        $this->inliner->detach();
        $this->compiledRevision = -1;
        $this->telemetry->metrics()->increment(name: 'container_compiled_flushes_total');
    }

    /**
     * @param list<string> $serviceIds
     */
    public function rebuildCompiled(array $serviceIds = []) : void
    {
        $this->flushCompiled();
        $this->compileContainer(serviceIds: $serviceIds);
        $this->telemetry->metrics()->increment(name: 'container_compiled_rebuilds_total');
    }

    public function tagged(string $tag) : array
    {
        return array_map(
            fn(string $serviceId) => $this->get(id: $serviceId),
            $this->registrations->getTaggedIds(tag: $tag)
        );
    }

    public function lazy(string $abstract) : LazyProxy
    {
        $serviceId = $this->registrations->resolveAlias(abstract: $abstract);

        return new LazyProxy(
            serviceId: $serviceId,
            factory  : fn() => $this->make(id: $serviceId)
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public function lazyInContext(string $abstract, array $context) : LazyProxy
    {
        $serviceId = $this->registrations->resolveAlias(abstract: $abstract);

        return new LazyProxy(
            serviceId: $serviceId,
            factory  : fn() => $this->makeInContext(id: $serviceId, parameters: [], context: $context)
        );
    }

    public function resolveRequest(ResolveRequest $request) : mixed
    {
        $request = $this->normalizeRequest(request: $request);

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
            $resolved = $this->shouldUseCompiled(request: $request)
                ? $this->resolveCompiledRequest(request: $request)
                : $this->resolveDynamicRequest(request: $request);

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

    public function resolveDynamicRequest(ResolveRequest $request) : mixed
    {
        $request = $this->normalizeRequest(request: $request);
        $registration = $this->registrationFor(request: $request);
        $candidate = $this->candidateFor(request: $request, registration: $registration);

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

        return $this->applyExtenders(
            abstract: $request->serviceId,
            instance: $resolved
        );
    }

    public function resolveCompiledDependency(string $serviceId, ResolveRequest $request) : mixed
    {
        return $this->resolveRequest(request: $request->child(serviceId: $serviceId));
    }

    /**
     * @param array<string, mixed> $context
     */
    public function resolveInContext(string $id, array $context, array $parameters = []) : mixed
    {
        return $this->resolveRequest(
            request: (new ResolveRequest(
                serviceId: $this->registrations->resolveAlias(abstract: $id),
                overrides: $parameters
            ))->withContext(context: $context)
        );
    }

    /**
     * @param array<string, mixed> $overrides
     */
    public function finishCompiledService(
        string $serviceId,
        object $instance,
        string $class,
        ResolveRequest $request,
        array $overrides = []
    ) : object {
        $blueprint = $this->blueprints->createFor(class: $class);

        if ($blueprint->injectableProperties !== []) {
            $this->injectProperties->inject(
                target   : $instance,
                blueprint: $blueprint,
                overrides: $overrides,
                resolver : $this,
                request  : $request
            );
        }

        if ($blueprint->injectableMethods !== []) {
            $this->injectMethods->inject(
                target   : $instance,
                blueprint: $blueprint,
                overrides: $overrides,
                resolver : $this,
                request  : $request
            );
        }

        $resolved = $this->applyExtenders(abstract: $serviceId, instance: $instance);

        if (! is_object($resolved)) {
            throw new ContainerException(message: "Compiled service [{$serviceId}] did not resolve to an object.");
        }

        return $resolved;
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
        $consumer = $this->contextualConsumer(request: $request);
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

    private function contextualConsumer(ResolveRequest $request) : string|null
    {
        return $request->parent?->serviceId ?? $request->consumer;
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

    private function isCompilable(string $serviceId) : bool
    {
        $registration = $this->registrations->get(abstract: $serviceId);
        $candidate = $registration?->concrete;

        if (is_string($candidate) && class_exists($candidate)) {
            return true;
        }

        if ($candidate instanceof Closure || is_object($candidate)) {
            return true;
        }

        return class_exists($serviceId);
    }

    /**
     * @param list<string> $serviceIds
     * @return list<string>
     */
    private function classesForWarmup(array $serviceIds) : array
    {
        $queue = [];

        if ($serviceIds !== []) {
            foreach (array_values(array_unique($serviceIds)) as $serviceId) {
                $queue[] = $serviceId;
            }
        } else {
            foreach ($this->registrations->all() as $registration) {
                if ($registration->deferred) {
                    continue;
                }

                $queue[] = $registration->abstract;
            }
        }

        $compiled = [];
        $classes = [];

        while ($queue !== []) {
            $serviceId = $this->registrations->resolveAlias(abstract: array_shift($queue));
            if (isset($compiled[$serviceId])) {
                continue;
            }

            if (! $this->isCompilable(serviceId: $serviceId)) {
                continue;
            }

            $compiled[$serviceId] = true;
            $classes[] = $serviceId;

            $registration = $this->registrations->get(abstract: $serviceId);
            $candidate = $registration?->concrete;

            if ($candidate === null && class_exists($serviceId)) {
                $candidate = $serviceId;
            }

            if (! is_string($candidate) || ! class_exists($candidate)) {
                continue;
            }

            $blueprint = $this->blueprints->createFor(class: $candidate);
            foreach ($this->dependenciesForWarmup(blueprint: $blueprint) as $dependency) {
                $queue[] = $dependency;
            }
        }

        return array_values(array_unique(array_filter(
            $classes,
            static fn(string $class) : bool => class_exists($class)
        )));
    }

    /**
     * @param list<string> $serviceIds
     * @return list<string>
     */
    private function classesForValidation(array $serviceIds) : array
    {
        $queue = [];

        if ($serviceIds !== []) {
            foreach (array_values(array_unique($serviceIds)) as $serviceId) {
                $queue[] = $serviceId;
            }
        } else {
            foreach ($this->registrations->all() as $registration) {
                $queue[] = $registration->abstract;
            }
        }

        $compiled = [];
        $classes = [];

        while ($queue !== []) {
            $serviceId = $this->registrations->resolveAlias(abstract: array_shift($queue));
            if (isset($compiled[$serviceId])) {
                continue;
            }

            if (! $this->isCompilable(serviceId: $serviceId)) {
                continue;
            }

            $compiled[$serviceId] = true;
            $classes[] = $serviceId;

            $registration = $this->registrations->get(abstract: $serviceId);
            $candidate = $registration?->concrete;

            if ($candidate === null && class_exists($serviceId)) {
                $candidate = $serviceId;
            }

            if (! is_string($candidate) || ! class_exists($candidate)) {
                continue;
            }

            $blueprint = $this->blueprints->createFor(class: $candidate);
            foreach ($this->dependenciesForWarmup(blueprint: $blueprint) as $dependency) {
                $queue[] = $dependency;
            }
        }

        return array_values(array_unique(array_filter(
            $classes,
            static fn(string $class) : bool => class_exists($class)
        )));
    }

    private function normalizeRequest(ResolveRequest $request) : ResolveRequest
    {
        $serviceId = $this->registrations->resolveAlias(abstract: $request->serviceId);
        if ($serviceId === $request->serviceId) {
            return $request;
        }

        return new ResolveRequest(
            serviceId       : $serviceId,
            overrides       : $request->overrides,
            context         : $request->context,
            parent          : $request->parent,
            manualInjection : $request->manualInjection,
            consumer        : $request->consumer
        );
    }

    private function refreshCompiledRuntime(string|null $serviceId = null) : void
    {
        if ($this->compiler === null) {
            return;
        }

        $revision = $this->registrations->revision();
        if ($revision === $this->compiledRevision && $this->inliner->isAttached()) {
            return;
        }

        $compiled = $this->compiler->load(
            serviceIds: $serviceId !== null ? [$serviceId] : []
        );
        if ($compiled !== null) {
            $this->inliner->attach($compiled);
        } else {
            $this->inliner->detach();
        }

        $this->compiledRevision = $revision;
    }

    private function shouldUseCompiled(ResolveRequest $request) : bool
    {
        $this->refreshCompiledRuntime(serviceId: $request->serviceId);

        if (! $this->inliner->has(serviceId: $request->serviceId)) {
            return false;
        }

        $consumer = $request->parent?->serviceId ?? $request->consumer;
        if ($consumer === null) {
            return true;
        }

        return $this->registrations->getContextualMatch(
            consumer: $consumer,
            needs   : $request->serviceId
        ) === null;
    }

    private function resolveCompiledRequest(ResolveRequest $request) : mixed
    {
        $this->telemetry->metrics()->increment(name: 'container_compiled_container_resolve_total');

        return $this->inliner->resolve(
            serviceId: $request->serviceId,
            resolver : $this,
            request  : $request
        );
    }

    public function defer(string $abstract, mixed $concrete = null) : ServiceRegistration
    {
        return $this->registrations->defer(abstract: $abstract, concrete: $concrete);
    }

    private function injectTarget(object $target, ResolveRequest $request) : object
    {
        $blueprint = $this->blueprints->createFor(class: $target::class);

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

    /**
     * @return list<string>
     */
    private function dependenciesForWarmup(ServiceBlueprint $blueprint) : array
    {
        $dependencies = [];

        foreach ($blueprint->constructor?->parameters ?? [] as $parameter) {
            if (is_string($parameter['serviceId'] ?? null)) {
                $dependencies[] = $parameter['serviceId'];
            }
        }

        foreach ($blueprint->injectableProperties ?? [] as $property) {
            if (is_string($property['serviceId'] ?? null)) {
                $dependencies[] = $property['serviceId'];
            }
        }

        foreach ($blueprint->injectableMethods ?? [] as $method) {
            foreach ($method['plan']->parameters as $parameter) {
                if (is_string($parameter['serviceId'] ?? null)) {
                    $dependencies[] = $parameter['serviceId'];
                }
            }
        }

        return array_values(array_unique($dependencies));
    }

    private function callableName(callable|string $callable) : string
    {
        if (is_string($callable)) {
            return 'call:' . $callable;
        }

        if (is_array($callable)) {
            $target = $callable[0] ?? null;
            $method = (string) ($callable[1] ?? '__invoke');

            if (is_object($target)) {
                return 'call:' . $target::class . '::' . $method;
            }

            if (is_string($target)) {
                return 'call:' . $target . '::' . $method;
            }
        }

        if ($callable instanceof Closure) {
            $reflection = new \ReflectionFunction($callable);

            return 'call:closure:' . ($reflection->getFileName() ?: 'internal')
                . ':' . $reflection->getStartLine()
                . ':' . $reflection->getEndLine();
        }

        if (is_object($callable)) {
            return 'call:' . $callable::class;
        }

        return 'call:unknown';
    }
}
