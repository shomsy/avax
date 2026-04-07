# Container

`Container` is the stable public facade of the component.

Its contract stays in the root `Avax\Container\...` namespace even though the
runtime implementation lives under `DependencyInjection/`.

## What It Owns

It exposes the public API for:

- binding services
- resolving services
- invoking callables
- injecting into existing objects
- beginning and ending scopes
- exporting metrics

It does not own low-level runtime logic. It delegates to:

- `DependencyInjection/Flow/RegisterBindings`
- `DependencyInjection/Flow/ResolveService`
- `DependencyInjection/Flow/InvokeCallable`
- `DependencyInjection/Flow/BeginScope`
- `DependencyInjection/Flow/EndScope`

## Public API Shape

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

## Ownership Rule

If a feature can stay behind the facade, keep it there.

If a feature needs shared runtime mechanics, move that logic into the correct capability slice and keep `Container`
thin.

`resolveContext()` exists on the runtime-facing contract used by nested resolution chains. It is not part of the
public `ContainerInterface`.
