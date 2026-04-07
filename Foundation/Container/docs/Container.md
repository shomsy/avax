# Container

`Container` is the stable public facade of this component.

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
- `validate()`
- `describeService()`
- `debugPlan()`
- `debugTags()`
- `debugAliases()`
- `debugScope()`
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
- `flush()` clears user registrations, scopes, runtime pools, and compiled artifacts
- `reset()` is the same clean-runtime boundary as `flush()`
- `validate()` reports obvious registration and blueprint issues without resolving values
- `describeService()` and the `debug*()` helpers explain how a service, tag set, alias map, or scope state looks right now
- `env()` reads env-backed configuration hooks from `ContainerSettings`
- `compileContainer()` writes a generated compiled runtime artifact plus compiled blueprint metadata
- `warmCompiled()` preserves the same public intent and records warmup metrics on top of the compile path
- `flushCompiled()` removes compiled artifacts for the current cache version
- `rebuildCompiled()` flushes then warms compiled artifacts again
- deferred services stay out of the default warm compile path unless they are explicit or needed by a compiled dependency
- scoped services require an active scope
- missing services throw `ServiceNotFoundException`

## Public Companion Types

The facade uses a small set of explicit companion types:

- `DependencyInjection/Dependencies/Bindings/ServiceRegistration.php`
- `DependencyInjection/Dependencies/Bindings/RegisterForTarget.php`
- `DependencyInjection/Injection/Reports/InjectionReport.php`
- `DependencyInjection/Scopes/ScopeInterface.php`
