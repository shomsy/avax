<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Resolution;

use Avax\Components\Application\Container\System\Capabilities\Composition\Compilation\CompileReport;
use Avax\Components\Application\Container\System\Capabilities\Composition\ContainerSettings;
use Avax\Components\Application\Container\System\Capabilities\Composition\CreateContainerConfig;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DependencyRegistration;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DependencyRegistry;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DependencyRegistryContract;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Blueprints\CreateDependencyBlueprint;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Blueprints\DependencyBlueprint;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Ownership\RegistrationCategory;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Ownership\RegistrationMetadata;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Ownership\RegistrationVisibility;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Ownership\SliceContext;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Providers\DeferredProviderRegistry;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Providers\RegisterDependency;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Errors\DependencyNotFoundException;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability\GraphExporter;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability\ResolutionMetrics;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability\ResolutionTelemetry;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability\ResolutionTimeline;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability\RuntimeReport;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Policy\GovernComposition;
use Avax\Components\Application\Container\System\Capabilities\Execution\BuildService;
use Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Invocation\FunctionCaller;
use Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Methods\InjectMethods;
use Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Properties\InjectProperties;
use Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Reports\InjectionReport;
use Avax\Components\Application\Container\System\Capabilities\Runtime\CompiledRuntime;
use Avax\Components\Application\Container\System\Capabilities\Runtime\LazyProxy;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\DisposableInterface;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\Lifetimes\SingletonLifetime;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\Lifetimes\TransientLifetime;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\ManageScopes;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\ResettableInterface;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\ScopeKind;
use Avax\Components\Application\Container\System\Container;
use Avax\Components\Application\Container\System\ContainerInterface;
use Closure;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use ReflectionException;
use ReflectionFunction;
use Throwable;

/**
 * Owns service resolution, compilation state, scopes, and runtime diagnostics.
 */
final class ResolveDependency
{
    private ResolutionTelemetry $telemetry;

    private ?ContainerInterface $container = null;

    private readonly ResolutionPolicy $policy;

    private readonly CompiledRuntime $compiledRuntime;

    private readonly DeferredProviderRegistry $deferredProviders;

    private readonly GovernComposition $governor;

    /** @var array<string, true> */
    private array $lazyServices = [];

    /** @var array{environment: string, flags: list<string>, tenant: string, region: string, mode: string}|null */
    private ?array $compositionState = null;

    private readonly string $asyncTarget;

    private readonly string $sliceBoundaryMode;

    private readonly string $environment;

    private readonly string $diagnosticsMode;

    private readonly FunctionCaller $caller;

    private readonly InjectMethods $injectMethods;

    private readonly InjectProperties $injectProperties;

    private readonly CreateDependencyBlueprint $blueprints;

    private readonly BuildService $builder;

    private readonly ManageScopes $scopes;

    private readonly DependencyRegistry $registrations;

