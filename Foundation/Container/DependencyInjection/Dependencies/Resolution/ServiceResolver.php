<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Dependencies\Resolution;

use Avax\Container\Compilation\CompileReport;
use Avax\Container\ContainerInterface;
use Avax\Container\Configuration\CreateContainerConfig;
use Avax\Container\Configuration\ContainerSettings;
use Avax\Container\Errors\ContainerException;
use Avax\Container\Errors\ServiceNotFoundException;
use Avax\Container\DependencyInjection\Dependencies\Blueprints\CreateServiceBlueprint;
use Avax\Container\DependencyInjection\Dependencies\Blueprints\ServiceBlueprint;
use Avax\Container\DependencyInjection\Dependencies\Providers\DeferredProviderRegistry;
use Avax\Container\DependencyInjection\Dependencies\Providers\ServiceProviderInterface;
use Avax\Container\DependencyInjection\Injection\Invocation\FunctionCaller;
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
use Avax\Container\Runtime\LazyProxy;
use Avax\Container\Observability\RuntimeReport;
use Closure;
use Throwable;

/**
 * Owns service resolution, compilation state, scopes, and runtime diagnostics.
 */
final class ServiceResolver
{
    private ResolutionTelemetry $telemetry;

    private ContainerInterface|null $container = null;

    private readonly ResolutionPolicy $policy;

    private readonly CompiledRuntime $compiledRuntime;

    private readonly DeferredProviderRegistry $deferredProviders;

    /** @var array<string, true> */
    private array $lazyServices = [];

    public function __construct(
        private readonly ServiceRegistry        $registrations,
        private readonly ManageScopes           $scopes,
        private readonly BuildService           $builder,
        private readonly CreateServiceBlueprint $blueprints,
        private readonly InjectProperties       $injectProperties,
        private readonly InjectMethods          $injectMethods,
        private readonly FunctionCaller       $caller,
        ResolutionMetrics|null                 $metrics = null,
        ResolutionTimeline|null                $timeline = null,
        ResolutionPolicy|null                  $policy = null,
        CompiledRuntime|null                   $compiledRuntime = null,
        DeferredProviderRegistry|null          $deferredProviders = null,
        private readonly string                $diagnosticsMode = CreateContainerConfig::DIAGNOSTICS_MODE_MINIMAL
    ) {
        $this->telemetry = new ResolutionTelemetry(
            metrics : $metrics ?? new ResolutionMetrics,
            timeline: $timeline ?? new ResolutionTimeline
        );
        $this->policy = $policy ?? new ResolutionPolicy;
        $this->compiledRuntime = $compiledRuntime ?? new CompiledRuntime(metrics: $metrics);
        $this->deferredProviders = $deferredProviders ?? new DeferredProviderRegistry;
    }

    /**
     * Attaches the thin public container facade to this runtime owner.
     */
    public function setContainer(ContainerInterface $container) : void
    {
        $this->container = $container;
        $this->caller->setResolver(resolver: $this);
    }

    /**
     * Returns the canonical service registration store.
     */
    public function registrations() : ServiceRegistry
    {
        return $this->registrations;
    }

    /**
     * Returns runtime container settings.
     */
    public function settings() : ContainerSettings
    {
        $settings = $this->registrations->get(abstract: ContainerSettings::class);
        if ($settings !== null && is_object($settings->concrete) && $settings->concrete instanceof ContainerSettings) {
            return $settings->concrete;
        }

        return new ContainerSettings;
    }

    /**
     * Returns the owned scope runtime.
     */
    public function scopes() : ManageScopes
    {
        return $this->scopes;
    }

    /**
     * Returns the observability surface for this resolver.
     */
    public function telemetry() : ResolutionTelemetry
    {
        return $this->telemetry;
    }

    /**
     * Exports low-overhead runtime metrics.
     */
    public function exportMetrics() : string
    {
        return $this->telemetry->exportMetrics();
    }

    /**
     * Clears derived caches, runtime state, and compiled artifacts.
     */
    public function flush() : void
    {
        $this->blueprints->flush();
        $this->compiledRuntime->flush();
        $this->registrations->resetDerivedState();
        $this->scopes->terminate();
        $this->caller->clearCache();
        $this->telemetry->reset();
        $this->lazyServices = [];
    }

