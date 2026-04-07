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

- `openScope()`
- `closeScope()`
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
- scoped services require an active scope
- missing services throw `ServiceNotFoundException`

## Public Companion Types

The facade uses a small set of explicit companion types:

- `DependencyInjection/Dependencies/Bindings/ServiceRegistration.php`
- `DependencyInjection/Dependencies/Bindings/RegisterForTarget.php`
- `DependencyInjection/Injection/Reports/InjectionReport.php`
- `DependencyInjection/Scopes/ScopeInterface.php`
