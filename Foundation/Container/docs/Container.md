# Container

`Container` is the stable public facade of this component.

See [`public-contract-matrix.md`](./public-contract-matrix.md) for the frozen public surface ledger.

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

- `DependencyInjection/Flows/RegisterServices.php`
- `DependencyInjection/Flows/ResolveService.php`
- `DependencyInjection/Flows/CallFunction.php`
- `DependencyInjection/Flows/OpenScope.php`
- `DependencyInjection/Flows/CloseScope.php`
- `DependencyInjection/Flows/BootProviders.php`

The facade does not own resolution internals, storage, blueprint creation, or telemetry wiring.

## Public Behavior Rules

- `get()` resolves or returns a cached service
- `make()` resolves an object with explicit constructor overrides
- `call()` supports closures, callable arrays, `Class@method`, `Class::method`, and invokable class strings
- `forContext()` returns a context view that feeds matching scalar values into `make()`, `call()`, and `injectInto()`
- `alias()` maps an alternate id onto one canonical service id
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
- `describeService()` and the `debug*()` helpers explain how a service, tag set, alias map, scope state, and compiled artifact state look right now
- `compileReport()` returns the machine-readable compiled artifact status for the current cache version or requested service ids, including compatibility state and compatibility issues
- `runtimeReport()` returns the current runtime state, including revision numbers, diagnostics mode, deferred provider ownership, lazy services, scope snapshot, metrics, timeline, and the attached compile report
- `hasAlias()`, `isDeferred()`, `isLazy()`, `isCompiled()`, and `isWarmedUp()` expose the container's current runtime and compiled-artifact status without making the caller inspect internals
- `env()` reads env-backed configuration hooks from `ContainerSettings`
- `compileContainer()` writes a generated compiled runtime artifact plus compiled blueprint metadata
- `warmCompiled()` preserves the same public intent and records warmup metrics on top of the compile path
- `flushCompiled()` removes compiled artifacts for the current cache version
- `rebuildCompiled()` flushes then warms compiled artifacts again
- compiled container metadata includes checksum, config hash, environment, service signatures, and changed service ids
- compiled container metadata also records reused services, invalidated services, dependency graphs, and invalidation reasons for incremental compile reuse
- incompatible compiled artifacts are not reported as available and fall back to the dynamic runtime path
- corrupted compiled artifacts are quarantined; production-style modes fail closed, while development mode can fall back to dynamic resolution
- diagnostics mode is explicit: `minimal` keeps timeline overhead near zero, while `detailed` keeps richer traces for CI and debugging
- deferred services stay out of the default warm compile path unless they are explicit or needed by a compiled dependency
- scoped services require an active scope
- missing services throw `ServiceNotFoundException`

## Public Companion Types

The facade uses a small set of explicit companion types:

- `DependencyInjection/Dependencies/Bindings/ServiceRegistration.php`
- `DependencyInjection/Dependencies/Bindings/RegisterForTarget.php`
- `DependencyInjection/Injection/Reports/InjectionReport.php`
- `DependencyInjection/Scopes/ScopeInterface.php`
- `Compilation/CompileReport.php`
- `Observability/RuntimeReport.php`
