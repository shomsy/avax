# Lifetimes And Scopes

This container now treats lifetime and scope as first-class runtime truth.

## Supported Lifetimes

- `transient`: build a fresh object every time
- `shared`: keep one runtime instance in `ServicePool`
- `scoped`: keep one runtime instance inside the current matching scope frame
- `operation`: scoped storage that requires an `operation` scope
- `request`: scoped storage that requires a `request` scope
- `job`: scoped storage that requires a `job` scope
- `tenant`: scoped storage that requires a `tenant` scope

Registrations can also carry lifecycle modifiers:

- `warm()`: eagerly build a shared service during `warmCompiled()`
- `lazy()`: keep a shared service out of warmup so it builds only on first real resolve
- `dispose()`: tell the runtime that the container owns the disposal boundary for this service

## Scope Rules

- `openScope()` defaults to an `operation` frame
- `openScope('request', 'request-123')` opens a named request frame
- `openScope('job', 'job-123')` opens a named job frame
- `closeScope()` closes the current frame
- `closeScope('request')` refuses to close the wrong frame kind
- `reset()` terminates all runtime frames and shared state

The runtime never falls back from scoped storage to shared storage.

## Validation Rules

`validate()` now reports lifetime misuse before normal runtime work when possible:

- shared services capturing scoped dependencies
- wider scopes capturing narrower scopes
- non-transient services capturing transients
- disposable transients, because the container cannot own their disposal boundary
- disposable services whose class does not expose `dispose()` and does not implement `DisposableInterface`

## Disposal Rules

Disposable services are disposed when the runtime actually owns the instance:

- shared disposables are disposed on `reset()` and `flush()`
- scoped disposables are disposed on `closeScope()` and `terminate()`
- transient services are never container-owned disposables

The runtime supports both:

- `DisposableInterface`
- plain `dispose()` methods on the resolved class

## Warm Versus Lazy Shared Services

`warmCompiled()` eagerly builds only shared services that are both:

- compiled or compilable
- marked `warm()`

It intentionally skips shared services marked `lazy()`.

That makes the lifecycle explicit:

- warm shared services are prebuilt for steady-state runtime
- lazy shared services remain cold until first use

## Diagnostics

Use:

- `describeService()['lifetimePlan']`
- `describeService()['cacheState']`
- `debugScope()`
- `runtimeReport()`

These surfaces explain:

- which storage the service uses
- which scope kind it requires
- whether it is warm or lazy
- whether it is disposable
- which scope frames are active
