# Container

`Container` is the stable public facade of this component.

## What It Exposes

Registration:

- `bind()`
- `singleton()`
- `scoped()`
- `when()`
- `extend()`
- `tag()`
- `instance()`

Resolution and execution:

- `get()`
- `has()`
- `make()`
- `call()`
- `injectInto()`

Scopes and diagnostics:

- `beginScope()`
- `endScope()`
- `scopes()`
- `canInject()`
- `inspectInjection()`
- `exportMetrics()`

## What It Delegates To

- `RegisterServices`
- `ResolveService`
- `CallFunction`
- `OpenScope`
- `CloseScope`

The facade does not own resolution internals, storage, blueprint creation, or telemetry wiring.

## Public Behavior Rules

- `get()` resolves or returns a cached service
- `make()` resolves an object with explicit constructor overrides
- `call()` supports closures, callable arrays, `Class@method`, `Class::method`, and invokable class strings
- scoped services require an active scope
- missing services throw a not-found exception

## Public Companion Types

The facade uses a small set of explicit companion types under `DependencyInjection/`:

- `Registrations/ServiceRegistration.php`
- `Registrations/RegisterForTarget.php`
- `Injection/InjectionReport.php`
- `Scopes/ScopeInterface.php`
