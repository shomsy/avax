# Container

`Container` is the stable public facade of this component.

See [`public-contract-matrix.md`](./public-contract-matrix.md) for the frozen public surface ledger.
See [`compile-artifact-model.md`](./compile-artifact-model.md) and [`runtime-state-model.md`](./runtime-state-model.md) for the operator view of generated and disposable state.

## What It Exposes

Registration:

- `alias()`
- `bind()`
- `defer()`
- `singleton()`
- `scoped()`
- `when()`
- `extend()`
- `decorate()`
- `tag()`
- `instance()`

Resolution and execution:

- `get()`
- `has()`
- `make()`
- `call()`
- `forContext()`
- `tagged()`
- `grouped()`
- `lazy()`
- `injectInto()`

Lifecycle, scopes, and diagnostics:

- `flush()`
- `reset()`
- `bootProviders()`
- `compileContainer()`
- `openScope()`
- `closeScope()`
- `warmCompiled()`
- `flushCompiled()`
- `rebuildCompiled()`
- `compileReport()`
- `runtimeReport()`
- `validate()`
- `describeService()`
- `debugService()`
- `debugPlan()`
- `debugGraph()`
- `debugTags()`
- `debugAliases()`
- `debugScope()`
- `hasAlias()`
- `isDeferred()`
- `isLazy()`
- `isCompiled()`
- `isWarmedUp()`
- `env()`
- `scopes()`
- `canInject()`
- `inspectInjection()`
- `exportMetrics()`

## What It Delegates To

- `src/Flows/RegisterServices/RegisterServices.php`
- `src/Flows/ResolveService/ResolveService.php`
- `src/Flows/CallFunction/CallFunction.php`
- `src/Flows/OpenScope/OpenScope.php`
- `src/Flows/CloseScope/CloseScope.php`
- `src/Flows/BootProviders/BootProviders.php`

The facade does not own resolution internals, storage, blueprint creation, or telemetry wiring.

## Public Behavior Rules

- `get()` resolves or returns a cached service
- `make()` resolves an object with explicit constructor overrides
- `call()` supports closures, callable arrays, `Class@method`, `Class::method`, and invokable class strings
- `forContext()` returns a context view that feeds matching scalar values into `make()`, `call()`, and `injectInto()`
- `alias()` maps an alternate id onto one canonical service id
- every `ServiceRegistration` can also declare ownership metadata such as owner slice, category, visibility, imports, exports, reason, override source, provenance, and concept name
- `defer()` marks a service for lazy compilation and on-demand resolution
- `tagged()` resolves all services carrying one tag
- `lazy()` returns a lazy proxy that resolves the target service on first use
- `decorate()` appends a post-build decoration step to one service id
- `bootProviders()` resolves provider dependencies, registers them, then boots them in deterministic order
- providers that implement `DeferredProviderInterface` are registered lazily and boot on first matching service resolve
- deferred providers should implement `DeferredProviderInterface` so lazy provider ownership stays explicit and reviewable
- `flush()` clears derived caches, scopes, runtime pools, lazy markers, telemetry state, and compiled artifacts without mutating canonical registrations
- `reset()` clears disposable runtime state and derived caches but keeps canonical registrations and compiled artifacts
- `validate()` reports obvious registration and blueprint issues without resolving values
- `describeService()` and the `debug*()` helpers explain alias expansion, decoration order, dependency chain, cache state, compiled-path decisions, fallback reasons, current scope state, ownership metadata, conditions, and override history
- `debugGraph()` emits dependency, dependent, dead-registration, duplicate-concept, slice, grouped-binding, structure-diff, and policy artifacts for humans and tools
- `compileReport()` returns the machine-readable compiled artifact status for the current cache version or requested service ids, including compatibility state and compatibility issues
- `runtimeReport()` returns the current runtime state, including its schema version, revision numbers, diagnostics mode, timeline-enabled state, shared/scoped counts, deferred provider ownership, lazy services, scope snapshot, hot-path summary, metrics, timeline, and the attached compile report
- `hasAlias()`, `isDeferred()`, `isLazy()`, `isCompiled()`, and `isWarmedUp()` expose the container's current runtime and compiled-artifact status without making the caller inspect internals
- `env()` reads env-backed configuration hooks from `ContainerSettings`
- `compileContainer()` writes a generated compiled runtime artifact plus compiled blueprint metadata
- `warmCompiled()` preserves the same public intent and records warmup metrics on top of the compile path
- `flushCompiled()` removes compiled artifacts for the current cache version
- `rebuildCompiled()` flushes then warms compiled artifacts again
- compiled container metadata includes checksum, config hash, environment, service signatures, and changed service ids
- compiled container metadata also records schema version, settings fingerprint, diagnostics mode, warmed state, artifact paths, dependency-graph revision, reused services, invalidated services, dependency graphs, derived ownership maps, derived slice manifests, and invalidation reasons for incremental compile reuse
- incompatible compiled artifacts are not reported as available and fall back to the dynamic runtime path
- corrupted compiled artifacts are quarantined; production-style modes fail closed, while development mode can fall back to dynamic resolution
- diagnostics mode is explicit: `minimal` keeps timeline overhead near zero, while `detailed` and `ci` keep richer traces for debugging and CI analysis
- deferred services stay out of the default warm compile path unless they are explicit or needed by a compiled dependency
- scoped services require an active scope
- `operation`, `request`, `job`, and `tenant` lifetimes each require a matching active scope kind
- `warm()` and `lazy()` make shared lifecycle posture explicit: `warmCompiled()` warms eager shared services and skips lazy ones
- `dispose()` makes disposal ownership explicit and validation rejects disposable transients or disposable services that do not expose a disposal contract
- missing services throw `ServiceNotFoundException`

## Public Companion Types

The facade uses a small set of explicit companion types:

- `src/Capabilities/Declaration/Bindings/ServiceRegistration.php`
- `src/Capabilities/Declaration/Bindings/RegisterForTarget.php`
- `src/Capabilities/Execution/Injection/Reports/InjectionReport.php`
- `src/Capabilities/Runtime/Scopes/ScopeInterface.php`
- `src/Capabilities/Composition/Compilation/CompileReport.php`
- `src/Capabilities/Diagnostics/Observability/RuntimeReport.php`