    public function __construct(
        DependencyRegistry        $registrations,
        ManageScopes              $scopes,
        BuildService              $builder,
        CreateDependencyBlueprint $blueprints,
        InjectProperties          $injectProperties,
        InjectMethods             $injectMethods,
        FunctionCaller            $caller,
        ?ResolutionMetrics        $metrics = null,
        ?ResolutionTimeline       $timeline = null,
        ?ResolutionPolicy         $policy = null,
        ?CompiledRuntime          $compiledRuntime = null,
        ?DeferredProviderRegistry $deferredProviders = null,
        ?string                   $diagnosticsMode = null,
        ?string                   $environment = null,
        ?string                   $sliceBoundaryMode = null,
        ?string                   $asyncTarget = null,
        ?GovernComposition        $governor = null,
    )
    {
        $diagnosticsMode         ??= CreateContainerConfig::DIAGNOSTICS_MODE_MINIMAL;
        $environment             ??= '';
        $sliceBoundaryMode       ??= CreateContainerConfig::SLICE_BOUNDARY_MODE_STRICT;
        $asyncTarget             ??= CreateContainerConfig::ASYNC_TARGET_FPM;
        $this->registrations     = $registrations;
        $this->scopes            = $scopes;
        $this->builder           = $builder;
        $this->blueprints        = $blueprints;
        $this->injectProperties  = $injectProperties;
        $this->injectMethods     = $injectMethods;
        $this->caller            = $caller;
        $this->diagnosticsMode   = $diagnosticsMode;
        $this->environment       = $environment;
        $this->sliceBoundaryMode = $sliceBoundaryMode;
        $this->asyncTarget       = $asyncTarget;
        $this->telemetry         = new ResolutionTelemetry(
            metrics : $metrics ?? new ResolutionMetrics,
            timeline: $timeline ?? new ResolutionTimeline,
        );
        $this->policy            = $policy ?? new ResolutionPolicy;
        $this->compiledRuntime   = $compiledRuntime ?? new CompiledRuntime(metrics: $metrics);
        $this->deferredProviders = $deferredProviders ?? new DeferredProviderRegistry;
        $this->governor          = $governor ?? new GovernComposition;
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
    public function registrations() : DependencyRegistry
    {
        return $this->registrations;
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
     * Returns the full diagnostics view for one service id.
     *
     *
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function debugService(string $id) : array
    {
        return $this->describeService(id: $id);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function describeService(string $id) : array
    {
        $resolved         = $this->registrations->resolveAlias(abstract: $id);
        $registration     = $this->registrations->get(abstract: $resolved);
        $lifetimePlan     = LifetimePlan::fromRegistration(
            serviceId   : $resolved,
            registration: $registration,
        );
        $blueprintClass = is_string(value: $registration?->concrete) && class_exists(class: $registration->concrete)
            ? $registration->concrete
            : (class_exists(class: $resolved) ? $resolved : null);
        $blueprint        = is_string(value: $blueprintClass)
            ? $this->blueprints->createFor(class: $blueprintClass)
            : null;
        $compiledArtifact = $this->compiledRuntime->report(serviceIds: [$resolved]);
        $compiledState    = $this->compiledRuntime->state(registrations: $this->registrations, serviceId: $resolved);
        $cacheState       = $this->cacheStateFor(serviceId: $resolved);
        $aliasChain       = $this->registrations->aliasChain(abstract: $id);
        $decorationChain  = $this->registrations->decorationChain(abstract: $resolved);
        $decorationDetails = $this->decorationDetailsFor(serviceId: $resolved, chain: $decorationChain);
        $concrete         = $registration?->concrete;
        $ownership        = $registration?->metadata ?? RegistrationMetadata::for(unitId: $resolved);
        $conditions       = $this->conditionStateFor(metadata: $ownership);
        $dependencyGraph  = $this->buildDependencyGraph(serviceIds: [$resolved]);
        $dependents       = $this->buildDependents(graph: $dependencyGraph);
        $overrides        = $this->registrations->overrideHistory(abstract: $resolved)[$resolved] ?? [];

        return [
            'id'               => $id,
            'resolvedId'       => $resolved,
            'registered'       => $registration !== null,
            'diagnosticsMode'  => $this->diagnosticsMode,
            'concrete'         => is_object(value: $concrete)
                ? $concrete::class
                : $concrete,
            'lifetime'         => $lifetimePlan->name,
            'lifetimePlan'     => $lifetimePlan->toArray(),
            'deferred'         => ($registration?->deferred ?? false) || $this->deferredProviders->isDeferred(serviceId: $resolved),
            'deferredProvider' => $this->deferredProviders->ownerOf(serviceId: $resolved),
            'tags'             => $registration?->tags ?? [],
            'group'            => [
                'name' => $registration?->group,
                'order' => $registration?->groupOrder ?? 0,
            ],
            'ownership'        => $ownership->toArray(),
            'conditions'       => $conditions,
            'overrides'        => $overrides,
            'aliases'          => array_keys(array: array_filter(
                                                        array   : $this->registrations->allAliases(),
                                                        callback: static fn (string $target) : bool => $target === $resolved,
                                                    )),
            'aliasChain'       => $aliasChain,
            'decorationChain'  => $decorationChain,
            'decorationDetails' => $decorationDetails,
            'blueprint'        => $blueprint !== null ? [
                'instantiable'      => $blueprint->instantiable,
                'shared'            => $blueprint->shared,
                'constructor'       => $blueprint->constructor?->parameters ?? [],
                'injectableProperties' => $blueprint->injectableProperties,
                'injectableMethods' => $blueprint->injectableMethods,
                'fingerprint'       => $blueprint->fingerprint,
            ] : null,
            'compiled'         => $this->compiledRuntime->isCompiled(registrations: $this->registrations, serviceId: $resolved),
            'warmedUp'         => $this->compiledRuntime->isWarmedUp(),
            'lazy'             => $this->isLazy(id: $resolved),
            'cacheState'       => $cacheState,
            'compiledState'    => $compiledState,
            'compiledArtifact' => $compiledArtifact?->toArray() ?? ['available' => false],
            'contextualBindings' => $this->contextualBindingsFor(serviceId: $resolved),
            'usedBy'           => $dependents[$resolved] ?? [],
            'impact'           => $this->impactFor(serviceId: $resolved, dependents: $dependents),
            'topLevelAccess'   => $this->registrations->topLevelAccessTo(serviceId: $resolved),
            'explain'          => $this->explainService(
                id              : $id,
                resolvedId      : $resolved,
                registration    : $registration,
                blueprint       : $blueprint,
                compiledState   : $compiledState,
                compiledArtifact: $compiledArtifact?->toArray() ?? ['available' => false],
                cacheState      : $cacheState,
                aliasChain      : $aliasChain,
                decorationChain : $decorationChain,
            ),
        ];
    }

    /**
     * Resolves one service by id.
     *
     *
     * @throws Throwable
     */
    public function get(string $id) : mixed
    {
        return $this->resolveRequest(request: new ResolveRequest(serviceId: $this->registrations->resolveAlias(abstract: $id)));
    }

    /**
     * Resolves one normalized request through compiled or dynamic runtime.
     *
     * @throws ContainerException
     * @throws DependencyNotFoundException
     * @throws Throwable
     */
    public function resolveRequest(ResolveRequest $request) : mixed
    {
        $request = $this->normalizeRequest(request: $request);
        $this->deferredProviders->bootIfNeeded(
            serviceId: $request->serviceId,
            metrics  : $this->telemetry->metrics(),
        );
        $registration = $this->registrationFor(request: $request);
        $lifetime = LifetimePlan::fromRegistration(
            serviceId   : $request->serviceId,
            registration: $registration,
        );

        $this->telemetry->timeline()->record(
            action   : 'resolve',
            serviceId: $request->serviceId,
            outcome  : 'started',
        );
        $this->telemetry->metrics()->increment(name: 'container_resolve_total');

        try {
            if (! $request->manualInjection && ! $this->policy->isAllowed(abstract: $request->serviceId)) {
                throw new ContainerException(
                    message: "Resolution blocked for [{$request->serviceId}] by policy. "
                             . 'Dependency path [' . $request->getPath() . ']. '
                             . 'Likely fix: resolve an exported entry or public capability instead of reaching into an internal implementation detail.',
                );
            }

            if ($request->contains(serviceId: $request->serviceId) && $request->parent !== null) {
                throw new ContainerException(
                    message: "Circular dependency detected along [{$request->getPath()}]. "
                             . 'Likely fix: remove the back-reference, inject an interface boundary, or defer one side of the graph.',
                );
            }

            $this->assertRuntimeAccess(
                request     : $request,
                registration: $registration,
                lifetime    : $lifetime,
            );

            if ($this->hasStored(serviceId: $request->serviceId, lifetime: $lifetime)) {
                $this->telemetry->timeline()->record(
                    action   : 'resolve',
                    serviceId: $request->serviceId,
                    outcome  : 'cached',
                );
                $this->telemetry->metrics()->increment(name: 'container_resolve_cached_total');

                return $this->stored(serviceId: $request->serviceId, lifetime: $lifetime);
            }

            if ($lifetime->isPooled()) {
                $checkedOut = $this->scopes->checkoutPooled(
                    abstract        : $request->serviceId,
                    kind            : $lifetime->scopeKind(),
                    maxSize         : $lifetime->poolSize,
                    resetBeforeReuse: $lifetime->poolResetBeforeReuse,
                    disposable      : $lifetime->disposable,
                );

                if (($checkedOut['hit'] ?? false) === true) {
                    $this->telemetry->timeline()->record(
                        action   : 'resolve',
                        serviceId: $request->serviceId,
                        outcome  : 'cached',
                    );
                    $this->telemetry->metrics()->increment(name: 'container_resolve_cached_total');
                    $this->telemetry->metrics()->increment(name: 'container_pooled_hits_total');

                    return $checkedOut['instance'];
                }

                $this->telemetry->metrics()->increment(name: 'container_pooled_misses_total');
            }

            $resolved = $this->compiledRuntime->shouldUse(registrations: $this->registrations, request: $request)
                ? $this->resolveCompiledRequest(request: $request)
                : $this->resolveDynamicRequest(request: $request);

            if (is_object(value: $resolved)) {
                $this->storeResolved(
                    abstract: $request->serviceId,
                    instance: $resolved,
                    lifetime: $lifetime,
                );
            }

            $this->telemetry->timeline()->record(
                action   : 'resolve',
                serviceId: $request->serviceId,
                outcome  : 'resolved',
            );

            return $resolved;
        } catch (Throwable $exception) {
            $this->telemetry->timeline()->record(
                action   : 'resolve',
                serviceId: $request->serviceId,
                outcome  : 'failed',
            );
            $this->telemetry->metrics()->increment(name: 'container_resolve_failures_total');

            throw $exception;
        }
    }

    private function normalizeRequest(ResolveRequest $request) : ResolveRequest
    {
        $serviceId = $this->registrations->resolveAlias(abstract: $request->serviceId);
        if ($serviceId === $request->serviceId) {
            return $request;
        }

        return new ResolveRequest(
            serviceId      : $serviceId,
            overrides      : $request->overrides,
            context        : $request->context,
            parent         : $request->parent,
            manualInjection: $request->manualInjection,
            consumer       : $request->consumer,
        );
    }

    /**
     * @throws ReflectionException
     */
    private function registrationFor(ResolveRequest $request) : ?DependencyRegistration
    {
        $registration = $this->registrations->get(abstract: $request->serviceId);

        if ($registration !== null) {
            return $registration;
        }

        if (! class_exists(class: $request->serviceId)) {
            return null;
        }

        $blueprint    = $this->blueprints->createFor(class: $request->serviceId);
        $registration = new DependencyRegistration(abstract: $request->serviceId);
        $registration->concrete = $request->serviceId;
        $registration->lifetime = $blueprint->shared ? SingletonLifetime::NAME : TransientLifetime::NAME;

        return $registration;
    }

    private function assertRuntimeAccess(
        ResolveRequest $request,
        ?DependencyRegistration $registration,
        LifetimePlan            $lifetime,
    ) : void
    {
        if (! $registration instanceof DependencyRegistration) {
            return;
        }

        $metadata = $registration->metadata;
        if ($metadata->hasConditions()) {
            $conditions = $this->conditionStateFor(metadata: $metadata);

            if (! $conditions['active']) {
                throw new ContainerException(
                    message: "Service [{$request->serviceId}] from slice [{$metadata->ownerSlice}] is inactive for the current composition. "
                             . 'Dependency path [' . $request->getPath() . ']. '
                             . 'Environment [' . $conditions['environment'] . '], flags [' . implode(separator: ', ', array: $conditions['flags']) . '], '
                             . 'tenant [' . $conditions['tenant'] . '], region [' . $conditions['region'] . '], mode [' . $conditions['mode'] . ']. '
                             . 'Why: ' . implode(separator: '; ', array: $conditions['reasons']) . '. '
                             . 'Likely fix: activate a matching profile, flag set, tenant, region, or mode, or request a compatible implementation.',
                );
            }
        }

        if (
            $lifetime->requiresScope()
            && ! $this->scopes->hasActiveScope(kind: $lifetime->scopeKind())
        ) {
            throw new ContainerException(
                message: "Service [{$request->serviceId}] uses lifetime [{$lifetime->name}] and requires an active [{$lifetime->scopeKind()}] scope. "
                         . 'Dependency path [' . $request->getPath() . ']. '
                         . "Likely fix: openScope('{$lifetime->scopeKind()}') before resolving it or change the service lifetime.",
            );
        }

        $consumerId = $request->parent?->serviceId ?? $request->consumer;
        if (is_string(value: $consumerId) && $consumerId !== '') {
            $this->assertRestrictedRuntimeDependency(
                consumerId: $consumerId,
                request   : $request,
            );

            if (! $this->consumerCanAccess(
                consumerId: $consumerId,
                request   : $request,
            )) {
                $access = $this->accessForConsumer(
                    consumerId: $consumerId,
                    request   : $request,
                );
                $consumer = $access['consumer']['ownerSlice'] ?? ($access['viewer']['slice'] ?? 'unknown');
                $dependency = $access['dependency']['ownerSlice'] ?? 'unknown';

                throw new ContainerException(
                    message: "Illegal cross-slice dependency [{$consumerId}] -> [{$request->serviceId}] along [{$request->getPath()}]. "
                             . "Consumer slice [{$consumer}] cannot use dependency slice [{$dependency}]: {$access['reason']}. "
                             . 'Likely fix: export the dependency intentionally, import its slice, or move the dependency back to the owning flow.',
                );
            }

            return;
        }

        if ($request->manualInjection) {
            return;
        }

        $slice = SliceContext::from(context: $request->context);
        if ($slice !== '') {
            if (! $this->registrations->has(abstract: $request->serviceId)) {
                throw new ContainerException(
                    message: "Slice view [{$slice}] can only resolve authored services, but [{$request->serviceId}] is not registered. "
                             . 'Dependency path [' . $request->getPath() . ']. '
                             . 'Likely fix: bind the unit to its owning slice explicitly before resolving it through a slice view.',
                );
            }

            if (! $this->registrations->allowsSliceAccess(
                viewerSlice: $slice,
                serviceId  : $request->serviceId,
            )) {
                $access = $this->registrations->sliceAccessTo(
                    viewerSlice: $slice,
                    serviceId  : $request->serviceId,
                );

                throw new ContainerException(
                    message: "Slice view [{$slice}] cannot resolve [{$request->serviceId}]: {$access['reason']}. "
                             . 'Dependency path [' . $request->getPath() . ']. '
                             . 'Likely fix: expose the dependency publicly, export and import the owning slice, or resolve it from its owning slice view.',
                );
            }

            return;
        }

        if (
            ! $this->registrations->allowsTopLevelAccess(serviceId: $request->serviceId)
            && $metadata->ownerSlice !== 'default'
        ) {
            $topLevel = $this->registrations->topLevelAccessTo(serviceId: $request->serviceId);

            throw new ContainerException(
                message: "Service [{$request->serviceId}] is not part of the top-level container surface: {$topLevel['reason']}. "
                         . 'Likely fix: mark the flow root as entry(), export the shared capability, or resolve it from its owning slice instead of the top level.',
            );
        }
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
            $reasons[] = 'environment [' . $environment . '] is not in [' . implode(separator: ', ', array: $metadata->profiles) . ']';
        }

        if (! $metadata->supportsFlags(activeFlags: $flags)) {
            $reasons[] = 'active flags [' . implode(separator: ', ', array: $flags) . '] do not satisfy required flags [' . implode(separator: ', ', array: $metadata->flags) . ']';
        }

        if (! $metadata->supportsTenant(tenant: $tenant)) {
            $reasons[] = 'tenant [' . $tenant . '] is not in [' . implode(separator: ', ', array: $metadata->tenants) . ']';
        }

        if (! $metadata->supportsRegion(region: $region)) {
            $reasons[] = 'region [' . $region . '] is not in [' . implode(separator: ', ', array: $metadata->regions) . ']';
        }

        if (! $metadata->supportsMode(mode: $mode)) {
            $reasons[] = 'mode [' . $mode . '] is not in [' . implode(separator: ', ', array: $metadata->modes) . ']';
        }

        return [
            'active'  => $reasons === [],
            'environment' => $environment,
            'flags'   => $flags,
            'tenant'  => $tenant,
            'region'  => $region,
            'mode'    => $mode,
            'reasons' => $reasons,
        ];
    }

    private function currentEnvironment() : string
    {
        return $this->currentComposition()['environment'];
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

        $environment = is_string(value: $configured) && $configured !== ''
            ? $configured
            : getenv(name: 'APP_ENV');
        $environment = is_string(value: $environment) ? $environment : '';

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
        if (is_array(value: $configuredFlags)) {
            $flags = array_map(
                    callback: static fn (mixed $flag) : string => is_string(value: $flag) ? trim(string: $flag) : '',
                    array   : $configuredFlags,
                )
                    |> (static fn ($x) => array_filter(array: $x, callback: static fn (string $flag) : bool => $flag !== ''))
                    |> array_values(...);

            $flags = array_values(array: array_unique(array: $flags));
            sort(array: $flags);
        }

        return $this->compositionState = [
            'environment' => $environment,
            'flags'  => $flags,
            'tenant' => is_string(value: $tenant) ? trim(string: $tenant) : '',
            'region' => is_string(value: $region) ? trim(string: $region) : '',
            'mode'   => is_string(value: $mode) ? trim(string: $mode) : '',
        ];
    }

    /**
     * Returns runtime container settings.
     */
    public function settings() : ContainerSettings
    {
        $settings = $this->registrations->get(abstract: ContainerSettings::class);
        if ($settings !== null && is_object(value: $settings->concrete) && $settings->concrete instanceof ContainerSettings) {
            return $settings->concrete;
        }

        return new ContainerSettings;
    }

    /**
     * @return list<string>
     */
    private function currentFlags() : array
    {
        return $this->currentComposition()['flags'];
    }

    private function currentTenant() : string
    {
        return $this->currentComposition()['tenant'];
    }

    private function currentRegion() : string
    {
        return $this->currentComposition()['region'];
    }

    private function currentMode() : string
    {
        return $this->currentComposition()['mode'];
    }

    private function assertRestrictedRuntimeDependency(string $consumerId, ResolveRequest $request) : void
    {
        if (! $this->isRestrictedDependency(serviceId: $request->serviceId) || $this->consumerMayUseRestrictedDependency(consumerId: $consumerId)) {
            return;
        }

        throw new ContainerException(
            message: "Service locator drift blocked for [{$consumerId}] -> [{$request->serviceId}] along [{$request->getPath()}]. "
                     . 'Only container configuration and foundation.system services may depend on container runtime internals. '
                     . 'Likely fix: inject the concrete dependency boundary instead of the container, resolver, registry, or raw settings object.',
        );
    }

    private function isRestrictedDependency(string $serviceId) : bool
    {
        return in_array(needle: $serviceId, haystack: [
            PsrContainerInterface::class,
            ContainerInterface::class,
            Container::class,
            self::class,
            DependencyRegistryContract::class,
            DependencyRegistry::class,
            CreateContainerConfig::class,
            ContainerSettings::class,
        ],              strict: true);
    }

    private function consumerMayUseRestrictedDependency(string $consumerId) : bool
    {
        $registration = $this->registrations->get(abstract: $consumerId);
        $metadata = $registration?->metadata;

        if ($metadata instanceof RegistrationMetadata) {
            return $metadata->ownerSlice === 'foundation.system'
                || $metadata->category === RegistrationCategory::CONFIGURATION;
        }

        if (class_exists(class: $consumerId) && is_subclass_of(object_or_class: $consumerId, class: RegisterDependency::class)) {
            return true;
        }

        return false;
    }

    private function consumerCanAccess(string $consumerId, ResolveRequest $request) : bool
    {
        $slice = SliceContext::from(context: $request->context);

        if ($slice !== '' && ! $this->registrations->has(abstract: $consumerId)) {
            return $this->registrations->allowsSliceAccess(
                viewerSlice: $slice,
                serviceId  : $request->serviceId,
            );
        }

        return $this->registrations->allowsAccess(
            consumerId  : $consumerId,
            dependencyId: $request->serviceId,
        );
    }

    /**
     * Returns whether one service can be resolved.
     */
    public function has(string $id) : bool
    {
        $id = $this->registrations->resolveAlias(abstract: $id);
        $registration = $this->registrations->get(abstract: $id);

        if ($registration instanceof DependencyRegistration) {
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

        if (! class_exists(class: $id)) {
            return false;
        }

        try {
            return $this->blueprints->createFor(class: $id)->instantiable;
        } catch (Throwable) {
            return false;
        }
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
     * @return array<string, mixed>
     */
    private function accessForConsumer(string $consumerId, ResolveRequest $request) : array
    {
        $slice = SliceContext::from(context: $request->context);

        if ($slice !== '' && ! $this->registrations->has(abstract: $consumerId)) {
            return $this->registrations->sliceAccessTo(
                viewerSlice: $slice,
                serviceId  : $request->serviceId,
            );
        }

        return $this->registrations->accessTo(
            consumerId  : $consumerId,
            dependencyId: $request->serviceId,
        );
    }

    private function hasStored(string $serviceId, LifetimePlan $lifetime) : bool
    {
        if ($lifetime->isShared()) {
            return $this->scopes->hasShared(abstract: $serviceId);
        }

        if ($lifetime->isPooled()) {
            return $this->scopes->hasPooled(
                abstract: $serviceId,
                kind    : $lifetime->scopeKind(),
            );
        }

        if ($lifetime->isScoped()) {
            return $this->scopes->hasScoped(
                abstract: $serviceId,
                kind    : $lifetime->scopeKind(),
            );
        }

        return false;
    }

    private function stored(string $serviceId, LifetimePlan $lifetime) : mixed
    {
        if ($lifetime->isShared()) {
            return $this->scopes->getShared(abstract: $serviceId);
        }

        if ($lifetime->isPooled()) {
            return $this->scopes->getPooled(
                abstract: $serviceId,
                kind    : $lifetime->scopeKind(),
            );
        }

        if ($lifetime->isScoped()) {
            return $this->scopes->getScoped(
                abstract: $serviceId,
                kind    : $lifetime->scopeKind(),
            );
        }

        return null;
    }

    private function resolveCompiledRequest(ResolveRequest $request) : mixed
    {
        return $this->compiledRuntime->resolve(resolver: $this, request: $request);
    }

    /**
     * Resolves one request without using compiled runtime methods.
     *
     *
     * @throws Throwable
     * @throws ReflectionException
     */
    public function resolveDynamicRequest(ResolveRequest $request) : mixed
    {
        $request   = $this->normalizeRequest(request: $request);
        $registration = $this->registrationFor(request: $request);
        $candidate = $this->candidateFor(request: $request, registration: $registration);

        if ($candidate === null) {
            throw new DependencyNotFoundException(
                message: "Service [{$request->serviceId}] is not registered and cannot be autowired. "
                         . 'Dependency path [' . $request->getPath() . ']. '
                         . 'Likely fix: bind the service explicitly, add a compatible class-backed registration, or pass a runtime override.',
            );
        }

        $resolved = $this->evaluateCandidate(
            candidate   : $candidate,
            request     : $request,
            registration: $registration,
        );

        return $this->applyExtenders(
            abstract: $request->serviceId,
            instance: $resolved,
        );
    }

    private function candidateFor(ResolveRequest $request, ?DependencyRegistration $registration) : mixed
    {
        $consumer = $this->contextualConsumer(request: $request);
        if ($consumer !== null) {
            $contextual = $this->registrations->getContextualMatch(
                consumer: $consumer,
                needs   : $request->serviceId,
            );
            if ($contextual !== null) {
                return $contextual;
            }
        }

        return $registration?->concrete;
    }

    private function contextualConsumer(ResolveRequest $request) : ?string
    {
        return $request->parent?->serviceId ?? $request->consumer;
    }

    /**
     * @throws Throwable
     * @throws ReflectionException
     */
    private function evaluateCandidate(mixed $candidate, ResolveRequest $request, ?DependencyRegistration $registration) : mixed
    {
        if (is_object(value: $candidate) && ! ($candidate instanceof Closure)) {
            return $candidate;
        }

        if ($candidate instanceof Closure) {
            return $this->invokeFactory(
                factory  : $candidate,
                overrides: array_merge($registration?->arguments ?? [], $request->overrides),
            );
        }

        if (is_string(value: $candidate)) {
            if ($candidate !== $request->serviceId && $this->registrations->has(abstract: $candidate)) {
                return $this->resolveRequest(request: $request->child(serviceId: $candidate));
            }

            $resolved = $this->builder->build(
                class    : $candidate,
                resolver : $this,
                overrides: array_merge($registration?->arguments ?? [], $request->overrides),
                request  : $request,
            );
            $blueprint = $this->blueprints->createFor(class: $candidate);

            $this->injectProperties->inject(
                target   : $resolved,
                blueprint: $blueprint,
                overrides: $request->overrides,
                resolver : $this,
                request  : $request,
            );
            $this->injectMethods->inject(
                target   : $resolved,
                blueprint: $blueprint,
                overrides: $request->overrides,
                resolver : $this,
                request  : $request,
            );

            return $resolved;
        }

        return $candidate;
    }

    /**
     * @throws ReflectionException
     */
    private function invokeFactory(Closure $factory, array $overrides = []) : mixed
    {
        $reflection = new ReflectionFunction(function: $factory);
        $arguments = [];

        if ($reflection->getNumberOfParameters() >= 1) {
            $arguments[] = $this->container ?? $this;
        }
        if ($reflection->getNumberOfParameters() >= 2) {
            $arguments[] = $overrides;
        }

        return $factory(...$arguments);
    }

    /**
     * @throws ReflectionException
     */
    private function applyExtenders(string $abstract, mixed $instance) : mixed
    {
        foreach ($this->registrations->getExtenders(abstract: $abstract) as $extender) {
            if (! $extender instanceof Closure) {
                continue;
            }

            $reflection = new ReflectionFunction(function: $extender);
            $arguments = [];

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

    private function storeResolved(string $abstract, object $instance, LifetimePlan $lifetime) : void
    {
        if ($lifetime->isShared()) {
            $this->scopes->setShared(
                abstract  : $abstract,
                instance  : $instance,
                disposable: $lifetime->disposable,
            );

            return;
        }

        if ($lifetime->isPooled()) {
            $this->scopes->setPooled(
                abstract        : $abstract,
                instance        : $instance,
                kind            : $lifetime->scopeKind(),
                maxSize         : $lifetime->poolSize,
                resetBeforeReuse: $lifetime->poolResetBeforeReuse,
                disposable      : $lifetime->disposable,
            );

            return;
        }

        if ($lifetime->isScoped()) {
            $this->scopes->setScoped(
                abstract  : $abstract,
                instance  : $instance,
                kind      : $lifetime->scopeKind(),
                disposable: $lifetime->disposable,
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function cacheStateFor(string $serviceId) : array
    {
        $snapshot = $this->scopes->snapshot();
        $checkedOut = false;

        foreach ($snapshot['frames'] as $frame) {
            if (in_array(needle: $serviceId, haystack: $frame['pooledServices'] ?? [], strict: true)) {
                $checkedOut = true;

                break;
            }
        }

        return [
            'cached'           => $this->scopes->has(abstract: $serviceId),
            'lazy'             => isset($this->lazyServices[$serviceId]),
            'deferred'         => $this->deferredProviders->isDeferred(serviceId: $serviceId),
            'scopeDepth'       => count(value: $snapshot['scoped']),
            'pooled'           => [
                'checkedOut' => $checkedOut,
                'available' => count(value: $snapshot['pooledAvailable'][$serviceId] ?? []),
                'stats'     => $snapshot['pooledStats'],
            ],
            'activeScopeKinds' => array_values(array: array_map(
                                                          callback: static fn (array $frame) : string => $frame['kind'],
                                                          array   : $snapshot['frames'],
                                                      )),
        ];
    }

    /**
     * @param list<string> $chain
     *
     * @return list<array<string, mixed>>
     */
    private function decorationDetailsFor(string $serviceId, array $chain): array
    {
        return array_map(
            callback: function (string $descriptor) use ($serviceId) : array {
                $metadata = $this->registrations->ownership(abstract: $descriptor)
                    ?? RegistrationMetadata::for(unitId: $descriptor);
                $registered = $this->registrations->has(abstract: $descriptor);

                return [
                    'descriptor' => $descriptor,
                    'registered' => $registered,
                    'owner'      => $metadata->ownerSlice,
                    'visibility' => $metadata->visibility,
                    'reason'     => $registered
                        ? 'decorator resolves through a registered ownership-aware unit'
                        : 'decorator is not a registered service and only has descriptor-level diagnostics',
                    'access' => $registered
                        ? $this->registrations->accessTo(
                            consumerId  : $serviceId,
                            dependencyId: $descriptor,
                        )
                        : null,
                ];
            },
            array   : $chain,
        );
    }

    /**
     * @param list<string> $serviceIds
     * @return array<string, list<string>>
     */
    private function buildDependencyGraph(array $serviceIds = []): array
    {
        $graph = [];
        $queue = $serviceIds !== []
            ? array_values(array: array_unique(array: $serviceIds))
            : array_keys(array: $this->registrations->all());

        while ( $queue !== []) {
            $serviceId = $this->registrations->resolveAlias(abstract: array_shift(array: $queue));
            if ($serviceId === '' || isset($graph[$serviceId])) {
                continue;
            }

            $graph[$serviceId] = [];
            $registration      = $this->registrations->get(abstract: $serviceId);
            $candidate         = $registration?->concrete;

            if ($candidate === null && class_exists(class: $serviceId)) {
                $candidate = $serviceId;
            }

            if (! is_string(value: $candidate) || ! class_exists(class: $candidate)) {
                continue;
            }

            try {
                $blueprint = $this->blueprints->createFor(class: $candidate);
                $dependencies = $this->dependenciesForWarmup(blueprint: $blueprint);
                sort(array: $dependencies);
                $graph[$serviceId] = $dependencies;

                foreach ($dependencies as $dependency) {
                    $queue[] = $dependency;
                }
            } catch (Throwable) {
                $graph[$serviceId] = [];
            }
        }

        ksort(array: $graph);

        return $graph;
    }

    /**
     * @return list<string>
     */
    private function dependenciesForWarmup(DependencyBlueprint $blueprint): array
    {
        $dependencies = [];

        foreach ($blueprint->constructor?->parameters ?? [] as $parameter) {
            if (is_string(value: $parameter['serviceId'] ?? null)) {
                $dependencies[] = $parameter['serviceId'];
            }
        }

        foreach ($blueprint->injectableProperties ?? [] as $property) {
            if (is_string(value: $property['serviceId'] ?? null)) {
                $dependencies[] = $property['serviceId'];
            }
        }

        foreach ($blueprint->injectableMethods ?? [] as $method) {
            foreach ($method['plan']->parameters as $parameter) {
                if (is_string(value: $parameter['serviceId'] ?? null)) {
                    $dependencies[] = $parameter['serviceId'];
                }
            }
        }

        return array_values(array: array_unique(array: $dependencies));
    }

    /**
     * @param array<string, list<string>> $graph
     * @return array<string, list<string>>
     */
    private function buildDependents(array $graph): array
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
            $items = array_values(array: array_unique(array: $items));
            sort(array: $items);
            $dependents[$serviceId] = $items;
        }

        ksort(array: $dependents);

        return $dependents;
    }

    /**
     * Returns whether one service id is present in compiled runtime artifacts.
     */
    public function isCompiled(string $id): bool
    {
        $resolved = $this->registrations->resolveAlias(abstract: $id);

        return $this->compiledRuntime->isCompiled(registrations: $this->registrations, serviceId: $resolved);
    }

    /**
     * Returns whether a compiled runtime is currently available.
     */
    public function isWarmedUp(): bool
    {
        return $this->compiledRuntime->isWarmedUp();
    }

    /**
     * Returns whether one service has been marked lazy.
     */
    public function isLazy(string $id): bool
    {
        $resolved = $this->registrations->resolveAlias(abstract: $id);

        return isset($this->lazyServices[$resolved]);
    }

    /**
     * @return list<array{consumer: string, target: string}>
     */
    private function contextualBindingsFor(string $serviceId): array
    {
        $matches = [];

        foreach ($this->registrations->contextual() as $consumer => $rules) {
            foreach ($rules as $needs => $give) {
                if ($this->registrations->resolveAlias(abstract: (string) $needs) !== $serviceId) {
                    continue;
                }

                $matches[] = [
                    'consumer' => $consumer,
                    'target'   => is_object(value: $give) ? $give::class : (string) $give,
                ];
            }
        }

        usort(
            array   : $matches,
            callback: static fn (array $left, array $right) : int => [$left['consumer'], $left['target']] <=> [$right['consumer'], $right['target']],
        );

        return $matches;
    }

    /**
     * @param array<string, list<string>> $dependents
     * @return list<string>
     */
    private function impactFor(string $serviceId, array $dependents): array
    {
        $queue    = $dependents[$serviceId] ?? [];
        $impacted = [];

        while ( $queue !== []) {
            $current = array_shift(array: $queue);
            if (! is_string(value: $current) || isset($impacted[$current])) {
                continue;
            }

            $impacted[$current] = true;

            foreach ($dependents[$current] ?? [] as $next) {
                $queue[] = $next;
            }
        }

        $ids = array_keys(array: $impacted);
        sort(array: $ids);

        return $ids;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    private function explainService(
        string                  $id,
        string                  $resolvedId,
        ?DependencyRegistration $registration,
        ?DependencyBlueprint    $blueprint,
        array                   $compiledState,
        array                   $compiledArtifact,
        array                   $cacheState,
        array                   $aliasChain,
        array                   $decorationChain,
    ) : array {
        $contextualBindings = $this->contextualBindingsFor(serviceId: $resolvedId);
        $ownership          = $registration?->metadata ?? RegistrationMetadata::for(unitId: $resolvedId);
        $conditions         = $this->conditionStateFor(metadata: $ownership);
        $overrides          = $this->registrations->overrideHistory(abstract: $resolvedId)[$resolvedId] ?? [];
        $fallback           = $compiledState['decision'] === 'dynamic'
            ? [
                'active' => true,
                'reason' => $compiledState['reason'],
            ]
            : [
                'active' => false,
                'reason' => 'compiled hot path is active',
            ];

        return [
            'failureChain'     => $this->failureChainFor(
                resolvedId      : $resolvedId,
                registration    : $registration,
                compiledState   : $compiledState,
                compiledArtifact: $compiledArtifact,
            ),
            'dependencyChain'  => $this->dependencyChainFor(serviceId: $resolvedId, seen: []),
            'contextualWinner' => [
                'activeConsumer' => null,
                'winner'         => null,
                'bindings'       => $contextualBindings,
                'reason'         => $contextualBindings === []
                    ? 'no contextual override is registered for this service'
                    : 'contextual overrides exist, but no active consumer selected one for this direct diagnostics request',
            ],
            'aliasExpansion' => [
                'requestedId' => $id,
                'resolvedId'  => $resolvedId,
                'chain'       => $aliasChain,
                'reason'      => count(value: $aliasChain) > 1
                    ? 'requested id resolves through the alias chain shown here'
                    : 'requested id is already canonical',
            ],
            'decoration'       => [
                'chain' => $decorationChain,
                'count' => count(value: $decorationChain),
                'reason' => $decorationChain === []
                    ? 'no decorators or extenders are registered for this service'
                    : 'decorators and extenders will run in the listed order',
            ],
            'cache'            => [
                'state'  => $cacheState,
                'reason' => match (true) {
                    $cacheState['cached']                           => 'service is already stored in shared or scoped runtime state',
                    ($cacheState['pooled']['checkedOut'] ?? false)  => 'service is currently checked out from the pooled lifetime bucket inside the active scope',
                    (($cacheState['pooled']['available'] ?? 0) > 0) => 'service has reusable instances waiting in the pooled lifetime bucket',
                    $cacheState['deferred']                         => 'service will boot through a deferred provider before the build path runs',
                    $cacheState['lazy']                             => 'a lazy proxy has been requested; the real service resolves on first use',
                    default                                         => 'service will resolve through a fresh build path and then enter lifetime storage if needed',
                },
            ],
            'compiled'         => [
                'state' => $compiledState,
                'reason' => $compiledState['reason'],
            ],
            'owner' => [
                'metadata' => $ownership->toArray(),
                'reason'   => 'ownership metadata explains who owns this unit, what slice it belongs to, and how visible it is',
            ],
            'override' => [
                'history' => $overrides,
                'reason'  => $overrides === []
                    ? 'no previous authored registration for this abstract was replaced'
                    : 'the service was rebound after one or more previous authored registrations; inspect the history to explain override posture',
            ],
            'conditions'       => [
                'state' => $conditions,
                'reason' => $conditions['active']
                    ? 'all active composition conditions match this registration'
                    : 'one or more environment, flag, tenant, region, or mode conditions exclude this registration',
            ],
            'fallback'         => $fallback,
            'blueprint' => [
                'available' => $blueprint !== null,
                'reason'    => $blueprint !== null
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
        ?DependencyRegistration $registration,
        array $compiledState,
        array $compiledArtifact,
    ) : array {
        $failures = [];

        if ($registration === null && ! class_exists(class: $resolvedId)) {
            $failures[] = "service [{$resolvedId}] is not registered and cannot be autowired";
        }

        if (($compiledState['decision'] ?? '') === 'dynamic' && is_string(value: $compiledState['reason'] ?? null)) {
            $failures[] = $compiledState['reason'];
        }

        foreach ($compiledArtifact['compatibilityIssues'] ?? [] as $issue) {
            if (is_string(value: $issue) && $issue !== '') {
                $failures[] = $issue;
            }
        }

        foreach ($compiledArtifact['warnings'] ?? [] as $warning) {
            if (is_string(value: $warning) && $warning !== '') {
                $failures[] = $warning;
            }
        }

        $failures = array_values(array: array_unique(array: $failures));
        sort(array: $failures);

        return $failures;
    }

    /**
     * @param list<string>  $seen
     *
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    private function dependencyChainFor(string $serviceId, array $seen): array
    {
        $resolvedId = $this->registrations->resolveAlias(abstract: $serviceId);
        if (in_array(needle: $resolvedId, haystack: $seen, strict: true)) {
            return [
                'serviceId' => $resolvedId,
                'cycle' => true,
                'dependencies' => [],
            ];
        }

        $registration = $this->registrations->get(abstract: $resolvedId);
        $candidate    = $registration?->concrete;

        if ($candidate === null && class_exists(class: $resolvedId)) {
            $candidate = $resolvedId;
        }

        $node = [
            'serviceId' => $resolvedId,
            'class'     => is_object(value: $candidate) ? $candidate::class : $candidate,
            'dependencies' => [],
        ];

        if (! is_string(value: $candidate) || ! class_exists(class: $candidate)) {
            return $node;
        }

        $blueprint = $this->blueprints->createFor(class: $candidate);
        $dependencies = $this->dependenciesForWarmup(blueprint: $blueprint);
        sort(array: $dependencies);
        $node['dependencies'] = array_map(
            callback: fn (string $dependency) : array => $this->dependencyChainFor(
                serviceId: $dependency,
                seen     : array_merge($seen, [$resolvedId]),
            ),
            array   : $dependencies,
        );

        return $node;
    }

    /**
     * Returns whether one alias is registered.
     */
    public function hasAlias(string $alias): bool
    {
        return $this->registrations->hasAlias(alias: $alias);
    }

    /**
     * Returns the current compile report when compilation is enabled.
     */
    public function compileReport(array $serviceIds = []) : ?CompileReport
    {
        return $this->compiledRuntime->report(serviceIds: $serviceIds);
    }

    public function sliceBoundaryMode(): string
    {
        return $this->sliceBoundaryMode;
    }

    public function asyncTarget(): string
    {
        return $this->asyncTarget;
    }

    /**
     * Returns the current disposable runtime state report.
     */
    public function runtimeReport() : RuntimeReport
    {
        $lazyServices = array_keys(array: $this->lazyServices);
        sort(array: $lazyServices);
        $deferredProviders = $this->deferredProviders->services();
        ksort(array: $deferredProviders);
        $scopeSnapshot      = $this->scopes->snapshot();
        $sharedServiceCount = count(value: $scopeSnapshot['shared']);
        $scopedServiceCount = array_sum(array: array_map(
                                                   callback: static fn (array $scope) : int => count(value: $scope),
                                                   array   : $scopeSnapshot['scoped'],
        ));

        return new RuntimeReport(
            registrationRevision: $this->registrations->revision(),
            compiledRevision    : $this->compiledRuntime->compiledRevision(),
            compiledAttached    : $this->compiledRuntime->isAttached(),
            warmedUp            : $this->compiledRuntime->isWarmedUp(),
            executionMode       : (string) ($this->compiledRuntime->summary()['executionMode'] ?? CreateContainerConfig::EXECUTION_MODE_DYNAMIC),
            asyncTarget         : $this->asyncTarget,
            sliceBoundaryMode   : $this->sliceBoundaryMode,
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
                                      'shared'          => $this->summarizeScopeEntries(entries: $scopeSnapshot['shared']),
                                      'scopedDepth'     => count(value: $scopeSnapshot['scoped']),
                                      'frames'          => $scopeSnapshot['frames'],
                                      'pooled'          => $scopeSnapshot['pooled'],
                                      'pooledAvailable' => $scopeSnapshot['pooledAvailable'],
                                      'pooledStats'     => $scopeSnapshot['pooledStats'],
                                      'scoped'          => array_map(
                                          callback: fn (array $scope) : array => $this->summarizeScopeEntries(entries: $scope),
                                          array   : $scopeSnapshot['scoped'],
                                      ),
                                  ],
            hotPath             : $this->compiledRuntime->summary(),
            compiled            : $this->compiledRuntime->report(),
        );
    }

    /**
     * @param array<string, mixed>  $entries
     * @return array<string, string>
     */
    private function summarizeScopeEntries(array $entries): array
    {
        $summary = [];

        foreach ($entries as $serviceId => $instance) {
            $summary[$serviceId] = is_object(value: $instance) ? $instance::class : get_debug_type(value: $instance);
        }

        ksort(array: $summary);

        return $summary;
    }

    /**
     * @param array<string, mixed>  $context
     *
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function debugPlanInContext(string $id, array $context) : array
    {
        $plan = $this->debugPlan(id: $id);
        $slice = SliceContext::from(context: $context);

        if ($slice === '') {
            return $plan;
        }

        $plan['viewAccess'] = $this->registrations->sliceAccessTo(
            viewerSlice: $slice,
            serviceId  : (string) ($plan['resolvedId'] ?? $id),
        );

        return $plan;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function debugPlan(string $id) : array
    {
        $resolved     = $this->registrations->resolveAlias(abstract: $id);
        $registration = $this->registrations->get(abstract: $resolved);
        $blueprintClass = is_string(value: $registration?->concrete) && class_exists(class: $registration->concrete)
            ? $registration->concrete
            : (class_exists(class: $resolved) ? $resolved : null);

        if ($resolved === '' || ! is_string(value: $blueprintClass)) {
            return ['id' => $id, 'resolvedId' => $resolved, 'constructor' => null, 'methods' => []];
        }

        $blueprint = $this->blueprints->createFor(class: $blueprintClass);

        return [
            'id'          => $id,
            'resolvedId' => $resolved,
            'constructor' => $blueprint->constructor?->parameters ?? [],
            'methods'     => $blueprint->injectableMethods,
            'properties'  => $blueprint->injectableProperties,
            'shared'      => $blueprint->shared,
            'deferred'    => $this->registrations->get(abstract: $resolved)?->deferred ?? false,
        ];
    }

    /**
     * @param array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function debugGovernanceInContext(string $id, array $context): array
    {
        $report = $this->debugGovernance(id: $id);
        $slice  = SliceContext::from(context: $context);

        if ($slice === '') {
            return $report;
        }

        if ($id !== '') {
            $resolved = $this->registrations->resolveAlias(abstract: $id);

            return array_merge(
                $report,
                [
                    'sliceView' => $this->registrations->sliceView(slice: $slice),
                    'viewAccess' => $this->registrations->sliceAccessTo(viewerSlice: $slice, serviceId: $resolved),
                ],
            );
        }

        $visible            = array_fill_keys(keys: $this->visibleServiceIdsForSlice(slice: $slice), value: true);
        $report['findings'] = array_intersect_key($report['findings'] ?? [], $visible);
        $report['sliceView'] = $this->registrations->sliceView(slice: $slice);

        return $report;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function debugGovernance(string $id = '') : array
    {
        $graph      = $this->buildDependencyGraph(serviceIds: $id !== '' ? [$id] : []);
        $dependents = $this->buildDependents(graph: $graph);
        $report     = $this->governanceReport(graph: $graph, dependents: $dependents);

        if ($id === '') {
            return $report;
        }

        $resolved = $this->registrations->resolveAlias(abstract: $id);

        return [
            'service'  => $resolved,
            'profile'  => $report['profile'],
            'failMode' => $report['failMode'],
            'blocked'  => $report['blocked'],
            'summary'  => $report['summary'],
            'findings' => $report['findings'][$resolved] ?? [],
        ];
    }

    /**
     * @param array<string, list<string>>  $graph
     * @param array<string, list<string>>  $dependents
     *
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    private function governanceReport(array $graph, array $dependents): array
    {
        return $this->governor->report(
            graph        : $graph,
            dependents   : $dependents,
            registrations: $this->registrations,
            blueprints   : $this->blueprints,
            policy       : $this->policy,
            environment  : $this->environment,
        );
    }

    /**
     * @return list<string>
     */
    private function visibleServiceIdsForSlice(string $slice): array
    {
        return array_values(array: array_map(
                                       callback: static fn (array $row) : string => $row['serviceId'],
                                       array   : $this->registrations->sliceView(slice: $slice)['visible'] ?? [],
                                   ));
    }

    /**
     * @param array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function debugSliceInContext(string $slice, array $context): array
    {
        $active = $slice !== '' ? $slice : SliceContext::from(context: $context);

        return $this->debugSlice(slice: $active);
    }

    /**
     * @return array<string, mixed>
     */
    public function debugSlice(string $slice = ''): array
    {
        if ($slice === '' || SliceContext::isRoot(slice: $slice)) {
            $visible = [];
            $hidden = [];

            foreach ($this->registrations->all() as $serviceId => $registration) {
                $access = $this->registrations->topLevelAccessTo(serviceId: $serviceId);
                $row    = [
                    'serviceId' => $serviceId,
                    'ownerSlice' => $registration->metadata->ownerSlice,
                    'visibility' => $registration->metadata->visibility,
                    'reason' => $access['reason'],
                ];

                if ($access['allowed'] ?? false) {
                    $visible[] = $row;

                    continue;
                }

                $hidden[] = $row;
            }

            usort(array: $visible, callback: static fn (array $left, array $right) : int => $left['serviceId'] <=> $right['serviceId']);
            usort(array: $hidden, callback: static fn (array $left, array $right) : int => $left['serviceId'] <=> $right['serviceId']);

            return [
                'slice'    => SliceContext::ROOT,
                'exists' => true,
                'manifest' => [
                    'slice'      => SliceContext::ROOT,
                    'category' => 'root',
                    'categories' => ['root'],
                    'services'   => array_column(array: $visible, column_key: 'serviceId'),
                    'exports'    => array_column(array: $visible, column_key: 'serviceId'),
                    'public'     => array_filter(
                            array   : $visible,
                            callback: static fn (array $row) : bool => $row['visibility'] === RegistrationVisibility::PUBLIC,
                    )
                            |> array_values(...)
                            |> (static fn ($x) => array_map(callback: static fn (array $row) : string => $row['serviceId'], array: $x))
                            |> array_values(...),
                    'shared'     => array_filter(
                            array   : $visible,
                            callback: static fn (array $row) : bool => $row['visibility'] === RegistrationVisibility::SHARED,
                    )
                            |> array_values(...)
                            |> (static fn ($x) => array_map(callback: static fn (array $row) : string => $row['serviceId'], array: $x))
                            |> array_values(...),
                    'private'    => [],
                    'internal'   => [],
                    'imports'    => [],
                ],
                'visible'  => $visible,
                'hidden' => $hidden,
            ];
        }

        return $this->registrations->sliceView(slice: $slice);
    }

    /**
     * @param array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function debugImportsInContext(string $slice, array $context): array
    {
        $active = $slice !== '' ? $slice : SliceContext::from(context: $context);

        return $this->debugImports(slice: $active);
    }

    /**
     * @return array<string, mixed>
     */
    public function debugImports(string $slice = '') : array
    {
        $view     = $this->debugSlice(slice: $slice);
        $manifest = $view['manifest'] ?? [];
        $imports = [];

        foreach ($manifest['imports'] ?? [] as $import) {
            $imports[$import] = [
                'slice'          => $import,
                'manifest'       => $this->registrations->sliceManifest(slice: $import),
                'visibleExports' => array_filter(
                        array   : $view['visible'] ?? [],
                        callback: static fn (array $row) : bool => $row['ownerSlice'] === $import,
                )
                        |> array_values(...)
                        |> (static fn ($x) => array_map(callback: static fn (array $row) : string => $row['serviceId'], array: $x))
                        |> array_values(...),
            ];
        }

        ksort(array: $imports);

        return [
            'slice'   => $view['slice'] ?? $slice,
            'imports' => array_values(array: $imports),
        ];
    }

    /**
     * @param array<string, mixed>  $context
     *
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function debugExportsInContext(string $slice, array $context): array
    {
        $active = $slice !== '' ? $slice : SliceContext::from(context: $context);

        return $this->debugExports(slice: $active);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function debugExports(string $slice = '') : array
    {
        $view     = $this->debugSlice(slice: $slice);
        $manifest = $view['manifest'] ?? [];
        $exports = [];

        foreach ($manifest['exports'] ?? [] as $serviceId) {
            $exports[] = [
                'serviceId' => $serviceId,
                'description' => $this->describeService(id: $serviceId),
            ];
        }

        usort(array: $exports, callback: static fn (array $left, array $right) : int => $left['serviceId'] <=> $right['serviceId']);

        return [
            'slice' => $view['slice'] ?? $slice,
            'exports' => $exports,
        ];
    }

    /**
     * @param list<string>           $serviceIds
     * @param  array<string, mixed>  $context
     *
     * @return list<string>
     *
     * @throws ReflectionException
     * @throws ReflectionException
     */
    public function validateInContext(array $serviceIds, array $context): array
    {
        $slice = SliceContext::from(context: $context);
        if ($slice === '') {
            return $this->validate(serviceIds: $serviceIds);
        }

        $visibleIds = $this->visibleServiceIdsForSlice(slice: $slice);
        $targets = $serviceIds === []
            ? $visibleIds
            : array_map(
                callback: fn (string $serviceId) : string => $this->registrations->resolveAlias(abstract: $serviceId),
                array   : $serviceIds,
            )
                |> (static fn ($x) => array_intersect($visibleIds, $x))
                |> array_values(...);

        $issues = $this->validate(serviceIds: $targets);
        foreach ($this->debugVisibilityViolationsInContext(serviceIds: $targets, context: $context)['violations'] ?? [] as $violation) {
            $issues[] = "Slice [{$slice}] visibility violation for [{$violation['dependencyId']}]: {$violation['reason']}.";
        }

        return array_values(array: array_unique(array: $issues));
    }

    /**
     * @param  list<string> $serviceIds
     *
     * @return list<string>
     *
     * @throws ReflectionException
     */
    public function validate(array $serviceIds = []): array
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
                    issues   : $issues,
                );
            } catch (Throwable $throwable) {
                $issues[] = "Service [{$class}] cannot be analyzed: {$throwable->getMessage()}";
            }
        }

        foreach ($this->registrations->allAliases() as $alias => $target) {
            if (
                ! $this->registrations->has(abstract: $target)
                && ! $this->deferredProviders->isDeferred(serviceId: $target)
                && ! class_exists(class: $target)
                && ! interface_exists(interface: $target)
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

        $validationServiceIds = $graph
                |> array_keys(...)
                |> (fn ($x) => array_merge($x, $serviceIds !== [] ? array_map(callback: fn (string $serviceId) : string => $this->registrations->resolveAlias(abstract: $serviceId), array: $serviceIds) : array_keys(array: $this->registrations->all())))
                |> array_unique(...)
                |> array_values(...);

        $this->validateSliceAccess(graph: $graph, issues: $issues);
        $this->validateLifetimeAccess(graph: $graph, issues: $issues);
        $this->validateSliceContracts(issues: $issues);
        $this->validateDuplicateConcepts(issues: $issues);
        $this->validateOverrideCollisions(issues: $issues);
        $this->validateDecoratorConflicts(issues: $issues);
        $this->validateGroupConflicts(issues: $issues);
        $this->validateEnvironmentProfiles(serviceIds: $validationServiceIds, issues: $issues);
        $this->validateDisposalSemantics(issues: $issues);
        $governance = $this->governanceReport(
            graph     : $graph,
            dependents: $this->buildDependents(graph: $graph),
        );
        foreach ($this->governor->messages(report: $governance) as $message) {
            $issues[] = $message.'.';
        }
        if (($governance['blocked'] ?? false) === true) {
            $issues[] = 'Policy governance blocked the current composition under fail-closed enforcement.';
        }

        return array_values(array: array_unique(array: $issues));
    }

    /**
     * @param list<string> $serviceIds
     */
    private function bootDeferredProvidersFor(array $serviceIds): void
    {
        $this->deferredProviders->bootFor(
            serviceIds   : $serviceIds,
            registrations: $this->registrations,
            metrics      : $this->telemetry->metrics(),
        );
    }

    /**
     * @param  list<string> $serviceIds
     *
     * @return list<string>
     *
     * @throws ReflectionException
     */
    private function classesForValidation(array $serviceIds): array
    {
        $queue = [];

        if ($serviceIds !== []) {
            foreach (array_values(array: array_unique(array: $serviceIds)) as $serviceId) {
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
            $serviceId = $this->registrations->resolveAlias(abstract: array_shift(array: $queue));
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

            if ($candidate === null && class_exists(class: $serviceId)) {
                $candidate = $serviceId;
            }

            if (! is_string(value: $candidate) || ! class_exists(class: $candidate)) {
                continue;
            }

            $blueprint = $this->blueprints->createFor(class: $candidate);
            foreach ($this->dependenciesForWarmup(blueprint: $blueprint) as $dependency) {
                $queue[] = $dependency;
            }
        }

        return array_filter(
                array   : $classes,
                callback: static fn (string $class): bool => class_exists(class: $class),
        )
                |> array_unique(...)
                |> array_values(...);
    }

    private function bootDeferredProviderIfNeeded(string $serviceId): void
    {
        $this->deferredProviders->bootIfNeeded(
            serviceId: $serviceId,
            metrics  : $this->telemetry->metrics(),
        );
    }

    private function isCompilable(string $serviceId): bool
    {
        $registration = $this->registrations->get(abstract: $serviceId);
        $candidate = $registration?->concrete;

        if (is_string(value: $candidate) && class_exists(class: $candidate)) {
            return true;
        }

        if ($candidate instanceof Closure || is_object(value: $candidate)) {
            return true;
        }

        return class_exists(class: $serviceId);
    }

    /**
     * @param  list<string> $issues
     * @return list<string>
     */
    private function dependenciesForValidation(string $serviceId, DependencyBlueprint $blueprint, array &$issues): array
    {
        $dependencies = [];

        foreach ($blueprint->constructor?->parameters ?? [] as $parameter) {
            if (! is_string(value: $parameter['serviceId'] ?? null)) {
                continue;
            }

            $dependency = $this->registrations->resolveAlias(abstract: $parameter['serviceId']);
            $dependencies[] = $dependency;

            if (! $this->registrations->has(abstract: $dependency) && ! class_exists(class: $dependency)) {
                $issues[] = "Service [{$serviceId}] depends on missing service [{$dependency}].";
            }
        }

        foreach ($blueprint->injectableProperties ?? [] as $property) {
            if (! is_string(value: $property['serviceId'] ?? null)) {
                continue;
            }

            $dependency = $this->registrations->resolveAlias(abstract: $property['serviceId']);
            $dependencies[] = $dependency;

            if (! $this->registrations->has(abstract: $dependency) && ! class_exists(class: $dependency)) {
                $issues[] = "Service [{$serviceId}] injects missing property dependency [{$dependency}].";
            }
        }

        foreach ($blueprint->injectableMethods ?? [] as $method) {
            foreach ($method['plan']->parameters as $parameter) {
                if (! is_string(value: $parameter['serviceId'] ?? null)) {
                    continue;
                }

                $dependency = $this->registrations->resolveAlias(abstract: $parameter['serviceId']);
                $dependencies[] = $dependency;

                if (! $this->registrations->has(abstract: $dependency) && ! class_exists(class: $dependency)) {
                    $issues[] = "Service [{$serviceId}] injects missing method dependency [{$dependency}].";
                }
            }
        }

        return array_values(array: array_unique(array: $dependencies));
    }

    private function isResolvableBinding(mixed $binding): bool
    {
        if ($binding instanceof Closure || is_object(value: $binding)) {
            return true;
        }

        if (! is_string(value: $binding) || $binding === '') {
            return false;
        }

        return class_exists(class: $binding)
            || interface_exists(interface: $binding)
            || $this->registrations->has(abstract: $binding)
            || str_contains(haystack: $binding, needle: '::')
            || str_contains(haystack: $binding, needle: '@');
    }

    /**
     * @param  array<string, list<string>> $graph
     * @return list<string>
     */
    private function detectCircularDependencies(array $graph): array
    {
        $issues = [];
        $state = [];

        foreach (array_keys(array: $graph) as $serviceId) {
            $this->visitDependency(
                serviceId: $serviceId,
                graph    : $graph,
                state    : $state,
                stack    : [],
                issues   : $issues,
            );
        }

        return array_values(array: array_unique(array: $issues));
    }

    /**
     * @param array<string, list<string>> $graph
     * @param array<string, string>       $state
     * @param list<string>  $stack
     * @param  list<string>  $issues
     */
    private function visitDependency(
        string $serviceId,
        array $graph,
        array &$state,
        array $stack,
        array &$issues,
    ): void {
        $currentState = $state[$serviceId] ?? 'new';
        if ($currentState === 'done') {
            return;
        }

        if ($currentState === 'visiting') {
            $cycleStart = array_search(needle: $serviceId, haystack: $stack, strict: true);
            $path       = $cycleStart === false ? array_merge($stack, [$serviceId]) : array_slice(array: $stack, offset: $cycleStart);
            $path[]     = $serviceId;
            $issues[]   = 'Circular dependency detected: '.implode(separator: ' -> ', array: $path);

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
                issues   : $issues,
            );
        }

        $state[$serviceId] = 'done';
    }

    /**
     * @param array<string, list<string>> $graph
     * @param list<string>                $issues
     */
    private function validateSliceAccess(array $graph, array &$issues): void
    {
        foreach ($graph as $serviceId => $dependencies) {
            foreach ($dependencies as $dependency) {
                $access = $this->registrations->accessTo(
                    consumerId  : $serviceId,
                    dependencyId: $dependency,
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
     * @param list<string>                $issues
     */
    private function validateLifetimeAccess(array $graph, array &$issues): void
    {
        foreach ($graph as $serviceId => $dependencies) {
            $consumerLifetime = LifetimePlan::fromRegistration(
                serviceId   : $serviceId,
                registration: $this->registrations->get(abstract: $serviceId),
            );

            foreach ($dependencies as $dependency) {
                $dependencyLifetime = LifetimePlan::fromRegistration(
                    serviceId   : $dependency,
                    registration: $this->registrations->get(abstract: $dependency),
                );

                if (
                    $consumerLifetime->isShared()
                    && $dependencyLifetime->isScoped()
                ) {
                    $issues[] = "Shared service [{$serviceId}] captures scoped dependency [{$dependency}] which can escape its scope.";
                }

                if ($consumerLifetime->isPooled() && $dependencyLifetime->isScoped()) {
                    $issues[] = "Pooled service [{$serviceId}] captures scoped dependency [{$dependency}] which would leak scope-bound state across pool reuse.";
                }

                if ($consumerLifetime->isPooled() && $dependencyLifetime->isTransient()) {
                    $issues[] = "Pooled service [{$serviceId}] captures transient dependency [{$dependency}] which would be reused implicitly across pool checkouts.";
                }

                if ($consumerLifetime->isPooled() && $dependencyLifetime->isPooled()) {
                    $issues[] = "Pooled service [{$serviceId}] captures pooled dependency [{$dependency}] which would retain another pooled instance across pool reuse.";
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

    private function lifetimeRank(LifetimePlan $lifetime): int
    {
        if ($lifetime->isShared()) {
            return 100;
        }

        if ($lifetime->isPooled()) {
            return 90;
        }

        if ($lifetime->isTransient()) {
            return 0;
        }

        return match ($lifetime->scopeKind()) {
            ScopeKind::TENANT    => 40,
            ScopeKind::JOB => 30,
            ScopeKind::REQUEST   => 20,
            ScopeKind::OPERATION => 10,
            ScopeKind::ANY => 5,
            default              => 1,
        };
    }

    /**
     * @param list<string> $issues
     */
    private function validateSliceContracts(array &$issues): void
    {
        $manifests = $this->registrations->sliceManifests();

        foreach ($manifests as $slice => $manifest) {
            if (($manifest['category'] ?? '') === 'mixed') {
                $issues[] = "Slice [{$slice}] mixes multiple categories [" . implode(separator: ', ', array: $manifest['categories'] ?? []).'].';
            }

            foreach ($manifest['imports'] ?? [] as $import) {
                if (! isset($manifests[$import])) {
                    $issues[] = "Slice [{$slice}] imports missing slice [{$import}].";
                }
            }

            foreach ($manifest['exports'] ?? [] as $serviceId) {
                $registration = $this->registrations->get(abstract: $serviceId);
                $visibility   = $registration?->metadata->visibility ?? RegistrationVisibility::PUBLIC;

                if (in_array(needle: $visibility, haystack: [RegistrationVisibility::PRIVATE, RegistrationVisibility::INTERNAL], strict: true)) {
                    $issues[] = "Service [{$serviceId}] is exported by slice [{$slice}] but keeps non-exportable visibility [{$visibility}].";
                }
            }
        }
    }

    /**
     * @param list<string> $issues
     */
    private function validateDuplicateConcepts(array &$issues): void
    {
        foreach ($this->registrations->duplicateConcepts() as $duplicate) {
            $services = array_map(
                callback: static fn (array $service) : string => $service['serviceId'].'@'.$service['ownerSlice'],
                array   : $duplicate['services'],
            );

            $issues[] = "Duplicate concept [{$duplicate['concept']}] is owned by multiple units: ".implode(separator: ', ', array: $services) . '.';
        }
    }

    /**
     * @param list<string> $issues
     */
    private function validateOverrideCollisions(array &$issues): void
    {
        foreach ($this->registrations->duplicateConcepts() as $duplicate) {
            $services = array_column(array: $duplicate['services'], column_key: 'serviceId');

            for ($index = 0; $index < count(value: $services); $index++) {
                for ($next = $index + 1; $next < count(value: $services); $next++) {
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
            if (! $current instanceof DependencyRegistration) {
                continue;
            }

            $currentMetadata = $current->metadata;

            foreach ($history as $previous) {
                $previousMetadataState = $previous['metadata'] ?? null;
                if (! is_array(value: $previousMetadataState)) {
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
                    $issues[]       = "Override collision for service [{$abstract}] changes ownership posture from "
                        . "[{$previousMetadata->ownerSlice}/{$previousMetadata->category}/{$previousMetadata->visibility}] to "
                        . "[{$currentMetadata->ownerSlice}/{$currentMetadata->category}/{$currentMetadata->visibility}] "
                        . "under overlapping composition conditions. Override source [{$overrideSource}].";
                }
            }
        }
    }

    private function conditionsOverlap(RegistrationMetadata $left, RegistrationMetadata $right): bool
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
    private function listsOverlap(array $left, array $right): bool
    {
        if ($left === [] || $right === []) {
            return true;
        }

        return array_intersect($left, $right) !== [];
    }

    /**
     * @param list<string> $issues
     */
    private function validateDecoratorConflicts(array &$issues): void
    {
        foreach ($this->registrations->all() as $serviceId => $registration) {
            $chain = $this->registrations->decorationChain(abstract: $serviceId);
            if ($chain === []) {
                continue;
            }

            if (count(value: $chain) !== count(value: array_unique(array: $chain))) {
                $issues[] = "Service [{$serviceId}] has duplicate decorator descriptors in its decoration chain.";
            }

            foreach ($chain as $descriptor) {
                if (! is_string(value: $descriptor) || ! $this->registrations->has(abstract: $descriptor)) {
                    continue;
                }

                $access = $this->registrations->accessTo(
                    consumerId  : $serviceId,
                    dependencyId: $descriptor,
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
    private function validateGroupConflicts(array &$issues): void
    {
        foreach ($this->registrations->groupIndex() as $group => $items) {
            $orders = [];

            foreach ($items as $item) {
                $orders[$item['order']][] = $item['serviceId'];
            }

            foreach ($orders as $order => $serviceIds) {
                if (count(value: $serviceIds) <= 1) {
                    continue;
                }

                sort(array: $serviceIds);
                $issues[] = "Group [{$group}] uses duplicate order [{$order}] across services [".implode(separator: ', ', array: $serviceIds) . '].';
            }
        }
    }

    /**
     * @param list<string>  $serviceIds
     * @param list<string>  $issues
     */
    private function validateEnvironmentProfiles(array $serviceIds, array &$issues): void
    {
        foreach (array_values(array: array_unique(array: $serviceIds)) as $serviceId) {
            $registration = $this->registrations->get(abstract: $serviceId);
            $metadata = $registration?->metadata;

            if (! $metadata instanceof RegistrationMetadata) {
                continue;
            }

            $conditions = $this->conditionStateFor(metadata: $metadata);
            if (! $conditions['active']) {
                $issues[] = "Service [{$serviceId}] is inactive for the current composition: " . implode(separator: '; ', array: $conditions['reasons']) . '.';
            }
        }
    }

    /**
     * @param list<string> $issues
     */
    private function validateDisposalSemantics(array &$issues): void
    {
        foreach ($this->registrations->all() as $serviceId => $registration) {
            $lifetime = LifetimePlan::fromRegistration(
                serviceId   : $serviceId,
                registration: $registration,
            );

            if ($lifetime->isPooled()) {
                if ($lifetime->warm || $lifetime->lazy) {
                    $issues[] = "Service [{$serviceId}] uses pooled lifetime and cannot also be marked warm or lazy.";
                }

                $candidate = $registration->concrete;
                if (is_object(value: $candidate) && ! ($candidate instanceof Closure)) {
                    $issues[] = "Service [{$serviceId}] uses pooled lifetime but is already a prebuilt instance; pooled services must be container-owned builds.";
                }

                if (is_string(value: $candidate) && class_exists(class: $candidate) && $lifetime->poolResetBeforeReuse && ! is_subclass_of(object_or_class: $candidate, class: ResettableInterface::class)) {
                    $issues[] = "Service [{$serviceId}] uses pooled lifetime with reset-before-reuse enabled but class [{$candidate}] does not implement ResettableInterface.";
                }
            }

            if ($lifetime->disposable && $lifetime->isTransient()) {
                $issues[] = "Service [{$serviceId}] is marked disposable but uses transient lifetime, so the container cannot own its disposal boundary.";
            }

            if (! $lifetime->disposable) {
                continue;
            }

            $candidate = $registration->concrete;
            if (! is_string(value: $candidate) || ! class_exists(class: $candidate)) {
                continue;
            }

            if (
                ! is_subclass_of(object_or_class: $candidate, class: DisposableInterface::class)
                && ! method_exists(object_or_class: $candidate, method: 'dispose')
            ) {
                $issues[] = "Service [{$serviceId}] is marked disposable but class [{$candidate}] does not expose dispose() or implement DisposableInterface.";
            }
        }
    }

    /**
     * @param list<string>          $serviceIds
     * @param  array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function debugVisibilityViolationsInContext(array $serviceIds, array $context): array
    {
        $slice = SliceContext::from(context: $context);
        if ($slice === '') {
            return $this->debugVisibilityViolations(serviceIds: $serviceIds);
        }

        $targets = $serviceIds !== []
            ? $serviceIds
            : array_values(array: array_map(
                                      callback: static fn (array $row) : string => $row['serviceId'],
                                      array   : $this->debugSlice(slice: $slice)['visible'] ?? [],
            ));

        $violations = [];
        foreach (($this->debugVisibilityViolations(serviceIds: $targets)['violations'] ?? []) as $violation) {
            $consumerSlice = (string) (($violation['consumer']['ownerSlice'] ?? ''));
            if ($consumerSlice !== $slice) {
                continue;
            }

            $violations[] = $violation;
        }

        foreach (($this->debugSlice(slice: $slice)['hidden'] ?? []) as $hidden) {
            $violations[] = [
                'consumerId' => $slice,
                'dependencyId' => $hidden['serviceId'],
                'reason'       => $hidden['reason'],
                'consumer'     => ['ownerSlice' => $slice, 'visibility' => 'slice-view'],
                'dependency' => [
                    'ownerSlice' => $hidden['ownerSlice'],
                    'visibility' => $hidden['visibility'],
                ],
            ];
        }

        return [
            'slice'      => $slice,
            'violations' => $violations,
            'count'      => count(value: $violations),
        ];
    }

    /**
     * @param  list<string> $serviceIds
     * @return array<string, mixed>
     */
    public function debugVisibilityViolations(array $serviceIds = []) : array
    {
        $graph = $this->buildDependencyGraph(serviceIds: $serviceIds);
        $violations = [];

        foreach ($graph as $serviceId => $dependencies) {
            foreach ($dependencies as $dependency) {
                $access = $this->registrations->accessTo(
                    consumerId  : $serviceId,
                    dependencyId: $dependency,
                );
                if ($access['allowed'] ?? false) {
                    continue;
                }

                $violations[] = [
                    'consumerId' => $serviceId,
                    'dependencyId' => $dependency,
                    'reason' => $access['reason'],
                    'consumer' => $access['consumer'] ?? [],
                    'dependency' => $access['dependency'] ?? [],
                ];
            }
        }

        usort(
            array   : $violations,
            callback: static fn (array $left, array $right): int => [$left['consumerId'], $left['dependencyId']]
                <=> [$right['consumerId'], $right['dependencyId']],
        );

        return [
            'violations' => $violations,
            'count'      => count(value: $violations),
        ];
    }

    /**
     * @param  array<string, mixed> $context
     * @return array<string, string>
     */
    public function debugAliasesInContext(array $context): array
    {
        $slice = SliceContext::from(context: $context);
        if ($slice === '') {
            return $this->debugAliases();
        }

        $aliases = [];
        foreach ($this->registrations->allAliases() as $alias => $target) {
            if (! $this->registrations->allowsSliceAccess(viewerSlice: $slice, serviceId: $target)) {
                continue;
            }

            $aliases[$alias] = $target;
        }

        ksort(array: $aliases);

        return $aliases;
    }

    /**
     * @return array<string, string>
     */
    public function debugAliases(): array
    {
        return $this->registrations->allAliases();
    }

    /**
     * @param  array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function debugScopeInContext(array $context): array
    {
        $scope = $this->debugScope();
        $slice = SliceContext::from(context: $context);
        if ($slice === '') {
            return $scope;
        }

        $visibleIds               = array_fill_keys(keys: $this->visibleServiceIdsForSlice(slice: $slice), value: true);
        $scope['shared']          = array_intersect_key($scope['shared'], $visibleIds);
        $scope['pooled'] = array_intersect_key($scope['pooled'], $visibleIds);
        $scope['pooledAvailable'] = array_intersect_key($scope['pooledAvailable'], $visibleIds);
        $scope['scoped']          = array_map(
            callback: static fn (array $frame): array => array_intersect_key($frame, $visibleIds),
            array   : $scope['scoped'],
        );
        $scope['sliceView'] = $this->registrations->sliceView(slice: $slice);

        return $scope;
    }

    /**
     * @return array<string, mixed>
     */
    public function debugScope(): array
    {
        $snapshot = $this->scopes->snapshot();

        return [
            'depth'           => count(value: $snapshot['scoped']),
            'frames'          => $snapshot['frames'],
            'shared'          => $this->summarizeScopeEntries(entries: $snapshot['shared']),
            'pooled' => $snapshot['pooled'],
            'pooledAvailable' => $snapshot['pooledAvailable'],
            'pooledStats'     => $snapshot['pooledStats'],
            'scoped'          => array_map(
                callback: fn (array $scope) : array => $this->summarizeScopeEntries(entries: $scope),
                array   : $snapshot['scoped'],
            ),
        ];
    }

    /**
     * @param  array<string, mixed> $context
     *
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function debugGroupInContext(string $group, array $context): array
    {
        $report = $this->debugGroup(group: $group);
        $slice = SliceContext::from(context: $context);

        if ($slice === '') {
            return $report;
        }

        $visible            = array_fill_keys(keys: $this->visibleServiceIdsForSlice(slice: $slice), value: true);
        $report['items']    = array_values(array: array_filter(
                                                      array   : $report['items'],
                                                      callback: static fn (array $item) : bool => isset($visible[(string) ($item['serviceId'] ?? '')]),
        ));
        $report['services'] = array_values(array: array_filter(
                                                      array   : $report['services'],
                                                      callback: static fn (array $service) : bool => isset($visible[(string) ($service['resolvedId'] ?? '')]),
        ));
        $report['sliceView'] = $this->registrations->sliceView(slice: $slice);

        return $report;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function debugGroup(string $group): array
    {
        $serviceIds = $this->registrations->getGroupedIds(group: $group);
        $items = [];

        foreach ($serviceIds as $serviceId) {
            $registration = $this->registrations->get(abstract: $serviceId);
            $items[] = [
                'serviceId' => $serviceId,
                'order' => $registration?->groupOrder ?? 0,
                'ownerSlice' => $registration?->metadata->ownerSlice ?? 'default',
                'visibility' => $registration?->metadata->visibility ?? RegistrationVisibility::PUBLIC,
            ];
        }

        return [
            'group' => $group,
            'items'    => $items,
            'services' => array_map(
                callback: fn (string $serviceId): array => $this->describeService(id: $serviceId),
                array   : $serviceIds,
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     *
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function debugSelectionInContext(string $id, array $context) : array
    {
        $report = $this->debugSelection(id: $id);
        $slice = SliceContext::from(context: $context);

        if ($slice === '') {
            return $report;
        }

        $resolved            = (string) ($report['service'] ?? $id);
        $report['sliceView'] = $this->registrations->sliceView(slice: $slice);
        $report['viewAccess'] = $this->registrations->sliceAccessTo(viewerSlice: $slice, serviceId: $resolved);

        return $report;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function debugSelection(string $id) : array
    {
        $description  = $this->describeService(id: $id);
        $resolved = (string) ($description['resolvedId'] ?? $id);
        $registration = $this->registrations->get(abstract: $resolved);
        $groupName = $registration?->group;
        $tags         = $registration?->tags ?? [];

        return [
            'service'            => $resolved,
            'aliasChain'         => $description['aliasChain'] ?? [],
            'conditions' => $description['conditions'] ?? [],
            'contextualBindings' => $description['contextualBindings'] ?? [],
            'group'              => $groupName !== null ? $this->debugGroup(group: $groupName) : null,
            'tags'               => array_map(
                callback: fn (string $tag): array => $this->debugTags(tag: $tag),
                array   : $tags,
            ),
            'decorators'         => $description['decorationDetails'] ?? [],
            'compiledState'      => $description['compiledState'] ?? [],
            'topLevelAccess' => $description['topLevelAccess'] ?? [],
        ];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function debugTags(string $tag): array
    {
        $ids = $this->registrations->getTaggedIds(tag: $tag);

        return [
            'tag' => $tag,
            'ordered' => true,
            'ids' => $ids,
            'services' => array_map(
                callback: fn (string $id) => $this->describeService(id: $id),
                array   : $ids,
            ),
        ];
    }

    /**
     * @param array<string, mixed>  $context
     *
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     * @throws ReflectionException
     */
    public function debugTagsInContext(string $tag, array $context) : array
    {
        $report = $this->debugTags(tag: $tag);
        $slice = SliceContext::from(context: $context);

        if ($slice === '') {
            return $report;
        }

        $visibleIds = [];
        $hidden = [];

        foreach ($report['ids'] as $serviceId) {
            $access = $this->registrations->sliceAccessTo(viewerSlice: $slice, serviceId: $serviceId);
            if ($access['allowed']) {
                $visibleIds[] = $serviceId;

                continue;
            }

            $hidden[] = [
                'serviceId' => $serviceId,
                'reason' => $access['reason'],
            ];
        }

        return [
            'tag' => $tag,
            'ordered' => true,
            'sliceView' => $this->registrations->sliceView(slice: $slice),
            'ids' => $visibleIds,
            'services' => array_map(
                callback: fn (string $serviceId) : array => $this->describeServiceInContext(id: $serviceId, context: $context),
                array   : $visibleIds,
            ),
            'hidden' => $hidden,
        ];
    }

    /**
     * @param array<string, mixed>  $context
     *
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function describeServiceInContext(string $id, array $context) : array
    {
        $description               = $this->describeService(id: $id);
        $resolved                  = (string) ($description['resolvedId'] ?? $id);
        $slice = SliceContext::from(context: $context);

        if ($slice === '') {
            return $description;
        }

        $description['sliceView'] = $this->registrations->sliceView(slice: $slice);
        $description['viewAccess'] = $this->registrations->sliceAccessTo(
            viewerSlice: $slice,
            serviceId  : $resolved,
        );

        return $description;
    }

    public function exportGraph(?string $format = null, ?string $kind = null, string $id = '') : string
    {
        $format ??= 'json';
        $kind ??= 'dependency';

        return (new GraphExporter)->export(
            artifact: $this->graphArtifact(kind: $kind, id: $id),
            format  : $format,
        );
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function graphArtifact(string $kind, ?string $id = null, array $context = []): array
    {
        $id             ??= '';
        $normalizedKind = strtolower(string: trim(string: $kind));
        $slice = SliceContext::from(context: $context);
        if ($id !== '' && $this->registrations->sliceManifest(slice: $id) !== null) {
            $slice = $id;
            $id = '';
        }

        if (SliceContext::isRoot(slice: $id)) {
            $id = '';
        }

        return match ($normalizedKind) {
            'usage'                => $this->usageGraphArtifact(id: $id, slice: $slice),
            'owner'                => $this->ownerGraphArtifact(slice: $slice),
            'slice'                => $this->sliceGraphArtifact(slice: $slice),
            'override'             => $this->overrideGraphArtifact(id: $id, slice: $slice),
            'architecture' => $this->architectureGraphArtifact(id: $id, slice: $slice),
            'governance', 'policy' => $this->policyGraphArtifact(id: $id, slice: $slice),
            default => $this->dependencyGraphArtifact(id: $id, slice: $slice),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function usageGraphArtifact(string $id, string $slice): array
    {
        $graph = $this->buildDependencyGraph(serviceIds: $id !== '' ? [$id] : []);
        if ($slice !== '') {
            $graph = $this->filterGraphToVisibleSlice(graph: $graph, slice: $slice);
        }

        $dependents = $this->buildDependents(graph: $graph);
        $nodes = [];
        $edges = [];

        foreach ($dependents as $serviceId => $consumers) {
            $nodes[$serviceId] = ['id' => $serviceId, 'label' => $serviceId, 'type' => 'service'];

            foreach ($consumers as $consumer) {
                $nodes[$consumer] = ['id' => $consumer, 'label' => $consumer, 'type' => 'service'];
                $edges[] = ['from' => $consumer, 'to' => $serviceId, 'label' => 'uses'];
            }
        }

        return [
            'schemaVersion' => 1,
            'kind'          => 'usage',
            'scope'         => $slice !== '' ? $slice : ($id !== '' ? $id : 'global'),
            'nodes'         => array_values(array: $nodes),
            'edges' => $edges,
            'meta' => [
                'sliceView' => $slice !== '' ? $this->registrations->sliceView(slice: $slice) : null,
            ],
        ];
    }

    /**
     * @param  array<string, list<string>> $graph
     *
     * @return array<string, list<string>>
     */
    private function filterGraphToVisibleSlice(array $graph, string $slice): array
    {
        $visibleIds = array_fill_keys(keys: $this->visibleServiceIdsForSlice(slice: $slice), value: true);
        $filtered = array_intersect_key($graph, $visibleIds);

        foreach ($filtered as $serviceId => $dependencies) {
            $filtered[$serviceId] = $visibleIds
                    |> array_keys(...)
                    |> (static fn ($x) => array_intersect($dependencies, $x))
                    |> array_values(...);
        }

        ksort(array: $filtered);

        return $filtered;
    }

    /**
     * @return array<string, mixed>
     */
    private function ownerGraphArtifact(string $slice): array
    {
        $manifests = $slice !== ''
            ? array_filter(
                array   : $this->registrations->sliceManifests(),
                callback: static fn (string $manifestSlice): bool => $manifestSlice === $slice,
                mode    : ARRAY_FILTER_USE_KEY,
            )
            : $this->registrations->sliceManifests();
        $nodes     = [];
        $edges     = [];

        foreach ($manifests as $manifestSlice => $manifest) {
            $nodes['slice:' . $manifestSlice] = ['id' => 'slice:'.$manifestSlice, 'label' => $manifestSlice, 'type' => 'slice'];
            foreach ($manifest['services'] ?? [] as $serviceId) {
                $nodes[$serviceId] = ['id' => $serviceId, 'label' => $serviceId, 'type' => 'service'];
                $edges[] = ['from' => 'slice:'.$manifestSlice, 'to' => $serviceId, 'label' => 'owns'];
            }
        }

        return [
            'schemaVersion' => 1,
            'kind'          => 'owner',
            'scope'         => $slice !== '' ? $slice : 'global',
            'nodes' => array_values(array: $nodes),
            'edges' => $edges,
            'meta' => [
                'slices' => $manifests,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function sliceGraphArtifact(string $slice): array
    {
        $manifests = $slice !== ''
            ? array_filter(
                array   : $this->registrations->sliceManifests(),
                callback: static fn (string $manifestSlice): bool => $manifestSlice === $slice,
                mode    : ARRAY_FILTER_USE_KEY,
            )
            : $this->registrations->sliceManifests();
        $nodes     = [];
        $edges     = [];

        foreach ($manifests as $manifestSlice => $manifest) {
            $nodes['slice:' . $manifestSlice] = ['id' => 'slice:'.$manifestSlice, 'label' => $manifestSlice, 'type' => 'slice'];
            foreach ($manifest['imports'] ?? [] as $import) {
                $nodes['slice:' . $import] = ['id' => 'slice:' . $import, 'label' => $import, 'type' => 'slice'];
                $edges[] = ['from' => 'slice:'.$manifestSlice, 'to' => 'slice:'.$import, 'label' => 'imports'];
            }
        }

        return [
            'schemaVersion' => 1,
            'kind'          => 'slice',
            'scope'         => $slice !== '' ? $slice : 'global',
            'nodes' => array_values(array: $nodes),
            'edges' => $edges,
            'meta' => [
                'slices' => $manifests,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function overrideGraphArtifact(string $id, string $slice) : array
    {
        $history = $this->registrations->overrideHistory();
        $nodes = [];
        $edges = [];
        $filteredIds = $slice !== '' ? array_fill_keys(keys: $this->visibleServiceIdsForSlice(slice: $slice), value: true) : null;

        foreach ($history as $serviceId => $items) {
            if ($id !== '' && $serviceId !== $id) {
                continue;
            }
            if (is_array(value: $filteredIds) && ! isset($filteredIds[$serviceId])) {
                continue;
            }

            $nodes[$serviceId] = ['id' => $serviceId, 'label' => $serviceId, 'type' => 'service'];

            foreach ($items as $index => $item) {
                $overrideId         = $serviceId . '#override-' . $index;
                $label = (string) (($item['metadata']['overrideSource'] ?? '') ?: 'override');
                $nodes[$overrideId] = ['id' => $overrideId, 'label' => $label, 'type' => 'override'];
                $edges[] = ['from' => $overrideId, 'to' => $serviceId, 'label' => 'overrides'];
            }
        }

        return [
            'schemaVersion' => 1,
            'kind'          => 'override',
            'scope'         => $slice !== '' ? $slice : ($id !== '' ? $id : 'global'),
            'nodes' => array_values(array: $nodes),
            'edges' => $edges,
            'meta' => [
                'overrides' => $history,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    private function architectureGraphArtifact(string $id, string $slice): array
    {
        $graph = $this->buildDependencyGraph(serviceIds: $id !== '' ? [$id] : []);
        if ($slice !== '') {
            $graph = $this->filterGraphToVisibleSlice(graph: $graph, slice: $slice);
        }

        $dependents   = $this->buildDependents(graph: $graph);
        $governance = $this->governanceReport(graph: $graph, dependents: $dependents);
        $architecture = $this->architectureInsights(
            graph        : $graph,
            dependents   : $dependents,
            governance   : $governance,
            structureDiff: $this->structureDiff(graph: $graph),
        );

        $nodes = [];
        $edges = [];
        $buckets = [
            'singleConsumerShared',
            'fakeFoundations',
            'speculativeShared',
            'promotionCandidates',
            'namingSmells',
        ];

        foreach ($buckets as $bucket) {
            $nodes['bucket:'.$bucket] = [
                'id' => 'bucket:'.$bucket,
                'label' => $bucket,
                'type' => 'bucket',
            ];

            foreach ($architecture[$bucket] ?? [] as $item) {
                $serviceId = (string) ($item['serviceId'] ?? '');
                if ($serviceId === '') {
                    continue;
                }

                $nodes[$serviceId] = [
                    'id'    => $serviceId,
                    'label' => $serviceId,
                    'type'  => 'service',
                ];
                $edges[]           = [
                    'from' => 'bucket:'.$bucket,
                    'to' => $serviceId,
                    'label' => 'recommends',
                ];
            }
        }

        return [
            'schemaVersion' => 1,
            'kind'          => 'architecture',
            'scope'         => $slice !== '' ? $slice : ($id !== '' ? $id : 'global'),
            'nodes'         => array_values(array: $nodes),
            'edges' => $edges,
            'meta' => [
                'governance' => $governance,
                'architecture' => $architecture,
            ],
        ];
    }

    /**
     * @param array<string, list<string>> $graph
     * @param array<string, list<string>> $dependents
     * @param  array<string, mixed>  $governance
     * @param  array<string, mixed>  $structureDiff
     * @return array<string, mixed>
     */
    private function architectureInsights(
        array $graph,
        array $dependents,
        array $governance,
        array $structureDiff,
    ) : array
    {
        $singleConsumerShared = [];
        $fakeFoundations      = [];
        $speculativeShared    = [];
        $promotionCandidates = [];
        $namingSmells = [];
        $privateLeaks         = [];

        foreach ($this->registrations->all() as $serviceId => $registration) {
            $metadata = $registration->metadata;
            $consumers = $dependents[$serviceId] ?? [];
            $consumerSlices = array_map(
                    callback: fn (string $consumerId) : string => ($this->registrations->ownership(abstract: $consumerId)?->ownerSlice ?? 'default'),
                array   : $consumers,
            )
                    |> array_unique(...)
                    |> array_values(...);
            sort(array: $consumerSlices);

            if (
                $metadata->visibility === RegistrationVisibility::SHARED
                && count(value: $consumers) <= 1
            ) {
                $singleConsumerShared[] = [
                    'serviceId' => $serviceId,
                    'ownerSlice'    => $metadata->ownerSlice,
                    'consumerCount' => count(value: $consumers),
                    'consumers'     => $consumers,
                ];
                $speculativeShared[] = [
                    'serviceId'  => $serviceId,
                    'ownerSlice' => $metadata->ownerSlice,
                    'reason' => 'shared visibility has one or zero known consumers',
                ];
            }

            if (
                $metadata->category === RegistrationCategory::FOUNDATION
                && count(value: $graph[$serviceId] ?? []) >= 5
            ) {
                $fakeFoundations[] = [
                    'serviceId' => $serviceId,
                    'ownerSlice' => $metadata->ownerSlice,
                    'dependencyCount' => count(value: $graph[$serviceId] ?? []),
                ];
            }

            if (
                count(value: $consumerSlices) >= 2
                && ! in_array(needle: $metadata->visibility, haystack: [RegistrationVisibility::SHARED, RegistrationVisibility::PUBLIC], strict: true)
            ) {
                $promotionCandidates[] = [
                    'serviceId' => $serviceId,
                    'ownerSlice'     => $metadata->ownerSlice,
                    'consumerSlices' => $consumerSlices,
                    'reason' => 'multiple slices depend on this unit while it remains local-only',
                ];
            }

            foreach ($governance['findings'][$serviceId] ?? [] as $finding) {
                if (($finding['category'] ?? '') === 'naming') {
                    $namingSmells[] = [
                        'serviceId'  => $serviceId,
                        'ownerSlice' => $metadata->ownerSlice,
                        'code' => $finding['code'],
                        'message' => $finding['message'],
                    ];
                }
            }
        }

        foreach ($this->debugVisibilityViolations()['violations'] ?? [] as $violation) {
            $privateLeaks[] = [
                'consumerId'   => $violation['consumerId'],
                'dependencyId' => $violation['dependencyId'],
                'reason'       => $violation['reason'],
            ];
        }

        usort(array: $singleConsumerShared, callback: static fn (array $left, array $right) : int => $left['serviceId'] <=> $right['serviceId']);
        usort(array: $fakeFoundations, callback: static fn (array $left, array $right) : int => $left['serviceId'] <=> $right['serviceId']);
        usort(array: $speculativeShared, callback: static fn (array $left, array $right) : int => $left['serviceId'] <=> $right['serviceId']);
        usort(array: $promotionCandidates, callback: static fn (array $left, array $right) : int => $left['serviceId'] <=> $right['serviceId']);
        usort(array: $namingSmells, callback: static fn (array $left, array $right) : int => $left['serviceId'] <=> $right['serviceId']);
        usort(array: $privateLeaks, callback: static fn (array $left, array $right) : int => [$left['consumerId'], $left['dependencyId']] <=> [$right['consumerId'], $right['dependencyId']]);

        return [
            'singleConsumerShared' => $singleConsumerShared,
            'fakeFoundations'      => $fakeFoundations,
            'speculativeShared'    => $speculativeShared,
            'promotionCandidates'  => $promotionCandidates,
            'privateLeaks'         => $privateLeaks,
            'namingSmells' => $namingSmells,
            'structuralDrift'      => [
                'changedDependencies' => count(value: array_filter(
                                                          array   : $structureDiff['dependencies'] ?? [],
                                                          callback: static fn (array $change) : bool => (bool) ($change['changed'] ?? false),
                                                      )),
                'changedOwnership'    => count(value: array_filter(
                                                          array   : $structureDiff['ownership'] ?? [],
                                                          callback: static fn (array $change): bool => (bool) ($change['changed'] ?? false),
                                                      )),
            ],
        ];
    }

    /**
     * @param array<string, list<string>> $graph
     *
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

        foreach (array_values(array: array_unique(array: array_merge(array_keys(array: $graph), array_keys(array: $metadata?->dependencies ?? [])))) as $serviceId) {
            $current = $graph[$serviceId] ?? [];
            $compiled = $metadata?->dependencies[$serviceId] ?? [];
            sort(array: $current);
            sort(array: $compiled);

            $dependencyDiff[$serviceId] = [
                'current' => $current,
                'compiled' => $compiled,
                'changed' => $current !== $compiled,
            ];
        }

        foreach (array_values(array: array_unique(array: array_merge(array_keys(array: $currentOwnership), array_keys(array: $metadata?->ownership ?? [])))) as $serviceId) {
            $ownershipDiff[$serviceId] = [
                'current' => $currentOwnership[$serviceId] ?? [],
                'compiled' => $metadata?->ownership[$serviceId] ?? [],
                'changed' => ($currentOwnership[$serviceId] ?? []) !== ($metadata?->ownership[$serviceId] ?? []),
            ];
        }

        return [
            'compiledAvailable' => $report?->available ?? false,
            'dependencies'      => $dependencyDiff,
            'ownership' => $ownershipDiff,
            'slices' => [
                'current'  => $currentSlices,
                'compiled' => $metadata?->slices ?? [],
                'changed' => $currentSlices !== ($metadata?->slices ?? []),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    private function policyGraphArtifact(string $id, string $slice): array
    {
        $graph = $this->buildDependencyGraph(serviceIds: $id !== '' ? [$id] : []);
        if ($slice !== '') {
            $graph = $this->filterGraphToVisibleSlice(graph: $graph, slice: $slice);
        }

        $governance = $this->governanceReport(
            graph     : $graph,
            dependents: $this->buildDependents(graph: $graph),
        );
        $findings = $governance['findings'] ?? [];
        $nodes = [];
        $edges = [];

        foreach ($findings as $serviceId => $serviceFindings) {
            $nodes[$serviceId] = ['id' => $serviceId, 'label' => $serviceId, 'type' => 'service'];

            foreach ($serviceFindings as $index => $finding) {
                $findingId         = $serviceId . '#policy-' . $index;
                $nodes[$findingId] = [
                    'id' => $findingId,
                    'label' => $finding['code'].' '.$finding['severity'],
                    'type' => 'policy',
                ];
                $edges[] = ['from' => $serviceId, 'to' => $findingId, 'label' => $finding['category']];
            }
        }

        return [
            'schemaVersion' => 1,
            'kind'          => 'policy',
            'scope'         => $slice !== '' ? $slice : ($id !== '' ? $id : 'global'),
            'nodes' => array_values(array: $nodes),
            'edges'         => $edges,
            'meta' => [
                'governance' => $governance,
                'findings' => $findings,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dependencyGraphArtifact(string $id, string $slice): array
    {
        $graph = $this->buildDependencyGraph(serviceIds: $id !== '' ? [$id] : []);
        if ($slice !== '') {
            $graph = $this->filterGraphToVisibleSlice(graph: $graph, slice: $slice);
        }

        $nodes = [];
        $edges = [];
        foreach ($graph as $serviceId => $dependencies) {
            $nodes[$serviceId] = ['id' => $serviceId, 'label' => $serviceId, 'type' => 'service'];

            foreach ($dependencies as $dependency) {
                $nodes[$dependency] = ['id' => $dependency, 'label' => $dependency, 'type' => 'service'];
                $edges[] = ['from' => $serviceId, 'to' => $dependency, 'label' => 'depends-on'];
            }
        }

        return [
            'schemaVersion' => 1,
            'kind'          => 'dependency',
            'scope'         => $slice !== '' ? $slice : ($id !== '' ? $id : 'global'),
            'nodes' => array_values(array: $nodes),
            'edges' => $edges,
            'meta' => [
                'sliceView' => $slice !== '' ? $this->registrations->sliceView(slice: $slice) : null,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function exportGraphInContext(string $format, string $kind, string $id, array $context): string
    {
        return (new GraphExporter)->export(
            artifact: $this->graphArtifact(kind: $kind, id: $id, context: $context),
            format  : $format,
        );
    }

    public function diffGraph(?string $format = null, string $id = ''): string
    {
        $format ??= 'json';

        return (new GraphExporter)->export(
            artifact: $this->graphDiffArtifact(id: $id),
            format  : $format,
        );
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function graphDiffArtifact(?string $id = null, array $context = []): array
    {
        $id ??= '';
        $slice = SliceContext::from(context: $context);
        if ($id !== '' && $this->registrations->sliceManifest(slice: $id) !== null) {
            $slice = $id;
            $id = '';
        }

        $graph = $this->buildDependencyGraph(serviceIds: $id !== '' ? [$id] : []);
        if ($slice !== '') {
            $graph = $this->filterGraphToVisibleSlice(graph: $graph, slice: $slice);
        }

        $structureDiff = $this->structureDiff(graph: $graph);
        $nodes = [];
        $edges = [];
        $added = [];
        $removed = [];
        $ownershipMoves = [];

        foreach ($structureDiff['dependencies'] ?? [] as $serviceId => $change) {
            if (! ($change['changed'] ?? false)) {
                continue;
            }

            $current = $change['current'] ?? [];
            $compiled = $change['compiled'] ?? [];
            foreach (array_values(array: array_diff($current, $compiled)) as $dependency) {
                $added[] = ['from' => $serviceId, 'to' => $dependency];
                $edges[] = ['from' => $serviceId, 'to' => $dependency, 'label' => 'added'];
                $nodes[$serviceId] = ['id' => $serviceId, 'label' => $serviceId, 'type' => 'service'];
                $nodes[$dependency] = ['id' => $dependency, 'label' => $dependency, 'type' => 'service'];
            }

            foreach (array_values(array: array_diff($compiled, $current)) as $dependency) {
                $removed[] = ['from' => $serviceId, 'to' => $dependency];
                $edges[]   = ['from' => $serviceId, 'to' => $dependency, 'label' => 'removed'];
                $nodes[$serviceId] = ['id' => $serviceId, 'label' => $serviceId, 'type' => 'service'];
                $nodes[$dependency] = ['id' => $dependency, 'label' => $dependency, 'type' => 'service'];
            }
        }

        foreach ($structureDiff['ownership'] ?? [] as $serviceId => $change) {
            if (! ($change['changed'] ?? false)) {
                continue;
            }

            $currentOwner = (string) (($change['current']['ownerSlice'] ?? ''));
            $compiledOwner = (string) (($change['compiled']['ownerSlice'] ?? ''));
            if ($currentOwner === $compiledOwner) {
                continue;
            }

            $ownershipMoves[]                 = [
                'serviceId' => $serviceId,
                'from'      => $compiledOwner,
                'to'        => $currentOwner,
            ];
            $nodes[$serviceId]                = ['id' => $serviceId, 'label' => $serviceId, 'type' => 'service'];
            $nodes['slice:' . $compiledOwner] = ['id' => 'slice:' . $compiledOwner, 'label' => $compiledOwner, 'type' => 'slice'];
            $nodes['slice:' . $currentOwner]  = ['id' => 'slice:' . $currentOwner, 'label' => $currentOwner, 'type' => 'slice'];
            $edges[]                          = ['from' => 'slice:' . $compiledOwner, 'to' => $serviceId, 'label' => 'previous-owner'];
            $edges[] = ['from' => 'slice:'.$currentOwner, 'to' => $serviceId, 'label' => 'current-owner'];
        }

        $sliceChanges = $this->sliceChangeSet(diff: $structureDiff['slices'] ?? ['current' => [], 'compiled' => []], slice: $slice);
        foreach ($sliceChanges['addedImports'] as $change) {
            $nodes['slice:' . $change['slice']]  = ['id' => 'slice:' . $change['slice'], 'label' => $change['slice'], 'type' => 'slice'];
            $nodes['slice:' . $change['import']] = ['id' => 'slice:' . $change['import'], 'label' => $change['import'], 'type' => 'slice'];
            $edges[]                             = ['from' => 'slice:'.$change['slice'], 'to' => 'slice:'.$change['import'], 'label' => 'added-import'];
        }
        foreach ($sliceChanges['removedImports'] as $change) {
            $nodes['slice:' . $change['slice']]  = ['id' => 'slice:' . $change['slice'], 'label' => $change['slice'], 'type' => 'slice'];
            $nodes['slice:' . $change['import']] = ['id' => 'slice:' . $change['import'], 'label' => $change['import'], 'type' => 'slice'];
            $edges[]                             = ['from' => 'slice:'.$change['slice'], 'to' => 'slice:'.$change['import'], 'label' => 'removed-import'];
        }

        return [
            'schemaVersion' => 1,
            'kind'          => 'diff',
            'scope'         => $slice !== '' ? $slice : ($id !== '' ? $id : 'global'),
            'nodes'         => array_values(array: $nodes),
            'edges'         => $edges,
            'changes'       => [
                'addedEdges'          => $added,
                'removedEdges' => $removed,
                'ownershipMoves' => $ownershipMoves,
                'exportImportChanges' => $sliceChanges,
            ],
            'raw' => $structureDiff,
        ];
    }

    /**
     * @param array<string, mixed> $diff
     *
     * @return array<string, list<array<string, string>>>
     */
    private function sliceChangeSet(array $diff, string $slice = '') : array
    {
        $current  = $diff['current'] ?? [];
        $compiled = $diff['compiled'] ?? [];
        $slices   = array_values(array: array_unique(array: array_merge(array_keys(array: $current), array_keys(array: $compiled))));
        $addedImports = [];
        $removedImports = [];
        $addedExports = [];
        $removedExports = [];

        foreach ($slices as $sliceId) {
            if ($slice !== '' && $sliceId !== $slice) {
                continue;
            }

            foreach (array_values(array: array_diff($current[$sliceId]['imports'] ?? [], $compiled[$sliceId]['imports'] ?? [])) as $import) {
                $addedImports[] = ['slice' => $sliceId, 'import' => $import];
            }
            foreach (array_values(array: array_diff($compiled[$sliceId]['imports'] ?? [], $current[$sliceId]['imports'] ?? [])) as $import) {
                $removedImports[] = ['slice' => $sliceId, 'import' => $import];
            }
            foreach (array_values(array: array_diff($current[$sliceId]['exports'] ?? [], $compiled[$sliceId]['exports'] ?? [])) as $export) {
                $addedExports[] = ['slice' => $sliceId, 'serviceId' => $export];
            }
            foreach (array_values(array: array_diff($compiled[$sliceId]['exports'] ?? [], $current[$sliceId]['exports'] ?? [])) as $export) {
                $removedExports[] = ['slice' => $sliceId, 'serviceId' => $export];
            }
        }

        return [
            'addedImports'   => $addedImports,
            'removedImports' => $removedImports,
            'addedExports'   => $addedExports,
            'removedExports' => $removedExports,
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function diffGraphInContext(string $format, string $id, array $context): string
    {
        return (new GraphExporter)->export(
            artifact: $this->graphDiffArtifact(id: $id, context: $context),
            format  : $format,
        );
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function why(string $id): array
    {
        $description = $this->describeService(id: $id);

        return [
            'service' => $description['resolvedId'] ?? $id,
            'why'     => $description['explain'] ?? [],
        ];
    }

    /**
     * @param  array<string, mixed> $context
     *
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function whyInContext(string $id, array $context): array
    {
        $description = $this->describeServiceInContext(id: $id, context: $context);

        return [
            'service' => $description['resolvedId'] ?? $id,
            'sliceView' => $description['sliceView'] ?? null,
            'viewAccess' => $description['viewAccess'] ?? null,
            'why' => $description['explain'] ?? [],
        ];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function whoUses(string $id): array
    {
        $graph = $this->debugGraph(id: $id);

        return [
            'service'    => $graph['service'] ?? $id,
            'dependents' => $graph['dependents'] ?? [],
            'impact' => $graph['impact'] ?? [],
        ];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function debugGraph(string $id = ''): array
    {
        if (SliceContext::isRoot(slice: $id)) {
            return $this->debugSlice();
        }

        if ($id !== '' && $this->registrations->sliceManifest(slice: $id) !== null) {
            return $this->debugGraphInContext(
                id     : '',
                context: SliceContext::with(context: [], slice: $id),
            );
        }

        $graph      = $this->buildDependencyGraph(serviceIds: $id !== '' ? [$id] : []);
        $dependents = $this->buildDependents(graph: $graph);
        $dead       = $this->deadRegistrations(graph: $graph, dependents: $dependents);
        $duplicates = $this->registrations->duplicateConcepts();
        $governance = $this->governanceReport(graph: $graph, dependents: $dependents);
        $warnings = $this->policyWarnings(governance: $governance);
        $findings   = $governance['findings'];
        $structureDiff = $this->structureDiff(graph: $graph);
        $architecture = $this->architectureInsights(
            graph        : $graph,
            dependents   : $dependents,
            governance   : $governance,
            structureDiff: $structureDiff,
        );

        if ($id === '') {
            $conditions = [];
            foreach ($this->registrations->all() as $serviceId => $registration) {
                $conditions[$serviceId] = $this->conditionStateFor(metadata: $registration->metadata);
            }

            return [
                'graph'             => $graph,
                'dependents'        => $dependents,
                'slices'            => $this->registrations->sliceManifests(),
                'conditions' => $conditions,
                'overrides' => $this->registrations->overrideHistory(),
                'deadRegistrations' => $dead,
                'duplicateConcepts' => $duplicates,
                'structureDiff'     => $structureDiff,
                'governance'        => $governance,
                'architecture'      => $architecture,
                'policyFindings'    => $findings,
                'groups'            => $this->registrations->groupIndex(),
                'policyWarnings' => $warnings,
            ];
        }

        $resolved = $this->registrations->resolveAlias(abstract: $id);
        $description = $this->describeService(id: $resolved);

        return [
            'service'           => $resolved,
            'owner'             => $description['ownership'] ?? RegistrationMetadata::for(unitId: $resolved)->toArray(),
            'conditions'        => $description['conditions'] ?? [],
            'overrides'         => $description['overrides'] ?? [],
            'dependencies'      => $this->dependencyChainFor(serviceId: $resolved, seen: []),
            'dependents'        => $dependents[$resolved] ?? [],
            'impact'            => $this->impactFor(serviceId: $resolved, dependents: $dependents),
            'topLevelAccess' => $this->registrations->topLevelAccessTo(serviceId: $resolved),
            'structureDiff' => [
                'dependencies' => $structureDiff['dependencies'][$resolved] ?? ['current' => $graph[$resolved] ?? [], 'compiled' => [], 'changed' => false],
                'ownership' => $structureDiff['ownership'][$resolved] ?? ['current' => $description['ownership'] ?? [], 'compiled' => [], 'changed' => false],
            ],
            'duplicateConcepts' => array_values(array: array_filter(
                                                           array   : $duplicates,
                                                           callback: static fn (array $duplicate) : bool => in_array(needle: $resolved, haystack: array_column(array: $duplicate['services'], column_key: 'serviceId'), strict: true),
            )),
            'governance' => [
                'profile'  => $governance['profile'],
                'failMode' => $governance['failMode'],
                'blocked'  => $governance['blocked'],
                'summary' => $governance['summary'],
            ],
            'architecture' => $this->architectureForService(
                serviceId   : $resolved,
                architecture: $architecture,
                governance  : $governance,
            ),
            'policyFindings'    => $findings[$resolved] ?? [],
            'policyWarnings'    => $warnings[$resolved] ?? [],
            'dead'              => in_array(needle: $resolved, haystack: $dead, strict: true),
        ];
    }

    /**
     * @param  array<string, mixed> $context
     *
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function debugGraphInContext(string $id, array $context): array
    {
        $graph = $this->debugGraph(id: $id);
        $slice = SliceContext::from(context: $context);

        if ($slice === '') {
            return $graph;
        }

        $view                = $this->registrations->sliceView(slice: $slice);
        if ($id === '') {
            $visibleIds = array_column(array: $view['visible'], column_key: 'serviceId');
            $filteredGraph = array_intersect_key($graph['graph'] ?? [], array_fill_keys(keys: $visibleIds, value: true));
            $filteredDependents = array_intersect_key($graph['dependents'] ?? [], array_fill_keys(keys: $visibleIds, value: true));
            foreach ($filteredGraph as $serviceId => $dependencies) {
                $filteredGraph[$serviceId] = array_values(array: array_intersect($dependencies, $visibleIds));
            }
            foreach ($filteredDependents as $serviceId => $dependents) {
                $filteredDependents[$serviceId] = array_values(array: array_intersect($dependents, $visibleIds));
            }
            $filteredFindings = array_intersect_key($graph['policyFindings'] ?? [], array_fill_keys(keys: $visibleIds, value: true));
            $filteredGroups = [];
            foreach (($graph['groups'] ?? []) as $group => $items) {
                $filteredItems = array_values(array: array_filter(
                                                         array   : $items,
                                                         callback: static fn (array $item) : bool => in_array(needle: (string) ($item['serviceId'] ?? ''), haystack: $visibleIds, strict: true),
                ));
                if ($filteredItems !== []) {
                    $filteredGroups[$group] = $filteredItems;
                }
            }

            return [
                'sliceView' => $view,
                'graph' => $filteredGraph,
                'dependents' => $filteredDependents,
                'governance' => array_merge(
                    $graph['governance'] ?? [],
                    ['findings' => $filteredFindings],
                ),
                'architecture' => $this->debugArchitectureInContext(id: '', context: $context),
                'policyFindings' => $filteredFindings,
                'hiddenServices' => $view['hidden'],
                'structureDiff' => $graph['structureDiff'] ?? [],
                'groups' => $filteredGroups,
            ];
        }

        $resolved = $this->registrations->resolveAlias(abstract: $id);
        $graph['sliceView'] = $view;
        $graph['viewAccess'] = $this->registrations->sliceAccessTo(
            viewerSlice: $slice,
            serviceId  : $resolved,
        );

        return $graph;
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function debugArchitectureInContext(string $id, array $context) : array
    {
        $report = $this->debugArchitecture(id: $id);
        $slice = SliceContext::from(context: $context);

        if ($slice === '') {
            return $report;
        }

        if ($id !== '') {
            $resolved = $this->registrations->resolveAlias(abstract: $id);

            return array_merge(
            $report,
                [
                    'sliceView' => $this->registrations->sliceView(slice: $slice),
                    'viewAccess' => $this->registrations->sliceAccessTo(viewerSlice: $slice, serviceId: $resolved),
                ],
            );
        }

        $visible = array_fill_keys(keys: $this->visibleServiceIdsForSlice(slice: $slice), value: true);
        foreach (['singleConsumerShared', 'fakeFoundations', 'speculativeShared', 'promotionCandidates', 'namingSmells'] as $key) {
            $report[$key] = array_values(array: array_filter(
                                                    array   : $report[$key] ?? [],
                                                    callback: static fn (array $item): bool => isset($visible[(string) ($item['serviceId'] ?? '')]),
                                                ));
        }
        $report['privateLeaks'] = array_values(array: array_filter(
                                                          array   : $report['privateLeaks'] ?? [],
                                                          callback: static fn (array $item) : bool => isset($visible[(string) ($item['consumerId'] ?? '')]),
        ));
        $report['sliceView'] = $this->registrations->sliceView(slice: $slice);

        return $report;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function debugArchitecture(string $id = ''): array
    {
        $graph = $this->buildDependencyGraph(serviceIds: $id !== '' ? [$id] : []);
        $dependents = $this->buildDependents(graph: $graph);
        $governance = $this->governanceReport(graph: $graph, dependents: $dependents);
        $insights = $this->architectureInsights(
            graph        : $graph,
            dependents   : $dependents,
            governance   : $governance,
            structureDiff: $this->structureDiff(graph: $graph),
        );

        if ($id === '') {
            return $insights;
        }

        $resolved = $this->registrations->resolveAlias(abstract: $id);

        return [
            'service'              => $resolved,
            'singleConsumerShared' => array_values(array: array_filter(
                                                              array   : $insights['singleConsumerShared'] ?? [],
                                                              callback: static fn (array $item) : bool => $item['serviceId'] === $resolved,
                                                          )),
            'fakeFoundations'      => array_values(array: array_filter(
                                                              array   : $insights['fakeFoundations'] ?? [],
                                                              callback: static fn (array $item) : bool => $item['serviceId'] === $resolved,
                                                          )),
            'speculativeShared'    => array_values(array: array_filter(
                                                              array   : $insights['speculativeShared'] ?? [],
                                                              callback: static fn (array $item) : bool => $item['serviceId'] === $resolved,
                                                          )),
            'promotionCandidates'  => array_values(array: array_filter(
                                                              array   : $insights['promotionCandidates'] ?? [],
                                                              callback: static fn (array $item) : bool => $item['serviceId'] === $resolved,
                                                          )),
            'namingSmells'         => array_values(array: array_filter(
                                                              array   : $insights['namingSmells'] ?? [],
                                                              callback: static fn (array $item) : bool => $item['serviceId'] === $resolved,
                                                          )),
            'governance'           => $governance['findings'][$resolved] ?? [],
        ];
    }

    /**
     * @param  array<string, list<string>> $graph
     * @param array<string, list<string>>  $dependents
     *
     * @return list<string>
     */
    private function deadRegistrations(array $graph, array $dependents) : array
    {
        $dead = [];
        $aliasTargets = array_values(array: $this->registrations->allAliases());
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

            if (in_array(needle: $serviceId, haystack: $aliasTargets, strict: true)) {
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

        sort(array: $dead);

        return $dead;
    }

    /**
     * @param  array<string, list<string>> $graph
     * @param array<string, list<string>>  $dependents
     *
     * @return array<string, list<string>>
     */
    private function policyWarnings(array $governance): array
    {
        $warnings = [];
        foreach ($governance['findings'] ?? [] as $serviceId => $findings) {
            $warnings[$serviceId] = array_map(
                callback: static fn (array $finding): string => $finding['message'],
                array   : $findings,
            );
        }

        return $warnings;
    }

    /**
     * @param array<string, mixed> $architecture
     * @param array<string, mixed> $governance
     *
     * @return array<string, mixed>
     */
    private function architectureForService(string $serviceId, array $architecture, array $governance): array
    {
        $filter = static fn (array $item): bool => ($item['serviceId'] ?? null) === $serviceId;

        return [
            'singleConsumerShared' => array_values(array: array_filter(array: $architecture['singleConsumerShared'] ?? [], callback: $filter)),
            'fakeFoundations'      => array_values(array: array_filter(array: $architecture['fakeFoundations'] ?? [], callback: $filter)),
            'speculativeShared'    => array_values(array: array_filter(array: $architecture['speculativeShared'] ?? [], callback: $filter)),
            'promotionCandidates'  => array_values(array: array_filter(array: $architecture['promotionCandidates'] ?? [], callback: $filter)),
            'namingSmells'         => array_values(array: array_filter(array: $architecture['namingSmells'] ?? [], callback: $filter)),
            'governance' => $governance['findings'][$serviceId] ?? [],
        ];
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function whoUsesInContext(string $id, array $context) : array
    {
        $graph = $this->debugGraphInContext(id: $id, context: $context);

        return [
            'service' => $graph['service'] ?? $id,
            'sliceView' => $graph['sliceView'] ?? null,
            'viewAccess' => $graph['viewAccess'] ?? null,
            'dependents' => $graph['dependents'] ?? [],
            'impact' => $graph['impact'] ?? [],
        ];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function whatBreaksIf(string $id): array
    {
        $graph = $this->debugGraph(id: $id);

        return [
            'service' => $graph['service'] ?? $id,
            'impact'  => $graph['impact'] ?? [],
        ];
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function whatBreaksIfInContext(string $id, array $context) : array
    {
        $graph = $this->debugGraphInContext(id: $id, context: $context);

        return [
            'service' => $graph['service'] ?? $id,
            'sliceView' => $graph['sliceView'] ?? null,
            'viewAccess' => $graph['viewAccess'] ?? null,
            'impact' => $graph['impact'] ?? [],
        ];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function showOwner(string $id) : array
    {
        $description = $this->describeService(id: $id);

        return [
            'service' => $description['resolvedId'] ?? $id,
            'ownership' => $description['ownership'] ?? [],
            'topLevelAccess' => $description['topLevelAccess'] ?? [],
            'conditions' => $description['conditions'] ?? [],
        ];
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     *
     * @throws ReflectionException
     */
    public function showOwnerInContext(string $id, array $context) : array
    {
        $description = $this->describeServiceInContext(id: $id, context: $context);

        return [
            'service' => $description['resolvedId'] ?? $id,
            'ownership' => $description['ownership'] ?? [],
            'topLevelAccess' => $description['topLevelAccess'] ?? [],
            'conditions' => $description['conditions'] ?? [],
            'sliceView' => $description['sliceView'] ?? null,
            'viewAccess' => $description['viewAccess'] ?? null,
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
     * @param array<string, mixed> $context
     */
    public function hasInContext(string $id, array $context): bool
    {
        $serviceId = $this->registrations->resolveAlias(abstract: $id);
        $registration = $this->registrations->get(abstract: $serviceId);
        $slice = SliceContext::from(context: $context);

        if ($slice !== '') {
            if (! $registration instanceof DependencyRegistration || ! ($this->registrations->sliceAccessTo(viewerSlice: $slice, serviceId: $serviceId)['allowed'] ?? false)) {
                return false;
            }
        }

        if ($registration instanceof DependencyRegistration) {
            $conditions = $this->conditionStateFor(metadata: $registration->metadata);
            if (! $conditions['active']) {
                return false;
            }
        }

        if (
            $this->scopes->has(abstract: $serviceId)
            || $this->registrations->has(abstract: $serviceId)
            || $this->deferredProviders->isDeferred(serviceId: $serviceId)
        ) {
            return true;
        }

        if (! class_exists(class: $serviceId)) {
            return false;
        }

        try {
            return $this->blueprints->createFor(class: $serviceId)->instantiable;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param  array<string, mixed> $parameters
     * @param array<string, mixed>  $context
     *
     * @throws ReflectionException
     * @throws Throwable
     */
    public function callInContext(callable|string $callable, array $parameters, array $context): mixed
    {
        $this->telemetry->metrics()->increment(name: 'container_calls_total');

        return $this->caller->call(
            target    : $callable,
            parameters: $parameters,
            request   : new ResolveRequest(serviceId: $this->callableName(callable: $callable))->withContext(context: $context),
        );
    }

    /**
     * Calls one function, method, or invokable object through the resolver.
     *
     * @param array<string, mixed> $parameters
     *
     * @throws ReflectionException
     * @throws Throwable
     */
    public function call(callable|string $callable, array $parameters = []): mixed
    {
        $this->telemetry->metrics()->increment(name: 'container_calls_total');

        return $this->caller->call(target: $callable, parameters: $parameters);
    }

    /**
     * @throws ReflectionException
     */
    private function callableName(callable|string $callable): string
    {
        if (is_string(value: $callable)) {
            return 'call:'.$callable;
        }

        if (is_array(value: $callable)) {
            $target = $callable[0] ?? null;
            $method = (string) ($callable[1] ?? '__invoke');

            if (is_object(value: $target)) {
                return 'call:' . $target::class.'::'.$method;
            }

            if (is_string(value: $target)) {
                return 'call:'.$target.'::'.$method;
            }
        }

        if ($callable instanceof Closure) {
            $reflection = new ReflectionFunction(function: $callable);

            return 'call:closure:'.($reflection->getFileName() ?: 'internal')
                . ':' . $reflection->getStartLine()
                .':'.$reflection->getEndLine();
        }

        if (is_object(value: $callable)) {
            return 'call:'.$callable::class;
        }

        return 'call:unknown';
    }

    /**
     * Applies property and method injection to one existing object.
     *
     *
     * @throws ReflectionException
     * @throws Throwable
     */
    public function injectInto(object $target): object
    {
        return $this->injectTarget(
            target : $target,
            request: new ResolveRequest(serviceId: $target::class, manualInjection: true),
        );
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    private function injectTarget(object $target, ResolveRequest $request): object
    {
        $blueprint = $this->blueprints->createFor(class: $target::class);

        $this->injectProperties->inject(
            target   : $target,
            blueprint: $blueprint,
            overrides: [],
            resolver : $this,
            request  : $request,
        );
        $this->injectMethods->inject(
            target   : $target,
            blueprint: $blueprint,
            overrides: [],
            resolver : $this,
            request  : $request,
        );

        $this->telemetry->metrics()->increment(name: 'container_injections_total');

        return $target;
    }

    /**
     * @param array<string, mixed> $context
     *
     * @throws ReflectionException
     * @throws Throwable
     */
    public function injectIntoInContext(object $target, array $context): object
    {
        return $this->injectTarget(
            target : $target,
            request: new ResolveRequest(serviceId: $target::class, manualInjection: true)->withContext(context: $context),
        );
    }

    /**
     * Returns whether one object has injectable members.
     *
     * @throws ReflectionException
     */
    public function canInject(object $target): bool
    {
        $report = $this->inspectInjection(target: $target);

        return $report->injectedProperties !== [] || $report->injectedMethods !== [];
    }

    /**
     * Returns the injectable members discovered on one object.
     *
     * @throws ReflectionException
     */
    public function inspectInjection(object $target): InjectionReport
    {
        $blueprint = $this->blueprints->createFor(class: $target::class);

        return new InjectionReport(
            target            : $target,
            injectedProperties: array_map(
                                    callback: static fn (array $property) => $property['serviceId'],
                                    array   : $blueprint->injectableProperties,
                                ),
            injectedMethods   : array_map(
                                    callback: static fn (array $method) => array_map(
                                        callback: static fn (array $parameter) => $parameter['serviceId'],
                                        array   : $method['plan']->parameters,
                ),
                array   : $blueprint->injectableMethods,
            ),
            success           : $blueprint->injectableProperties !== [] || $blueprint->injectableMethods !== [],
        );
    }

    /**
     * Registers one prebuilt shared instance.
     */
    public function instance(string $abstract, object $instance): void
    {
        $this->scopes->instance(abstract: $abstract, instance: $instance);
        $this->registrations->instance(abstract: $abstract, instance: $instance);
    }

    /**
     * Opens one new scope layer.
     */
    public function openScope(?string $kind = null, string $scopeId = ''): void
    {
        $kind ??= ScopeKind::OPERATION;
        $this->scopes->openScope(kind: $kind, scopeId: $scopeId);
    }

    /**
     * Closes the current scope layer.
     */
    public function closeScope(?string $kind = null) : void
    {
        $this->scopes->closeScope(kind: $kind);
    }

    /**
     * @param  list<string>  $serviceIds
     *                                    Compiles and marks the runtime as warmed up.
     *
     * @throws ReflectionException
     * @throws Throwable
     */
    public function warmCompiled(array $serviceIds = []): void
    {
        $this->compileArtifacts(serviceIds: $serviceIds, warmed: true);
        $this->telemetry->metrics()->increment(name: 'container_compiled_warmups_total');
    }

    /**
     * @param list<string> $serviceIds
     *
     * @throws ReflectionException
     * @throws Throwable
     */
    private function compileArtifacts(array $serviceIds, bool $warmed): void
    {
        $this->bootDeferredProvidersFor(serviceIds: $serviceIds);

        $validationIssues = [];
        if ($this->compiledRuntime->shouldValidateBeforeCompile()) {
            $validationIssues = $this->validate(serviceIds: $serviceIds);
            if ($validationIssues !== []) {
                throw new ContainerException(
                    message: "Container compile failed:\n- ".implode(separator: "\n- ", array: $validationIssues),
                );
            }
        }

        $classes = $this->classesForWarmup(serviceIds: $serviceIds);
        $this->blueprints->warm(classes: $classes);

        $compiled = $this->compiledRuntime->compile(
            serviceIds      : $serviceIds,
            validationIssues: $validationIssues,
            warmed          : $warmed,
        );
        if ($compiled !== null) {
            $this->compiledRuntime->attach(
                compiled: $compiled,
                revision: $this->registrations->revision(),
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
     * @param list<string> $serviceIds
     * @return list<string>
     *
     * @throws ReflectionException
     */
    private function classesForWarmup(array $serviceIds): array
    {
        $queue = [];

        if ($serviceIds !== []) {
            foreach (array_values(array: array_unique(array: $serviceIds)) as $serviceId) {
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
            $serviceId = $this->registrations->resolveAlias(abstract: array_shift(array: $queue));
            if (isset($compiled[$serviceId])) {
                continue;
            }

            $this->bootDeferredProviderIfNeeded(serviceId: $serviceId);

            if (! $this->isCompilable(serviceId: $serviceId)) {
                continue;
            }

            $compiled[$serviceId] = true;
            $classes[]            = $serviceId;

            $registration = $this->registrations->get(abstract: $serviceId);
            $candidate = $registration?->concrete;

            if ($candidate === null && class_exists(class: $serviceId)) {
                $candidate = $serviceId;
            }

            if (! is_string(value: $candidate) || ! class_exists(class: $candidate)) {
                continue;
            }

            $blueprint = $this->blueprints->createFor(class: $candidate);
            foreach ($this->dependenciesForWarmup(blueprint: $blueprint) as $dependency) {
                $queue[] = $dependency;
            }
        }

        return array_filter(
            array   : $classes,
            callback: static fn (string $class) : bool => class_exists(class: $class),
            )
                |> array_unique(...)
                |> array_values(...);
    }

    /**
     * @param  list<string>  $serviceIds
     * @return list<string>
     */
    private function warmSharedServiceIds(array $serviceIds): array
    {
        $selected = [];
        $filter = $serviceIds !== [] ? array_fill_keys(keys: $serviceIds, value: true) : null;

        foreach ($this->registrations->all() as $abstract => $registration) {
            if ($filter !== null && ! isset($filter[$abstract])) {
                continue;
            }

            $lifetime = LifetimePlan::fromRegistration(
                serviceId   : $abstract,
                registration: $registration,
            );

            if (! $lifetime->isShared() || ! $lifetime->warm || $lifetime->lazy) {
                continue;
            }

            $selected[] = $abstract;
        }

        sort(array: $selected);

        return $selected;
    }

    /**
     * @param  list<string>  $serviceIds
     *                                    Rebuilds compiled artifacts from scratch.
     *
     * @throws ReflectionException
     * @throws Throwable
     */
    public function rebuildCompiled(array $serviceIds = []): void
    {
        $this->flushCompiled();
        $this->compileContainer(serviceIds: $serviceIds);
        $this->telemetry->metrics()->increment(name: 'container_compiled_rebuilds_total');
    }

    /**
     * Clears compiled blueprint and container artifacts.
     */
    public function flushCompiled(): void
    {
        $this->blueprints->flush();
        $this->compiledRuntime->flush();
        $this->telemetry->metrics()->increment(name: 'container_compiled_flushes_total');
    }

    /**
     * Clears derived caches, runtime state, and compiled artifacts.
     */
    public function flush(): void
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
    public function reset(): void
    {
        $this->compiledRuntime->reset();
        $this->registrations->resetDerivedState();
        $this->scopes->terminate();
        $this->caller->clearCache();
        $this->telemetry->reset();
        $this->lazyServices = [];
    }

    /**
     * @param  list<string>  $serviceIds
     *                                    Builds compiled runtime artifacts for the requested service set.
     *
     * @throws ReflectionException
     * @throws Throwable
     */
    public function compileContainer(array $serviceIds = []): void
    {
        $this->compileArtifacts(serviceIds: $serviceIds, warmed: false);
    }

    /**
     * Resolves every service registered under one tag.
     *
     * @return list<mixed>
     *
     * @throws ContainerException
     * @throws DependencyNotFoundException
     * @throws Throwable
     */
    public function tagged(string $tag): array
    {
        return array_map(
            callback: fn (string $serviceId) => $this->get(id: $serviceId),
            array   : $this->registrations->getTaggedIds(tag: $tag),
        );
    }

    /**
     * @param  array<string, mixed>  $context
     *
     * @return list<mixed>
     *
     * @throws ContainerException
     * @throws DependencyNotFoundException
     * @throws Throwable
     */
    public function taggedInContext(string $tag, array $context): array
    {
        $slice = SliceContext::from(context: $context);
        $serviceIds = $this->registrations->getTaggedIds(tag: $tag);

        if ($slice !== '') {
            $serviceIds = array_values(array: array_filter(
                                                  array   : $serviceIds,
                                                  callback: fn (string $serviceId) : bool => ($this->registrations->sliceAccessTo(
                                                      viewerSlice: $slice,
                    serviceId  : $serviceId,
                )['allowed'] ?? false) === true,
            ));
        }

        return array_map(
            callback: fn (string $serviceId) => $this->getInContext(id: $serviceId, context: $context),
            array   : $serviceIds,
        );
    }

    /**
     * @param  array<string, mixed>  $context
     *
     * @throws Throwable
     */
    public function getInContext(string $id, array $context): mixed
    {
        return $this->resolveRequest(
            request: new ResolveRequest(serviceId: $this->registrations->resolveAlias(abstract: $id))->withContext(context: $context),
        );
    }

    /**
     * Resolves every service registered in one ordered group.
     *
     * @return list<mixed>
     *
     * @throws ContainerException
     * @throws DependencyNotFoundException
     * @throws Throwable
     */
    public function grouped(string $group): array
    {
        return array_map(
            callback: fn (string $serviceId) => $this->get(id: $serviceId),
            array   : $this->registrations->getGroupedIds(group: $group),
        );
    }

    /**
     * @param  array<string, mixed>  $context
     *
     * @return list<mixed>
     *
     * @throws ContainerException
     * @throws DependencyNotFoundException
     * @throws Throwable
     */
    public function groupedInContext(string $group, array $context): array
    {
        $slice = SliceContext::from(context: $context);
        $serviceIds = $this->registrations->getGroupedIds(group: $group);

        if ($slice !== '') {
            $serviceIds = array_values(array: array_filter(
                                                  array   : $serviceIds,
                                                  callback: fn (string $serviceId) : bool => ($this->registrations->sliceAccessTo(
                    viewerSlice: $slice,
                    serviceId  : $serviceId,
                )['allowed'] ?? false) === true,
            ));
        }

        return array_map(
            callback: fn (string $serviceId) => $this->getInContext(id: $serviceId, context: $context),
            array   : $serviceIds,
        );
    }

    /**
     * Returns one lazy proxy for one service.
     */
    public function lazy(string $abstract): LazyProxy
    {
        $serviceId = $this->registrations->resolveAlias(abstract: $abstract);
        $this->lazyServices[$serviceId] = true;
        $this->telemetry->metrics()->increment(name: 'container_lazy_proxy_requests_total');

        return new LazyProxy(
            serviceId: $serviceId,
            factory  : fn () => $this->make(id: $serviceId),
        );
    }

    /**
     * Resolves one service and asserts that the result is an object.
     *
     * @param  array<string, mixed>  $parameters
     *
     * @throws Throwable
     */
    public function make(string $id, array $parameters = []) : object
    {
        $resolved = $this->resolveRequest(
            request: new ResolveRequest(
                serviceId: $this->registrations->resolveAlias(abstract: $id),
                overrides: $parameters,
            ),
        );

        if (! is_object(value: $resolved)) {
            throw new ContainerException(message: "Service [{$id}] did not resolve to an object.");
        }

        return $resolved;
    }

    /**
     * @param array<string, mixed> $context
     *                                         Returns one context-aware lazy proxy.
     */
    public function lazyInContext(string $abstract, array $context): LazyProxy
    {
        $serviceId = $this->registrations->resolveAlias(abstract: $abstract);
        $this->lazyServices[$serviceId] = true;
        $this->telemetry->metrics()->increment(name: 'container_lazy_proxy_requests_total');

        return new LazyProxy(
            serviceId: $serviceId,
            factory  : fn () => $this->makeInContext(id: $serviceId, parameters: [], context: $context),
        );
    }

    /**
     * @param array<string, mixed>  $parameters
     * @param array<string, mixed>  $context
     *
     * @throws Throwable
     */
    public function makeInContext(string $id, array $parameters, array $context) : object
    {
        $resolved = $this->resolveRequest(
            request: new ResolveRequest(
                     serviceId: $this->registrations->resolveAlias(abstract: $id),
                overrides: $parameters,
            )->withContext(context: $context),
        );

        if (! is_object(value: $resolved)) {
            throw new ContainerException(message: "Service [{$id}] did not resolve to an object.");
        }

        return $resolved;
    }

    /**
     * Resolves one dependency from an existing parent request.
     *
     *
     * @throws Throwable
     */
    public function resolveCompiledDependency(string $serviceId, ResolveRequest $request) : mixed
    {
        return $this->resolveRequest(request: $request->child(serviceId: $serviceId));
    }

    /**
     * @param array<string, mixed>  $context
     * @param array<string, mixed>  $parameters
     *
     * @throws Throwable
     */
    public function resolveInContext(string $id, array $context, array $parameters = []) : mixed
    {
        return $this->resolveRequest(
            request: new ResolveRequest(
                         serviceId: $this->registrations->resolveAlias(abstract: $id),
                         overrides: $parameters,
                     )->withContext(context: $context),
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     *                                           Finishes one compiled object by running injections and decorators.
     *
     * @throws ContainerException
     * @throws ReflectionException
     * @throws Throwable
     */
    public function finishCompiledService(
    string $serviceId,
        object $instance,
        string $class,
        ResolveRequest $request,
        array $overrides = [],
    ): object {
        $blueprint = $this->blueprints->createFor(class: $class);

        if ($blueprint->injectableProperties !== []) {
            $this->injectProperties->inject(
                target   : $instance,
                blueprint: $blueprint,
                overrides: $overrides,
                resolver : $this,
                request  : $request,
            );
        }

        if ($blueprint->injectableMethods !== []) {
            $this->injectMethods->inject(
                target   : $instance,
                blueprint: $blueprint,
                overrides: $overrides,
                resolver : $this,
                request  : $request,
            );
        }

        $resolved = $this->applyExtenders(abstract: $serviceId, instance: $instance);

        if (! is_object(value: $resolved)) {
            throw new ContainerException(message: "Compiled service [{$serviceId}] did not resolve to an object.");
        }

        return $resolved;
    }

    /**
     * Registers one deferred transient service.
     */
    public function defer(string $abstract, mixed $concrete = null) : DependencyRegistration
    {
        return $this->registrations->defer(abstract: $abstract, concrete: $concrete);
    }

    /**
     * @param  list<string>  $serviceIds
     *                                    Registers one deferred provider and its owned service ids.
     *
     * @throws ContainerException
     */
    public function registerDeferredProvider(RegisterDependency $provider, array $serviceIds): void
    {
        $this->deferredProviders->register(
            provider     : $provider,
            serviceIds   : $serviceIds,
            registrations: $this->registrations,
            metrics      : $this->telemetry->metrics(),
        );
    }

    /**
     * @param  array<string, list<string>>  $graph
     * @param array<string, list<string>>  $dependents
     *
     * @return array<string, list<array{code: string, severity: string, category: string, message: string}>>
     *
     * @throws ReflectionException
     */
    private function policyFindings(array $graph, array $dependents): array
    {
        return $this->governanceReport(graph: $graph, dependents: $dependents)['findings'] ?? [];
    }
}
