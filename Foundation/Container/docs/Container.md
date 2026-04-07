# Container

`Container` is the stable public facade of the component.

## What It Owns

It exposes the public API for:

- binding services
- resolving services
- invoking callables
- injecting into existing objects
- beginning and ending scopes
- exporting metrics

It does not own low-level runtime logic. It delegates to:

- `Flows/RegisterBindings`
- `Flows/ResolveService`
- `Flows/InvokeCallable`
- `Flows/BeginScope`
- `Flows/EndScope`

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
- `resolveContext()`
- `resolve()`
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