    /**
     * Resets disposable runtime state without mutating canonical registrations.
     */
    public function reset() : void
    {
        $this->compiledRuntime->reset();
        $this->registrations->resetDerivedState();
        $this->scopes->terminate();
        $this->caller->clearCache();
        $this->telemetry->reset();
        $this->lazyServices = [];
    }

    /**
     * @param list<string> $serviceIds
     * @return list<string>
     */
    public function validate(array $serviceIds = []) : array
    {
        $this->bootDeferredProvidersFor(serviceIds: $serviceIds);

        $issues = [];
        $graph = [];

        foreach ($this->classesForValidation(serviceIds: $serviceIds) as $class) {
            try {
                $blueprint = $this->blueprints->createFor(class: $class);
                if (! $blueprint->instantiable) {
                    $issues[] = "Service [{$class}] is not instantiable.";
                }

                $graph[$class] = $this->dependenciesForValidation(
                    serviceId: $class,
                    blueprint: $blueprint,
                    issues   : $issues
                );
            } catch (Throwable $throwable) {
                $issues[] = "Service [{$class}] cannot be analyzed: {$throwable->getMessage()}";
            }
        }

        foreach ($this->registrations->allAliases() as $alias => $target) {
            if (
                ! $this->registrations->has(abstract: $target)
                && ! $this->deferredProviders->isDeferred(serviceId: $target)
                && ! class_exists($target)
                && ! interface_exists($target)
            ) {
                $issues[] = "Alias [{$alias}] points to missing service [{$target}].";
            }
        }

        foreach ($this->registrations->all() as $abstract => $registration) {
            if ($registration->concrete === null) {
                $issues[] = "Service [{$abstract}] has no concrete target.";
            }
        }

        foreach ($this->registrations->contextual() as $consumer => $rules) {
            foreach ($rules as $needs => $give) {
                if (! $this->isResolvableBinding(binding: $give)) {
                    $issues[] = "Contextual binding [{$consumer}] -> [{$needs}] points to an invalid target.";
                }
            }
        }

        foreach ($this->detectCircularDependencies(graph: $graph) as $cycle) {
            $issues[] = $cycle;
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
        $lifetimePlan = LifetimePlan::fromRegistration(
            serviceId   : $resolved,
            registration: $registration
        );
        $blueprintClass = is_string($registration?->concrete) && class_exists($registration->concrete)
            ? $registration->concrete
            : (class_exists($resolved) ? $resolved : null);
        $blueprint = is_string($blueprintClass)
            ? $this->blueprints->createFor(class: $blueprintClass)
            : null;
        $compiledArtifact = $this->compiledRuntime->report(serviceIds: [$resolved]);
        $compiledState = $this->compiledRuntime->state(registrations: $this->registrations, serviceId: $resolved);
        $cacheState = $this->cacheStateFor(serviceId: $resolved);
        $aliasChain = $this->registrations->aliasChain(abstract: $id);
        $decorationChain = $this->registrations->decorationChain(abstract: $resolved);
        $concrete = $registration?->concrete;

        return [
            'id' => $id,
            'resolvedId' => $resolved,
            'registered' => $registration !== null,
            'diagnosticsMode' => $this->diagnosticsMode,
            'concrete' => is_object($concrete)
                ? $concrete::class
                : $concrete,
            'lifetime' => $lifetimePlan->name,
            'lifetimePlan' => $lifetimePlan->toArray(),
            'deferred' => ($registration?->deferred ?? false) || $this->deferredProviders->isDeferred(serviceId: $resolved),
            'deferredProvider' => $this->deferredProviders->ownerOf(serviceId: $resolved),
            'tags' => $registration?->tags ?? [],
            'aliases' => array_keys(array_filter(
                $this->registrations->allAliases(),
                static fn(string $target) : bool => $target === $resolved
            )),
            'aliasChain' => $aliasChain,
            'decorationChain' => $decorationChain,
            'blueprint' => $blueprint !== null ? [
                'instantiable' => $blueprint->instantiable,
                'shared' => $blueprint->shared,
                'constructor' => $blueprint->constructor?->parameters ?? [],
                'injectableProperties' => $blueprint->injectableProperties,
                'injectableMethods' => $blueprint->injectableMethods,
                'fingerprint' => $blueprint->fingerprint,
            ] : null,
            'compiled' => $this->compiledRuntime->isCompiled(registrations: $this->registrations, serviceId: $resolved),
            'warmedUp' => $this->compiledRuntime->isWarmedUp(),
            'lazy' => $this->isLazy(id: $resolved),
            'cacheState' => $cacheState,
            'compiledState' => $compiledState,
            'compiledArtifact' => $compiledArtifact?->toArray() ?? ['available' => false],
            'contextualBindings' => $this->contextualBindingsFor(serviceId: $resolved),
            'explain' => $this->explainService(
                id              : $id,
                resolvedId      : $resolved,
                registration    : $registration,
                blueprint       : $blueprint,
                compiledState   : $compiledState,
                compiledArtifact: $compiledArtifact?->toArray() ?? ['available' => false],
                cacheState      : $cacheState,
                aliasChain      : $aliasChain,
                decorationChain : $decorationChain
            ),
        ];
    }

    /**
     * Returns the full diagnostics view for one service id.
     *
     * @return array<string, mixed>
     */
    public function debugService(string $id) : array
    {
        return $this->describeService(id: $id);
    }

    /**
     * Returns whether one alias is registered.
     */
    public function hasAlias(string $alias) : bool
    {
        return $this->registrations->hasAlias(alias: $alias);
    }

    /**
     * Returns whether one service resolves through a deferred registration path.
     */
    public function isDeferred(string $id) : bool
    {
        $resolved = $this->registrations->resolveAlias(abstract: $id);

        return ($this->registrations->get(abstract: $resolved)?->deferred ?? false)
            || $this->deferredProviders->isDeferred(serviceId: $resolved);
    }

    /**
     * Returns whether one service has been marked lazy.
     */
    public function isLazy(string $id) : bool
    {
        $resolved = $this->registrations->resolveAlias(abstract: $id);

        return isset($this->lazyServices[$resolved]);
    }

    /**
     * Returns whether one service id is present in compiled runtime artifacts.
     */
    public function isCompiled(string $id) : bool
    {
        $resolved = $this->registrations->resolveAlias(abstract: $id);

        return $this->compiledRuntime->isCompiled(registrations: $this->registrations, serviceId: $resolved);
    }

    /**
     * Returns whether a compiled runtime is currently available.
     */
    public function isWarmedUp() : bool
    {
        return $this->compiledRuntime->isWarmedUp();
    }

    /**
     * Returns the current compile report when compilation is enabled.
     */
    public function compileReport(array $serviceIds = []) : CompileReport|null
    {
        return $this->compiledRuntime->report(serviceIds: $serviceIds);
    }

    /**
     * Returns the current disposable runtime state report.
     */
    public function runtimeReport() : RuntimeReport
    {
        $lazyServices = array_keys($this->lazyServices);
        sort($lazyServices);
        $deferredProviders = $this->deferredProviders->services();
        ksort($deferredProviders);
        $scopeSnapshot = $this->scopes->snapshot();
        $sharedServiceCount = count($scopeSnapshot['shared']);
        $scopedServiceCount = array_sum(array_map(
            static fn(array $scope) : int => count($scope),
            $scopeSnapshot['scoped']
        ));

        return new RuntimeReport(
            registrationRevision: $this->registrations->revision(),
            compiledRevision    : $this->compiledRuntime->compiledRevision(),
            compiledAttached    : $this->compiledRuntime->isAttached(),
            warmedUp            : $this->compiledRuntime->isWarmedUp(),
            diagnosticsMode     : $this->diagnosticsMode,
            timelineEnabled     : $this->telemetry->timeline()->enabled(),
            lazyServices        : $lazyServices,
            aliases             : $this->registrations->allAliases(),
            deferredProviders   : $deferredProviders,
            sharedServiceCount  : $sharedServiceCount,
            scopedServiceCount  : $scopedServiceCount,
            metrics             : $this->telemetry->metrics()->all(),
            timeline            : $this->telemetry->timeline()->all(),
            scopes              : [
                'shared' => $this->summarizeScopeEntries(entries: $scopeSnapshot['shared']),
                'scopedDepth' => count($scopeSnapshot['scoped']),
                'scoped' => array_map(
                    fn(array $scope) : array => $this->summarizeScopeEntries(entries: $scope),
                    $scopeSnapshot['scoped']
                ),
            ],
            hotPath             : $this->compiledRuntime->summary(),
            compiled            : $this->compiledRuntime->report()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function debugPlan(string $id) : array
    {
        $resolved = $this->registrations->resolveAlias(abstract: $id);
        $registration = $this->registrations->get(abstract: $resolved);
        $blueprintClass = is_string($registration?->concrete) && class_exists($registration->concrete)
            ? $registration->concrete
            : (class_exists($resolved) ? $resolved : null);

        if ($resolved === '' || ! is_string($blueprintClass)) {
            return ['id' => $id, 'resolvedId' => $resolved, 'constructor' => null, 'methods' => []];
        }

        $blueprint = $this->blueprints->createFor(class: $blueprintClass);

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
            'ordered' => true,
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
            'depth' => count($snapshot['scoped']),
            'shared' => $this->summarizeScopeEntries(entries: $snapshot['shared']),
            'scoped' => array_map(
                fn(array $scope) : array => $this->summarizeScopeEntries(entries: $scope),
                $snapshot['scoped']
            ),
        ];
    }

    /**
     * Reads one environment-backed setting value.
     */
    public function env(string $key, mixed $default = null) : mixed
    {
        return $this->settings()->env(key: $key, default: $default);
    }

    /**
     * Returns whether one service can be resolved.
     */
    public function has(string $id) : bool
    {
        $id = $this->registrations->resolveAlias(abstract: $id);

        if (
            $this->scopes->has(abstract: $id)
            || $this->registrations->has(abstract: $id)
            || $this->deferredProviders->isDeferred(serviceId: $id)
        ) {
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

    /**
     * Resolves one service by id.
     *
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
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

    /**
     * Resolves one service and asserts that the result is an object.
     *
     * @param array<string, mixed> $parameters
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
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
     * @param array<string, mixed> $parameters
     * @throws ContainerException
     * @throws ServiceNotFoundException
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

    /**
     * Calls one function, method, or invokable object through the resolver.
     *
     * @param array<string, mixed> $parameters
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function call(callable|string $callable, array $parameters = []) : mixed
    {
        $this->telemetry->metrics()->increment(name: 'container_calls_total');

        return $this->caller->call(target: $callable, parameters: $parameters);
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $parameters
     * @throws ContainerException
     * @throws ServiceNotFoundException
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

    /**
     * Applies property and method injection to one existing object.
     *
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function injectInto(object $target) : object
    {
        return $this->injectTarget(
            target : $target,
            request: new ResolveRequest(serviceId: $target::class, manualInjection: true)
        );
    }

    /**
     * @param array<string, mixed> $context
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function injectIntoInContext(object $target, array $context) : object
    {
        return $this->injectTarget(
            target : $target,
            request: (new ResolveRequest(serviceId: $target::class, manualInjection: true))->withContext(context: $context)
        );
    }

    /**
     * Returns whether one object has injectable members.
     */
    public function canInject(object $target) : bool
    {
        $report = $this->inspectInjection(target: $target);

        return $report->injectedProperties !== [] || $report->injectedMethods !== [];
    }

    /**
     * Returns the injectable members discovered on one object.
     */
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

    /**
     * Registers one prebuilt shared instance.
     */
    public function instance(string $abstract, object $instance) : void
    {
        $this->scopes->instance(abstract: $abstract, instance: $instance);
        $this->registrations->instance(abstract: $abstract, instance: $instance);
    }

    /**
     * Opens one new scope layer.
     */
    public function openScope() : void
    {
        $this->scopes->openScope();
    }

    /**
     * Closes the current scope layer.
     */
    public function closeScope() : void
    {
        $this->scopes->closeScope();
    }

    /**
     * @param list<string> $serviceIds
     * Builds compiled runtime artifacts for the requested service set.
     *
     * @throws ContainerException
     */
    public function compileContainer(array $serviceIds = []) : void
    {
        $this->compileArtifacts(serviceIds: $serviceIds, warmed: false);
    }

    /**
     * @param list<string> $serviceIds
     * Compiles and marks the runtime as warmed up.
     *
     * @throws ContainerException
     */
    public function warmCompiled(array $serviceIds = []) : void
    {
        $this->compileArtifacts(serviceIds: $serviceIds, warmed: true);
        $this->telemetry->metrics()->increment(name: 'container_compiled_warmups_total');
    }

    /**
     * Clears compiled blueprint and container artifacts.
     */
    public function flushCompiled() : void
    {
        $this->blueprints->flush();
        $this->compiledRuntime->flush();
        $this->telemetry->metrics()->increment(name: 'container_compiled_flushes_total');
    }

    /**
     * @param list<string> $serviceIds
     * Rebuilds compiled artifacts from scratch.
     *
     * @throws ContainerException
     */
    public function rebuildCompiled(array $serviceIds = []) : void
    {
        $this->flushCompiled();
        $this->compileContainer(serviceIds: $serviceIds);
        $this->telemetry->metrics()->increment(name: 'container_compiled_rebuilds_total');
    }

    /**
     * Resolves every service registered under one tag.
     *
     * @return list<mixed>
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function tagged(string $tag) : array
    {
        return array_map(
            fn(string $serviceId) => $this->get(id: $serviceId),
            $this->registrations->getTaggedIds(tag: $tag)
        );
    }

    /**
     * Returns one lazy proxy for one service.
     */
    public function lazy(string $abstract) : LazyProxy
    {
        $serviceId = $this->registrations->resolveAlias(abstract: $abstract);
        $this->lazyServices[$serviceId] = true;
        $this->telemetry->metrics()->increment(name: 'container_lazy_proxy_requests_total');

        return new LazyProxy(
            serviceId: $serviceId,
            factory  : fn() => $this->make(id: $serviceId)
        );
    }

    /**
     * @param array<string, mixed> $context
     * Returns one context-aware lazy proxy.
     */
    public function lazyInContext(string $abstract, array $context) : LazyProxy
    {
        $serviceId = $this->registrations->resolveAlias(abstract: $abstract);
        $this->lazyServices[$serviceId] = true;
        $this->telemetry->metrics()->increment(name: 'container_lazy_proxy_requests_total');

        return new LazyProxy(
            serviceId: $serviceId,
            factory  : fn() => $this->makeInContext(id: $serviceId, parameters: [], context: $context)
        );
    }

    /**
     * Resolves one normalized request through compiled or dynamic runtime.
     *
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function resolveRequest(ResolveRequest $request) : mixed
    {
        $request = $this->normalizeRequest(request: $request);
        $this->deferredProviders->bootIfNeeded(
            serviceId: $request->serviceId,
            metrics  : $this->telemetry->metrics()
        );

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
            $resolved = $this->compiledRuntime->shouldUse(registrations: $this->registrations, request: $request)
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

    /**
     * Resolves one request without using compiled runtime methods.
     *
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
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

    /**
     * Resolves one dependency from an existing parent request.
     *
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function resolveCompiledDependency(string $serviceId, ResolveRequest $request) : mixed
    {
        return $this->resolveRequest(request: $request->child(serviceId: $serviceId));
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $parameters
     * @throws ContainerException
     * @throws ServiceNotFoundException
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
     * Finishes one compiled object by running injections and decorators.
     *
     * @throws ContainerException
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
        $lifetime = LifetimePlan::fromRegistration(
            serviceId   : $abstract,
            registration: $registration
        );

        if ($lifetime->isShared()) {
            $this->scopes->instance(abstract: $abstract, instance: $instance);
            return;
        }

        if ($lifetime->isScoped()) {
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

            $this->bootDeferredProviderIfNeeded(serviceId: $serviceId);

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

            $this->bootDeferredProviderIfNeeded(serviceId: $serviceId);

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

    /**
     * @return array<string, mixed>
     */
    private function cacheStateFor(string $serviceId) : array
    {
        $snapshot = $this->scopes->snapshot();

        return [
            'cached' => $this->scopes->has(abstract: $serviceId),
            'lazy' => isset($this->lazyServices[$serviceId]),
            'deferred' => $this->deferredProviders->isDeferred(serviceId: $serviceId),
            'scopeDepth' => count($snapshot['scoped']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function explainService(
        string $id,
        string $resolvedId,
        ServiceRegistration|null $registration,
        ServiceBlueprint|null $blueprint,
        array $compiledState,
        array $compiledArtifact,
        array $cacheState,
        array $aliasChain,
        array $decorationChain
    ) : array {
        $contextualBindings = $this->contextualBindingsFor(serviceId: $resolvedId);
        $fallback = $compiledState['decision'] === 'dynamic'
            ? [
                'active' => true,
                'reason' => $compiledState['reason'],
            ]
            : [
                'active' => false,
                'reason' => 'compiled hot path is active',
            ];

        return [
            'failureChain' => $this->failureChainFor(
                resolvedId      : $resolvedId,
                registration    : $registration,
                compiledState   : $compiledState,
                compiledArtifact: $compiledArtifact
            ),
            'dependencyChain' => $this->dependencyChainFor(serviceId: $resolvedId, seen: []),
            'contextualWinner' => [
                'activeConsumer' => null,
                'winner' => null,
                'bindings' => $contextualBindings,
                'reason' => $contextualBindings === []
                    ? 'no contextual override is registered for this service'
                    : 'contextual overrides exist, but no active consumer selected one for this direct diagnostics request',
            ],
            'aliasExpansion' => [
                'requestedId' => $id,
                'resolvedId' => $resolvedId,
                'chain' => $aliasChain,
                'reason' => count($aliasChain) > 1
                    ? 'requested id resolves through the alias chain shown here'
                    : 'requested id is already canonical',
            ],
            'decoration' => [
                'chain' => $decorationChain,
                'count' => count($decorationChain),
                'reason' => $decorationChain === []
                    ? 'no decorators or extenders are registered for this service'
                    : 'decorators and extenders will run in the listed order',
            ],
            'cache' => [
                'state' => $cacheState,
                'reason' => match (true) {
                    $cacheState['cached'] => 'service is already stored in shared or scoped runtime state',
                    $cacheState['deferred'] => 'service will boot through a deferred provider before the build path runs',
                    $cacheState['lazy'] => 'a lazy proxy has been requested; the real service resolves on first use',
                    default => 'service will resolve through a fresh build path and then enter lifetime storage if needed',
                },
            ],
            'compiled' => [
                'state' => $compiledState,
                'reason' => $compiledState['reason'],
            ],
            'fallback' => $fallback,
            'blueprint' => [
                'available' => $blueprint !== null,
                'reason' => $blueprint !== null
                    ? 'compiled and dynamic resolution can explain constructor and injection metadata from the blueprint'
                    : 'no class-backed blueprint is available for this service',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private function failureChainFor(
        string $resolvedId,
        ServiceRegistration|null $registration,
        array $compiledState,
        array $compiledArtifact
    ) : array {
        $failures = [];

        if ($registration === null && ! class_exists($resolvedId)) {
            $failures[] = "service [{$resolvedId}] is not registered and cannot be autowired";
        }

        if (($compiledState['decision'] ?? '') === 'dynamic' && is_string($compiledState['reason'] ?? null)) {
            $failures[] = $compiledState['reason'];
        }

        foreach ($compiledArtifact['compatibilityIssues'] ?? [] as $issue) {
            if (is_string($issue) && $issue !== '') {
                $failures[] = $issue;
            }
        }

        foreach ($compiledArtifact['warnings'] ?? [] as $warning) {
            if (is_string($warning) && $warning !== '') {
                $failures[] = $warning;
            }
        }

        $failures = array_values(array_unique($failures));
        sort($failures);

        return $failures;
    }

    /**
     * @param list<string> $seen
     * @return array<string, mixed>
     */
    private function dependencyChainFor(string $serviceId, array $seen) : array
    {
        $resolvedId = $this->registrations->resolveAlias(abstract: $serviceId);
        if (in_array($resolvedId, $seen, true)) {
            return [
                'serviceId' => $resolvedId,
                'cycle' => true,
                'dependencies' => [],
            ];
        }

        $registration = $this->registrations->get(abstract: $resolvedId);
        $candidate = $registration?->concrete;

        if ($candidate === null && class_exists($resolvedId)) {
            $candidate = $resolvedId;
        }

        $node = [
            'serviceId' => $resolvedId,
            'class' => is_object($candidate) ? $candidate::class : $candidate,
            'dependencies' => [],
        ];

        if (! is_string($candidate) || ! class_exists($candidate)) {
            return $node;
        }

        $blueprint = $this->blueprints->createFor(class: $candidate);
        $dependencies = $this->dependenciesForWarmup(blueprint: $blueprint);
        sort($dependencies);
        $node['dependencies'] = array_map(
            fn(string $dependency) : array => $this->dependencyChainFor(
                serviceId: $dependency,
                seen     : array_merge($seen, [$resolvedId])
            ),
            $dependencies
        );

        return $node;
    }

    /**
     * @return list<array{consumer: string, target: string}>
     */
    private function contextualBindingsFor(string $serviceId) : array
    {
        $matches = [];

        foreach ($this->registrations->contextual() as $consumer => $rules) {
            foreach ($rules as $needs => $give) {
                if ($this->registrations->resolveAlias(abstract: (string) $needs) !== $serviceId) {
                    continue;
                }

                $matches[] = [
                    'consumer' => $consumer,
                    'target' => is_object($give) ? $give::class : (string) $give,
                ];
            }
        }

        usort(
            $matches,
            static fn(array $left, array $right) : int => [$left['consumer'], $left['target']] <=> [$right['consumer'], $right['target']]
        );

        return $matches;
    }

    private function bootDeferredProviderIfNeeded(string $serviceId) : void
    {
        $this->deferredProviders->bootIfNeeded(
            serviceId: $serviceId,
            metrics  : $this->telemetry->metrics()
        );
    }

    /**
     * @param list<string> $serviceIds
     */
    private function bootDeferredProvidersFor(array $serviceIds) : void
    {
        $this->deferredProviders->bootFor(
            serviceIds    : $serviceIds,
            registrations : $this->registrations,
            metrics       : $this->telemetry->metrics()
        );
    }

    private function resolveCompiledRequest(ResolveRequest $request) : mixed
    {
        return $this->compiledRuntime->resolve(resolver: $this, request: $request);
    }

    /**
     * @param list<string> $serviceIds
     * @throws ContainerException
     */
    private function compileArtifacts(array $serviceIds, bool $warmed) : void
    {
        $this->bootDeferredProvidersFor(serviceIds: $serviceIds);

        $validationIssues = [];
        if ($this->compiledRuntime->shouldValidateBeforeCompile()) {
            $validationIssues = $this->validate(serviceIds: $serviceIds);
            if ($validationIssues !== []) {
                throw new ContainerException(
                    message: "Container compile failed:\n- " . implode("\n- ", $validationIssues)
                );
            }
        }

        $classes = $this->classesForWarmup(serviceIds: $serviceIds);
        $this->blueprints->warm(classes: $classes);

        $compiled = $this->compiledRuntime->compile(
            serviceIds       : $serviceIds,
            validationIssues : $validationIssues,
            warmed           : $warmed
        );
        if ($compiled !== null) {
            $this->compiledRuntime->attach(
                compiled: $compiled,
                revision: $this->registrations->revision()
            );
        }

        $this->telemetry->metrics()->increment(name: 'container_compile_total');
    }

    /**
     * Registers one deferred transient service.
     */
    public function defer(string $abstract, mixed $concrete = null) : ServiceRegistration
    {
        return $this->registrations->defer(abstract: $abstract, concrete: $concrete);
    }

    /**
     * @param list<string> $serviceIds
     * Registers one deferred provider and its owned service ids.
     *
     * @throws ContainerException
     */
    public function registerDeferredProvider(ServiceProviderInterface $provider, array $serviceIds) : void
    {
        $this->deferredProviders->register(
            provider      : $provider,
            serviceIds    : $serviceIds,
            registrations : $this->registrations,
            metrics       : $this->telemetry->metrics()
        );
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

    /**
     * @param list<string> $issues
     * @return list<string>
     */
    private function dependenciesForValidation(string $serviceId, ServiceBlueprint $blueprint, array &$issues) : array
    {
        $dependencies = [];

        foreach ($blueprint->constructor?->parameters ?? [] as $parameter) {
            if (! is_string($parameter['serviceId'] ?? null)) {
                continue;
            }

            $dependency = $this->registrations->resolveAlias(abstract: $parameter['serviceId']);
            $dependencies[] = $dependency;

            if (! $this->registrations->has(abstract: $dependency) && ! class_exists($dependency)) {
                $issues[] = "Service [{$serviceId}] depends on missing service [{$dependency}].";
            }
        }

        foreach ($blueprint->injectableProperties ?? [] as $property) {
            if (! is_string($property['serviceId'] ?? null)) {
                continue;
            }

            $dependency = $this->registrations->resolveAlias(abstract: $property['serviceId']);
            $dependencies[] = $dependency;

            if (! $this->registrations->has(abstract: $dependency) && ! class_exists($dependency)) {
                $issues[] = "Service [{$serviceId}] injects missing property dependency [{$dependency}].";
            }
        }

        foreach ($blueprint->injectableMethods ?? [] as $method) {
            foreach ($method['plan']->parameters as $parameter) {
                if (! is_string($parameter['serviceId'] ?? null)) {
                    continue;
                }

                $dependency = $this->registrations->resolveAlias(abstract: $parameter['serviceId']);
                $dependencies[] = $dependency;

                if (! $this->registrations->has(abstract: $dependency) && ! class_exists($dependency)) {
                    $issues[] = "Service [{$serviceId}] injects missing method dependency [{$dependency}].";
                }
            }
        }

        return array_values(array_unique($dependencies));
    }

    /**
     * @param array<string, list<string>> $graph
     * @return list<string>
     */
    private function detectCircularDependencies(array $graph) : array
    {
        $issues = [];
        $state = [];

        foreach (array_keys($graph) as $serviceId) {
            $this->visitDependency(
                serviceId: $serviceId,
                graph    : $graph,
                state    : $state,
                stack    : [],
                issues   : $issues
            );
        }

        return array_values(array_unique($issues));
    }

    /**
     * @param array<string, list<string>> $graph
     * @param array<string, string> $state
     * @param list<string> $stack
     * @param list<string> $issues
     */
    private function visitDependency(
        string $serviceId,
        array $graph,
        array &$state,
        array $stack,
        array &$issues
    ) : void {
        $currentState = $state[$serviceId] ?? 'new';
        if ($currentState === 'done') {
            return;
        }

        if ($currentState === 'visiting') {
            $cycleStart = array_search($serviceId, $stack, true);
            $path = $cycleStart === false ? array_merge($stack, [$serviceId]) : array_slice($stack, $cycleStart);
            $path[] = $serviceId;
            $issues[] = 'Circular dependency detected: ' . implode(' -> ', $path);

            return;
        }

        $state[$serviceId] = 'visiting';
        $stack[] = $serviceId;

        foreach ($graph[$serviceId] ?? [] as $dependency) {
            if (! isset($graph[$dependency])) {
                continue;
            }

            $this->visitDependency(
                serviceId: $dependency,
                graph    : $graph,
                state    : $state,
                stack    : $stack,
                issues   : $issues
            );
        }

        $state[$serviceId] = 'done';
    }

    private function isResolvableBinding(mixed $binding) : bool
    {
        if ($binding instanceof Closure || is_object($binding)) {
            return true;
        }

        if (! is_string($binding) || $binding === '') {
            return false;
        }

        return class_exists($binding)
            || interface_exists($binding)
            || $this->registrations->has(abstract: $binding)
            || str_contains($binding, '::')
            || str_contains($binding, '@');
    }

    /**
     * @param array<string, mixed> $entries
     * @return array<string, string>
     */
    private function summarizeScopeEntries(array $entries) : array
    {
        $summary = [];

        foreach ($entries as $serviceId => $instance) {
            $summary[$serviceId] = is_object($instance) ? $instance::class : get_debug_type($instance);
        }

        ksort($summary);

        return $summary;
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
