# Container

`Container` is the stable public facade of this component.

## What It Exposes

Registration:

- `alias()`
- `bind()`
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
- `tagged()`
- `lazy()`
- `injectInto()`

Scopes and diagnostics:

- `compileContainer()`
- `openScope()`
- `closeScope()`
- `warmCompiled()`
- `flushCompiled()`
- `rebuildCompiled()`
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

The facade does not own resolution internals, storage, blueprint creation, or telemetry wiring.

## Public Behavior Rules

- `get()` resolves or returns a cached service
- `make()` resolves an object with explicit constructor overrides
- `call()` supports closures, callable arrays, `Class@method`, `Class::method`, and invokable class strings
- `alias()` maps an alternate id onto one canonical service id
- `tagged()` resolves all services carrying one tag
- `lazy()` returns a lazy proxy that resolves the target service on first use
- `decorate()` appends a post-build decoration step to one service id
- `compileContainer()` writes a generated compiled runtime artifact plus compiled blueprint metadata
- `warmCompiled()` preserves the same public intent and records warmup metrics on top of the compile path
- `flushCompiled()` removes compiled artifacts for the current cache version
- `rebuildCompiled()` flushes then warms compiled artifacts again
- scoped services require an active scope
- missing services throw `ServiceNotFoundException`

## Public Companion Types

The facade uses a small set of explicit companion types:

- `DependencyInjection/Dependencies/Bindings/ServiceRegistration.php`
- `DependencyInjection/Dependencies/Bindings/RegisterForTarget.php`
- `DependencyInjection/Injection/Reports/InjectionReport.php`
- `DependencyInjection/Scopes/ScopeInterface.php`
