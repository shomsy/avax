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
use Avax\Container\DependencyInjection\Dependencies\Ownership\CheckCompositionPolicies;
use Avax\Container\DependencyInjection\Dependencies\Ownership\RegistrationMetadata;
use Avax\Container\DependencyInjection\Dependencies\Ownership\RegistrationVisibility;
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
use Avax\Container\DependencyInjection\Scopes\ScopeKind;
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

    /** @var array{environment: string, flags: list<string>, tenant: string, region: string, mode: string}|null */
    private array|null $compositionState = null;

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

        $validationServiceIds = array_values(array_unique(array_merge(
            array_keys($graph),
            $serviceIds !== [] ? array_map(
                fn(string $serviceId) : string => $this->registrations->resolveAlias(abstract: $serviceId),
                $serviceIds
            ) : array_keys($this->registrations->all())
        )));

        $this->validateSliceAccess(graph: $graph, issues: $issues);
        $this->validateLifetimeAccess(graph: $graph, issues: $issues);
        $this->validateSliceContracts(issues: $issues);
        $this->validateDuplicateConcepts(issues: $issues);
        $this->validateOverrideCollisions(issues: $issues);
        $this->validateDecoratorConflicts(issues: $issues);
        $this->validateGroupConflicts(issues: $issues);
        $this->validateEnvironmentProfiles(serviceIds: $validationServiceIds, issues: $issues);
        $this->validateDisposalSemantics(issues: $issues);
        foreach ($this->policyFindings(graph: $graph, dependents: $this->buildDependents(graph: $graph)) as $serviceId => $findings) {
            foreach ($findings as $finding) {
                $issues[] = strtoupper($finding['severity']) . " {$finding['code']} [{$serviceId}]: {$finding['message']}.";
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
        $decorationDetails = $this->decorationDetailsFor(serviceId: $resolved, chain: $decorationChain);
        $concrete = $registration?->concrete;
        $ownership = $registration?->metadata ?? RegistrationMetadata::for(unitId: $resolved);
        $conditions = $this->conditionStateFor(metadata: $ownership);
        $dependencyGraph = $this->buildDependencyGraph(serviceIds: [$resolved]);
        $dependents = $this->buildDependents(graph: $dependencyGraph);
        $overrides = $this->registrations->overrideHistory(abstract: $resolved)[$resolved] ?? [];

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
            'group' => [
                'name' => $registration?->group,
                'order' => $registration?->groupOrder ?? 0,
            ],
            'ownership' => $ownership->toArray(),
            'conditions' => $conditions,
            'overrides' => $overrides,
            'aliases' => array_keys(array_filter(
                $this->registrations->allAliases(),
                static fn(string $target) : bool => $target === $resolved
            )),
            'aliasChain' => $aliasChain,
            'decorationChain' => $decorationChain,
            'decorationDetails' => $decorationDetails,
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
            'usedBy' => $dependents[$resolved] ?? [],
            'impact' => $this->impactFor(serviceId: $resolved, dependents: $dependents),
            'topLevelAccess' => $this->registrations->topLevelAccessTo(serviceId: $resolved),
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
                'frames' => $scopeSnapshot['frames'],
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
    public function debugGraph(string $id = '') : array
    {
        $graph = $this->buildDependencyGraph(serviceIds: $id !== '' ? [$id] : []);
        $dependents = $this->buildDependents(graph: $graph);
        $dead = $this->deadRegistrations(graph: $graph, dependents: $dependents);
        $duplicates = $this->registrations->duplicateConcepts();
        $warnings = $this->policyWarnings(graph: $graph, dependents: $dependents);
        $findings = $this->policyFindings(graph: $graph, dependents: $dependents);
        $structureDiff = $this->structureDiff(graph: $graph);

        if ($id === '') {
            $conditions = [];
            foreach ($this->registrations->all() as $serviceId => $registration) {
                $conditions[$serviceId] = $this->conditionStateFor(metadata: $registration->metadata);
            }

            return [
                'graph' => $graph,
                'dependents' => $dependents,
                'slices' => $this->registrations->sliceManifests(),
                'conditions' => $conditions,
                'overrides' => $this->registrations->overrideHistory(),
                'deadRegistrations' => $dead,
                'duplicateConcepts' => $duplicates,
                'structureDiff' => $structureDiff,
                'policyFindings' => $findings,
                'groups' => $this->registrations->groupIndex(),
                'policyWarnings' => $warnings,
            ];
        }

        $resolved = $this->registrations->resolveAlias(abstract: $id);
        $description = $this->describeService(id: $resolved);

        return [
            'service' => $resolved,
            'owner' => $description['ownership'] ?? RegistrationMetadata::for(unitId: $resolved)->toArray(),
            'conditions' => $description['conditions'] ?? [],
            'overrides' => $description['overrides'] ?? [],
            'dependencies' => $this->dependencyChainFor(serviceId: $resolved, seen: []),
            'dependents' => $dependents[$resolved] ?? [],
            'impact' => $this->impactFor(serviceId: $resolved, dependents: $dependents),
            'topLevelAccess' => $this->registrations->topLevelAccessTo(serviceId: $resolved),
            'structureDiff' => [
                'dependencies' => $structureDiff['dependencies'][$resolved] ?? ['current' => $graph[$resolved] ?? [], 'compiled' => [], 'changed' => false],
                'ownership' => $structureDiff['ownership'][$resolved] ?? ['current' => $description['ownership'] ?? [], 'compiled' => [], 'changed' => false],
            ],
            'duplicateConcepts' => array_values(array_filter(
                $duplicates,
                static fn(array $duplicate) : bool => in_array($resolved, array_column($duplicate['services'], 'serviceId'), true)
            )),
            'policyFindings' => $findings[$resolved] ?? [],
            'policyWarnings' => $warnings[$resolved] ?? [],
            'dead' => in_array($resolved, $dead, true),
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
            'frames' => $snapshot['frames'],
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
        $registration = $this->registrations->get(abstract: $id);

        if ($registration instanceof ServiceRegistration) {
            $conditions = $this->conditionStateFor(metadata: $registration->metadata);
            if (! $conditions['active']) {
                return false;
            }

            $topLevel = $this->registrations->topLevelAccessTo(serviceId: $id);
            if (
                ! ($topLevel['allowed'] ?? false)
                && $registration->metadata->ownerSlice !== 'default'
            ) {
                return false;
            }
        }

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
    public function openScope(string $kind = ScopeKind::OPERATION, string $scopeId = '') : void
    {
        $this->scopes->openScope(kind: $kind, scopeId: $scopeId);
    }

    /**
     * Closes the current scope layer.
     */
    public function closeScope(string|null $kind = null) : void
    {
        $this->scopes->closeScope(kind: $kind);
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
     * Resolves every service registered in one ordered group.
     *
     * @return list<mixed>
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function grouped(string $group) : array
    {
        return array_map(
            fn(string $serviceId) => $this->get(id: $serviceId),
            $this->registrations->getGroupedIds(group: $group)
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
        $registration = $this->registrationFor(request: $request);
        $lifetime = LifetimePlan::fromRegistration(
            serviceId   : $request->serviceId,
            registration: $registration
        );

        $this->telemetry->timeline()->record(
            action   : 'resolve',
            serviceId: $request->serviceId,
            outcome  : 'started'
        );
        $this->telemetry->metrics()->increment(name: 'container_resolve_total');

        try {
            if ($this->hasStored(serviceId: $request->serviceId, lifetime: $lifetime)) {
                $this->telemetry->timeline()->record(
                    action   : 'resolve',
                    serviceId: $request->serviceId,
                    outcome  : 'cached'
                );
                $this->telemetry->metrics()->increment(name: 'container_resolve_cached_total');

                return $this->stored(serviceId: $request->serviceId, lifetime: $lifetime);
            }

            if (! $request->manualInjection && ! $this->policy->isAllowed(abstract: $request->serviceId)) {
                throw new ContainerException(
                    message: "Resolution blocked for [{$request->serviceId}] by policy. "
                        . 'Dependency path [' . $request->getPath() . ']. '
                        . 'Likely fix: resolve an exported entry or public capability instead of reaching into an internal implementation detail.'
                );
            }

            if ($request->contains(serviceId: $request->serviceId) && $request->parent !== null) {
                throw new ContainerException(
                    message: "Circular dependency detected along [{$request->getPath()}]. "
                        . 'Likely fix: remove the back-reference, inject an interface boundary, or defer one side of the graph.'
                );
            }

            $this->assertRuntimeAccess(
                request     : $request,
                registration: $registration,
                lifetime    : $lifetime
            );
            $resolved = $this->compiledRuntime->shouldUse(registrations: $this->registrations, request: $request)
                ? $this->resolveCompiledRequest(request: $request)
                : $this->resolveDynamicRequest(request: $request);

            if (is_object($resolved)) {
                $this->storeResolved(
                    abstract : $request->serviceId,
                    instance : $resolved,
                    lifetime : $lifetime
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
                message: "Service [{$request->serviceId}] is not registered and cannot be autowired. "
                    . 'Dependency path [' . $request->getPath() . ']. '
                    . 'Likely fix: bind the service explicitly, add a compatible class-backed registration, or pass a runtime override.'
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

    private function hasStored(string $serviceId, LifetimePlan $lifetime) : bool
    {
        if ($lifetime->isShared()) {
            return $this->scopes->hasShared(abstract: $serviceId);
        }

        if ($lifetime->isScoped()) {
            return $this->scopes->hasScoped(
                abstract: $serviceId,
                kind    : $lifetime->scopeKind()
            );
        }

        return false;
    }

    private function stored(string $serviceId, LifetimePlan $lifetime) : mixed
    {
        if ($lifetime->isShared()) {
            return $this->scopes->getShared(abstract: $serviceId);
        }

        if ($lifetime->isScoped()) {
            return $this->scopes->getScoped(
                abstract: $serviceId,
                kind    : $lifetime->scopeKind()
            );
        }

        return null;
    }

    private function storeResolved(string $abstract, object $instance, LifetimePlan $lifetime) : void
    {
        if ($lifetime->isShared()) {
            $this->scopes->setShared(
                abstract   : $abstract,
                instance   : $instance,
                disposable : $lifetime->disposable
            );
            return;
        }

        if ($lifetime->isScoped()) {
            $this->scopes->setScoped(
                abstract   : $abstract,
                instance   : $instance,
                kind       : $lifetime->scopeKind(),
                disposable : $lifetime->disposable
            );
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
            'activeScopeKinds' => array_values(array_map(
                static fn(array $frame) : string => $frame['kind'],
                $snapshot['frames']
            )),
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
        $ownership = $registration?->metadata ?? RegistrationMetadata::for(unitId: $resolvedId);
        $conditions = $this->conditionStateFor(metadata: $ownership);
        $overrides = $this->registrations->overrideHistory(abstract: $resolvedId)[$resolvedId] ?? [];
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
            'owner' => [
                'metadata' => $ownership->toArray(),
                'reason' => 'ownership metadata explains who owns this unit, what slice it belongs to, and how visible it is',
            ],
            'override' => [
                'history' => $overrides,
                'reason' => $overrides === []
                    ? 'no previous authored registration for this abstract was replaced'
                    : 'the service was rebound after one or more previous authored registrations; inspect the history to explain override posture',
            ],
            'conditions' => [
                'state' => $conditions,
                'reason' => $conditions['active']
                    ? 'all active composition conditions match this registration'
                    : 'one or more environment, flag, tenant, region, or mode conditions exclude this registration',
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

    /**
     * @param list<string> $chain
     * @return list<array<string, mixed>>
     */
    private function decorationDetailsFor(string $serviceId, array $chain) : array
    {
        return array_map(
            function (string $descriptor) use ($serviceId) : array {
                $metadata = $this->registrations->ownership(abstract: $descriptor)
                    ?? RegistrationMetadata::for(unitId: $descriptor);
                $registered = $this->registrations->has(abstract: $descriptor);

                return [
                    'descriptor' => $descriptor,
                    'registered' => $registered,
                    'owner' => $metadata->ownerSlice,
                    'visibility' => $metadata->visibility,
                    'reason' => $registered
                        ? 'decorator resolves through a registered ownership-aware unit'
                        : 'decorator is not a registered service and only has descriptor-level diagnostics',
                    'access' => $registered
                        ? $this->registrations->accessTo(
                            consumerId  : $serviceId,
                            dependencyId: $descriptor
                        )
                        : null,
                ];
            },
            $chain
        );
    }

    /**
     * @param list<string> $serviceIds
     * @return array<string, list<string>>
     */
    private function buildDependencyGraph(array $serviceIds = []) : array
    {
        $graph = [];
        $queue = $serviceIds !== []
            ? array_values(array_unique($serviceIds))
            : array_keys($this->registrations->all());

        while ($queue !== []) {
            $serviceId = $this->registrations->resolveAlias(abstract: array_shift($queue));
            if ($serviceId === '' || isset($graph[$serviceId])) {
                continue;
            }

            $graph[$serviceId] = [];
            $registration = $this->registrations->get(abstract: $serviceId);
            $candidate = $registration?->concrete;

            if ($candidate === null && class_exists($serviceId)) {
                $candidate = $serviceId;
            }

            if (! is_string($candidate) || ! class_exists($candidate)) {
                continue;
            }

            try {
                $blueprint = $this->blueprints->createFor(class: $candidate);
                $dependencies = $this->dependenciesForWarmup(blueprint: $blueprint);
                sort($dependencies);
                $graph[$serviceId] = $dependencies;

                foreach ($dependencies as $dependency) {
                    $queue[] = $dependency;
                }
            } catch (Throwable) {
                $graph[$serviceId] = [];
            }
        }

        ksort($graph);

        return $graph;
    }

    /**
     * @param array<string, list<string>> $graph
     * @return array<string, list<string>>
     */
    private function buildDependents(array $graph) : array
    {
        $dependents = [];

        foreach ($graph as $serviceId => $dependencies) {
            $dependents[$serviceId] ??= [];

            foreach ($dependencies as $dependency) {
                $dependents[$dependency] ??= [];
                $dependents[$dependency][] = $serviceId;
            }
        }

        foreach ($dependents as $serviceId => $items) {
            $items = array_values(array_unique($items));
            sort($items);
            $dependents[$serviceId] = $items;
        }

        ksort($dependents);

        return $dependents;
    }

    /**
     * @param array<string, list<string>> $dependents
     * @return list<string>
     */
    private function impactFor(string $serviceId, array $dependents) : array
    {
        $queue = $dependents[$serviceId] ?? [];
        $impacted = [];

        while ($queue !== []) {
            $current = array_shift($queue);
            if (! is_string($current) || isset($impacted[$current])) {
                continue;
            }

            $impacted[$current] = true;

            foreach ($dependents[$current] ?? [] as $next) {
                $queue[] = $next;
            }
        }

        $ids = array_keys($impacted);
        sort($ids);

        return $ids;
    }

    /**
     * @param array<string, list<string>> $graph
     * @return array<string, mixed>
     */
    private function structureDiff(array $graph) : array
    {
        $report = $this->compiledRuntime->report();
        $metadata = $report?->metadata;
        $currentOwnership = $this->registrations->ownershipMap();
        $currentSlices = $this->registrations->sliceManifests();

        $dependencyDiff = [];
        $ownershipDiff = [];

        foreach (array_values(array_unique(array_merge(array_keys($graph), array_keys($metadata?->dependencies ?? [])))) as $serviceId) {
            $current = $graph[$serviceId] ?? [];
            $compiled = $metadata?->dependencies[$serviceId] ?? [];
            sort($current);
            sort($compiled);

            $dependencyDiff[$serviceId] = [
                'current' => $current,
                'compiled' => $compiled,
                'changed' => $current !== $compiled,
            ];
        }

        foreach (array_values(array_unique(array_merge(array_keys($currentOwnership), array_keys($metadata?->ownership ?? [])))) as $serviceId) {
            $ownershipDiff[$serviceId] = [
                'current' => $currentOwnership[$serviceId] ?? [],
                'compiled' => $metadata?->ownership[$serviceId] ?? [],
                'changed' => ($currentOwnership[$serviceId] ?? []) !== ($metadata?->ownership[$serviceId] ?? []),
            ];
        }

        return [
            'compiledAvailable' => $report?->available ?? false,
            'dependencies' => $dependencyDiff,
            'ownership' => $ownershipDiff,
            'slices' => [
                'current' => $currentSlices,
                'compiled' => $metadata?->slices ?? [],
                'changed' => $currentSlices !== ($metadata?->slices ?? []),
            ],
        ];
    }

    /**
     * @param array<string, list<string>> $graph
     * @param array<string, list<string>> $dependents
     * @return list<string>
     */
    private function deadRegistrations(array $graph, array $dependents) : array
    {
        $dead = [];
        $aliasTargets = array_values($this->registrations->allAliases());
        $taggedIds = [];

        foreach ($this->registrations->tagIndex() as $ids) {
            foreach ($ids as $id) {
                $taggedIds[$id] = true;
            }
        }

        foreach ($this->registrations->all() as $serviceId => $registration) {
            $topLevel = $this->registrations->topLevelAccessTo(serviceId: $serviceId);
            $usedBy = $dependents[$serviceId] ?? [];
            $metadata = $registration->metadata;

            if ($usedBy !== []) {
                continue;
            }

            if ($metadata->category === 'flow' && $metadata->intent === 'entry') {
                continue;
            }

            if (in_array($serviceId, $aliasTargets, true)) {
                continue;
            }

            if (isset($taggedIds[$serviceId])) {
                continue;
            }

            if (($topLevel['allowed'] ?? false) === true) {
                continue;
            }

            $dead[] = $serviceId;
        }

        sort($dead);

        return $dead;
    }

    /**
     * @param array<string, list<string>> $graph
     * @param array<string, list<string>> $dependents
     * @return array<string, list<string>>
     */
    private function policyWarnings(array $graph, array $dependents) : array
    {
        $warnings = [];
        foreach ($this->policyFindings(graph: $graph, dependents: $dependents) as $serviceId => $findings) {
            $warnings[$serviceId] = array_map(
                static fn(array $finding) : string => $finding['message'],
                $findings
            );
        }

        return $warnings;
    }

    /**
     * @param array<string, list<string>> $graph
     * @param array<string, list<string>> $dependents
     * @return array<string, list<array{code: string, severity: string, category: string, message: string}>>
     */
    private function policyFindings(array $graph, array $dependents) : array
    {
        return (new CheckCompositionPolicies)->check(
            graph         : $graph,
            dependents    : $dependents,
            registrations : $this->registrations,
            blueprints    : $this->blueprints
        );
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

        if ($warmed) {
            foreach ($this->warmSharedServiceIds(serviceIds: $serviceIds) as $serviceId) {
                $this->get(id: $serviceId);
            }
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
     * @param list<string> $serviceIds
     * @return list<string>
     */
    private function warmSharedServiceIds(array $serviceIds) : array
    {
        $selected = [];
        $filter = $serviceIds !== [] ? array_fill_keys($serviceIds, true) : null;

        foreach ($this->registrations->all() as $abstract => $registration) {
            if ($filter !== null && ! isset($filter[$abstract])) {
                continue;
            }

            $lifetime = LifetimePlan::fromRegistration(
                serviceId   : $abstract,
                registration: $registration
            );

            if (! $lifetime->isShared() || ! $lifetime->warm || $lifetime->lazy) {
                continue;
            }

            $selected[] = $abstract;
        }

        sort($selected);

        return $selected;
    }

    private function currentEnvironment() : string
    {
        return $this->currentComposition()['environment'];
    }

    private function currentMode() : string
    {
        return $this->currentComposition()['mode'];
    }

    private function currentTenant() : string
    {
        return $this->currentComposition()['tenant'];
    }

    private function currentRegion() : string
    {
        return $this->currentComposition()['region'];
    }

    /**
     * @return list<string>
     */
    private function currentFlags() : array
    {
        return $this->currentComposition()['flags'];
    }

    /**
     * @return array{environment: string, flags: list<string>, tenant: string, region: string, mode: string}
     */
    private function currentComposition() : array
    {
        if ($this->compositionState !== null) {
            return $this->compositionState;
        }

        $configured = $this->settings()->get(key: 'app_env')
            ?? $this->settings()->get(key: 'APP_ENV')
            ?? null;

        $environment = is_string($configured) && $configured !== ''
            ? $configured
            : getenv('APP_ENV');
        $environment = is_string($environment) ? $environment : '';

        $mode = $this->settings()->get(key: 'composition.mode')
            ?? $this->settings()->get(key: 'app_mode')
            ?? $this->settings()->get(key: 'APP_MODE')
            ?? null;

        $tenant = $this->settings()->get(key: 'composition.tenant')
            ?? $this->settings()->get(key: 'tenant')
            ?? $this->settings()->get(key: 'TENANT')
            ?? null;

        $region = $this->settings()->get(key: 'composition.region')
            ?? $this->settings()->get(key: 'region')
            ?? $this->settings()->get(key: 'REGION')
            ?? null;

        $configuredFlags = $this->settings()->get(key: 'composition.flags')
            ?? $this->settings()->get(key: 'flags')
            ?? $this->settings()->get(key: 'feature_flags')
            ?? [];

        $flags = [];
        if (is_array($configuredFlags)) {
            $flags = array_values(array_filter(
                array_map(
                    static fn(mixed $flag) : string => is_string($flag) ? trim($flag) : '',
                    $configuredFlags
                ),
                static fn(string $flag) : bool => $flag !== ''
            ));

            $flags = array_values(array_unique($flags));
            sort($flags);
        }

        return $this->compositionState = [
            'environment' => $environment,
            'flags' => $flags,
            'tenant' => is_string($tenant) ? trim($tenant) : '',
            'region' => is_string($region) ? trim($region) : '',
            'mode' => is_string($mode) ? trim($mode) : '',
        ];
    }

    /**
     * @return array{
     *     active: bool,
     *     environment: string,
     *     flags: list<string>,
     *     tenant: string,
     *     region: string,
     *     mode: string,
     *     reasons: list<string>
     * }
     */
    private function conditionStateFor(RegistrationMetadata $metadata) : array
    {
        $environment = $this->currentEnvironment();
        $flags = $this->currentFlags();
        $tenant = $this->currentTenant();
        $region = $this->currentRegion();
        $mode = $this->currentMode();
        $reasons = [];

        if (! $metadata->supportsEnvironment(environment: $environment)) {
            $reasons[] = 'environment [' . $environment . '] is not in [' . implode(', ', $metadata->profiles) . ']';
        }

        if (! $metadata->supportsFlags(activeFlags: $flags)) {
            $reasons[] = 'active flags [' . implode(', ', $flags) . '] do not satisfy required flags [' . implode(', ', $metadata->flags) . ']';
        }

        if (! $metadata->supportsTenant(tenant: $tenant)) {
            $reasons[] = 'tenant [' . $tenant . '] is not in [' . implode(', ', $metadata->tenants) . ']';
        }

        if (! $metadata->supportsRegion(region: $region)) {
            $reasons[] = 'region [' . $region . '] is not in [' . implode(', ', $metadata->regions) . ']';
        }

        if (! $metadata->supportsMode(mode: $mode)) {
            $reasons[] = 'mode [' . $mode . '] is not in [' . implode(', ', $metadata->modes) . ']';
        }

        return [
            'active' => $reasons === [],
            'environment' => $environment,
            'flags' => $flags,
            'tenant' => $tenant,
            'region' => $region,
            'mode' => $mode,
            'reasons' => $reasons,
        ];
    }

    private function assertRuntimeAccess(
        ResolveRequest $request,
        ServiceRegistration|null $registration,
        LifetimePlan $lifetime
    ) : void
    {
        if (! $registration instanceof ServiceRegistration) {
            return;
        }

        $metadata = $registration->metadata;
        $conditions = $this->conditionStateFor(metadata: $metadata);

        if (! $conditions['active']) {
            throw new ContainerException(
                message: "Service [{$request->serviceId}] from slice [{$metadata->ownerSlice}] is inactive for the current composition. "
                    . 'Dependency path [' . $request->getPath() . ']. '
                    . 'Environment [' . $conditions['environment'] . '], flags [' . implode(', ', $conditions['flags']) . '], '
                    . 'tenant [' . $conditions['tenant'] . '], region [' . $conditions['region'] . '], mode [' . $conditions['mode'] . ']. '
                    . 'Why: ' . implode('; ', $conditions['reasons']) . '. '
                    . 'Likely fix: activate a matching profile, flag set, tenant, region, or mode, or request a compatible implementation.'
            );
        }

        if (
            $lifetime->requiresScope()
            && ! $this->scopes->hasActiveScope(kind: $lifetime->scopeKind())
        ) {
            throw new ContainerException(
                message: "Service [{$request->serviceId}] uses lifetime [{$lifetime->name}] and requires an active [{$lifetime->scopeKind()}] scope. "
                    . 'Dependency path [' . $request->getPath() . ']. '
                    . "Likely fix: openScope('{$lifetime->scopeKind()}') before resolving it or change the service lifetime."
            );
        }

        $consumerId = $request->parent?->serviceId ?? $request->consumer;
        if (is_string($consumerId) && $consumerId !== '') {
            $access = $this->registrations->accessTo(
                consumerId  : $consumerId,
                dependencyId: $request->serviceId
            );

            if (! ($access['allowed'] ?? false)) {
                $consumer = $access['consumer']['ownerSlice'] ?? 'unknown';
                $dependency = $access['dependency']['ownerSlice'] ?? 'unknown';

                throw new ContainerException(
                    message: "Illegal cross-slice dependency [{$consumerId}] -> [{$request->serviceId}] along [{$request->getPath()}]. "
                        . "Consumer slice [{$consumer}] cannot use dependency slice [{$dependency}]: {$access['reason']}. "
                        . 'Likely fix: export the dependency intentionally, import its slice, or move the dependency back to the owning flow.'
                );
            }

            return;
        }

        if ($request->manualInjection) {
            return;
        }

        $topLevel = $this->registrations->topLevelAccessTo(serviceId: $request->serviceId);
        if (
            ! ($topLevel['allowed'] ?? false)
            && $metadata->ownerSlice !== 'default'
        ) {
            throw new ContainerException(
                message: "Service [{$request->serviceId}] is not part of the top-level container surface: {$topLevel['reason']}. "
                    . "Likely fix: mark the flow root as entry(), export the shared capability, or resolve it from its owning slice instead of the top level."
            );
        }
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
     * @param list<string> $issues
     */
    private function validateSliceAccess(array $graph, array &$issues) : void
    {
        foreach ($graph as $serviceId => $dependencies) {
            foreach ($dependencies as $dependency) {
                $access = $this->registrations->accessTo(
                    consumerId  : $serviceId,
                    dependencyId: $dependency
                );

                if (($access['allowed'] ?? false) === true) {
                    continue;
                }

                $issues[] = "Service [{$serviceId}] cannot use dependency [{$dependency}]: {$access['reason']}.";
            }
        }
    }

    /**
     * @param array<string, list<string>> $graph
     * @param list<string> $issues
     */
    private function validateLifetimeAccess(array $graph, array &$issues) : void
    {
        foreach ($graph as $serviceId => $dependencies) {
            $consumerLifetime = LifetimePlan::fromRegistration(
                serviceId   : $serviceId,
                registration: $this->registrations->get(abstract: $serviceId)
            );

            foreach ($dependencies as $dependency) {
                $dependencyLifetime = LifetimePlan::fromRegistration(
                    serviceId   : $dependency,
                    registration: $this->registrations->get(abstract: $dependency)
                );

                if (
                    $consumerLifetime->isShared()
                    && $dependencyLifetime->isScoped()
                ) {
                    $issues[] = "Shared service [{$serviceId}] captures scoped dependency [{$dependency}] which can escape its scope.";
                }

                if (
                    $this->lifetimeRank(lifetime: $consumerLifetime) > $this->lifetimeRank(lifetime: $dependencyLifetime)
                    && $dependencyLifetime->isScoped()
                ) {
                    $issues[] = "Service [{$serviceId}] with lifetime [{$consumerLifetime->name}] captures narrower scoped dependency [{$dependency}] with lifetime [{$dependencyLifetime->name}].";
                }

                if (
                    ! $consumerLifetime->isTransient()
                    && $dependencyLifetime->isTransient()
                ) {
                    $issues[] = "Service [{$serviceId}] with lifetime [{$consumerLifetime->name}] captures transient dependency [{$dependency}] which will be reused implicitly after construction.";
                }
            }
        }
    }

    /**
     * @param list<string> $issues
     */
    private function validateSliceContracts(array &$issues) : void
    {
        $manifests = $this->registrations->sliceManifests();

        foreach ($manifests as $slice => $manifest) {
            if (($manifest['category'] ?? '') === 'mixed') {
                $issues[] = "Slice [{$slice}] mixes multiple categories [" . implode(', ', $manifest['categories'] ?? []) . '].';
            }

            foreach ($manifest['imports'] ?? [] as $import) {
                if (! isset($manifests[$import])) {
                    $issues[] = "Slice [{$slice}] imports missing slice [{$import}].";
                }
            }

            foreach ($manifest['exports'] ?? [] as $serviceId) {
                $registration = $this->registrations->get(abstract: $serviceId);
                $visibility = $registration?->metadata->visibility ?? RegistrationVisibility::PUBLIC;

                if (in_array($visibility, [RegistrationVisibility::PRIVATE, RegistrationVisibility::INTERNAL], true)) {
                    $issues[] = "Service [{$serviceId}] is exported by slice [{$slice}] but keeps non-exportable visibility [{$visibility}].";
                }
            }
        }
    }

    /**
     * @param list<string> $issues
     */
    private function validateDuplicateConcepts(array &$issues) : void
    {
        foreach ($this->registrations->duplicateConcepts() as $duplicate) {
            $services = array_map(
                static fn(array $service) : string => $service['serviceId'] . '@' . $service['ownerSlice'],
                $duplicate['services']
            );

            $issues[] = "Duplicate concept [{$duplicate['concept']}] is owned by multiple units: " . implode(', ', $services) . '.';
        }
    }

    /**
     * @param list<string> $issues
     */
    private function validateOverrideCollisions(array &$issues) : void
    {
        foreach ($this->registrations->duplicateConcepts() as $duplicate) {
            $services = array_column($duplicate['services'], 'serviceId');

            for ($index = 0; $index < count($services); $index++) {
                for ($next = $index + 1; $next < count($services); $next++) {
                    $left = $this->registrations->ownership(abstract: $services[$index]);
                    $right = $this->registrations->ownership(abstract: $services[$next]);

                    if (! $left instanceof RegistrationMetadata || ! $right instanceof RegistrationMetadata) {
                        continue;
                    }

                    if (! $this->conditionsOverlap(left: $left, right: $right)) {
                        continue;
                    }

                    if ($left->overrideSource === null && $right->overrideSource === null) {
                        continue;
                    }

                    $issues[] = "Override collision for concept [{$duplicate['concept']}] between [{$services[$index]}] and [{$services[$next]}]: both registrations overlap the same composition conditions.";
                }
            }
        }

        foreach ($this->registrations->overrideHistory() as $abstract => $history) {
            $current = $this->registrations->get(abstract: $abstract);
            if (! $current instanceof ServiceRegistration) {
                continue;
            }

            $currentMetadata = $current->metadata;

            foreach ($history as $previous) {
                $previousMetadataState = $previous['metadata'] ?? null;
                if (! is_array($previousMetadataState)) {
                    continue;
                }

                $previousMetadata = RegistrationMetadata::fromArray(state: $previousMetadataState);
                if (! $this->conditionsOverlap(left: $previousMetadata, right: $currentMetadata)) {
                    continue;
                }

                if (
                    $previousMetadata->ownerSlice !== $currentMetadata->ownerSlice
                    || $previousMetadata->visibility !== $currentMetadata->visibility
                    || $previousMetadata->category !== $currentMetadata->category
                ) {
                    $overrideSource = $currentMetadata->overrideSource ?? 'unspecified';
                    $issues[] = "Override collision for service [{$abstract}] changes ownership posture from "
                        . "[{$previousMetadata->ownerSlice}/{$previousMetadata->category}/{$previousMetadata->visibility}] to "
                        . "[{$currentMetadata->ownerSlice}/{$currentMetadata->category}/{$currentMetadata->visibility}] "
                        . "under overlapping composition conditions. Override source [{$overrideSource}].";
                }
            }
        }
    }

    /**
     * @param list<string> $issues
     */
    private function validateDecoratorConflicts(array &$issues) : void
    {
        foreach ($this->registrations->all() as $serviceId => $registration) {
            $chain = $this->registrations->decorationChain(abstract: $serviceId);
            if ($chain === []) {
                continue;
            }

            if (count($chain) !== count(array_unique($chain))) {
                $issues[] = "Service [{$serviceId}] has duplicate decorator descriptors in its decoration chain.";
            }

            foreach ($chain as $descriptor) {
                if (! is_string($descriptor) || ! $this->registrations->has(abstract: $descriptor)) {
                    continue;
                }

                $access = $this->registrations->accessTo(
                    consumerId  : $serviceId,
                    dependencyId: $descriptor
                );

                if (! ($access['allowed'] ?? false)) {
                    $issues[] = "Service [{$serviceId}] decorates through [{$descriptor}] but the decorator is not visible from the service slice: {$access['reason']}.";
                }
            }
        }
    }

    /**
     * @param list<string> $issues
     */
    private function validateGroupConflicts(array &$issues) : void
    {
        foreach ($this->registrations->groupIndex() as $group => $items) {
            $orders = [];

            foreach ($items as $item) {
                $orders[$item['order']][] = $item['serviceId'];
            }

            foreach ($orders as $order => $serviceIds) {
                if (count($serviceIds) <= 1) {
                    continue;
                }

                sort($serviceIds);
                $issues[] = "Group [{$group}] uses duplicate order [{$order}] across services [" . implode(', ', $serviceIds) . '].';
            }
        }
    }

    /**
     * @param list<string> $serviceIds
     * @param list<string> $issues
     */
    private function validateEnvironmentProfiles(array $serviceIds, array &$issues) : void
    {
        foreach (array_values(array_unique($serviceIds)) as $serviceId) {
            $registration = $this->registrations->get(abstract: $serviceId);
            $metadata = $registration?->metadata;

            if (! $metadata instanceof RegistrationMetadata) {
                continue;
            }

            $conditions = $this->conditionStateFor(metadata: $metadata);
            if (! $conditions['active']) {
                $issues[] = "Service [{$serviceId}] is inactive for the current composition: " . implode('; ', $conditions['reasons']) . '.';
            }
        }
    }

    /**
     * @param list<string> $issues
     */
    private function validateDisposalSemantics(array &$issues) : void
    {
        foreach ($this->registrations->all() as $serviceId => $registration) {
            $lifetime = LifetimePlan::fromRegistration(
                serviceId   : $serviceId,
                registration: $registration
            );

            if ($lifetime->disposable && $lifetime->isTransient()) {
                $issues[] = "Service [{$serviceId}] is marked disposable but uses transient lifetime, so the container cannot own its disposal boundary.";
            }

            if (! $lifetime->disposable) {
                continue;
            }

            $candidate = $registration->concrete;
            if (! is_string($candidate) || ! class_exists($candidate)) {
                continue;
            }

            if (
                ! is_subclass_of($candidate, \Avax\Container\DependencyInjection\Scopes\DisposableInterface::class)
                && ! method_exists($candidate, 'dispose')
            ) {
                $issues[] = "Service [{$serviceId}] is marked disposable but class [{$candidate}] does not expose dispose() or implement DisposableInterface.";
            }
        }
    }

    private function lifetimeRank(LifetimePlan $lifetime) : int
    {
        if ($lifetime->isShared()) {
            return 100;
        }

        if ($lifetime->isTransient()) {
            return 0;
        }

        return match ($lifetime->scopeKind()) {
            ScopeKind::TENANT => 40,
            ScopeKind::JOB => 30,
            ScopeKind::REQUEST => 20,
            ScopeKind::OPERATION => 10,
            ScopeKind::ANY => 5,
            default => 1,
        };
    }

    private function conditionsOverlap(RegistrationMetadata $left, RegistrationMetadata $right) : bool
    {
        return $this->listsOverlap(left: $left->profiles, right: $right->profiles)
            && $this->listsOverlap(left: $left->flags, right: $right->flags)
            && $this->listsOverlap(left: $left->tenants, right: $right->tenants)
            && $this->listsOverlap(left: $left->regions, right: $right->regions)
            && $this->listsOverlap(left: $left->modes, right: $right->modes);
    }

    /**
     * @param list<string> $left
     * @param list<string> $right
     */
    private function listsOverlap(array $left, array $right) : bool
    {
        if ($left === [] || $right === []) {
            return true;
        }

        return array_intersect($left, $right) !== [];
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
