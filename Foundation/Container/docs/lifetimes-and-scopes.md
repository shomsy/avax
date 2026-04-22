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
- `pooled`: keep reusable instances in a bounded bucket with checkout/return lifecycle

Registrations can also carry lifecycle modifiers:

- `warm()`: eagerly build a shared service during `warmCompiled()`
- `lazy()`: keep a shared service out of warmup so it builds only on first real resolve
- `dispose()`: tell the runtime that the container owns the disposal boundary for this service
- `pooled(maxSize, scopeKind, resetBeforeReuse)`: acquire from a bounded bucket on resolve, return on scope close

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
- pooled services that are marked warm or lazy
- pooled services that are prebuilt instances instead of container-owned builds
- pooled services with reset-before-reuse enabled whose class does not implement `ResettableInterface`
- pooled services capturing scoped, transient, or other pooled dependencies

## Disposal Rules

Disposable services are disposed when the runtime actually owns the instance:

- shared disposables are disposed on `reset()` and `flush()`
- scoped disposables are disposed on `closeScope()` and `terminate()`
- pooled disposables are disposed on overflow, unsafe return, and `terminate()`
- pooled services are reset via `ResettableInterface::reset()` on return to the idle bucket
- transient services are never container-owned disposables

The runtime supports both:

- `DisposableInterface`
- plain `dispose()` methods on the resolved class
- `ResettableInterface::reset()` for pooled services returned to the idle bucket

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
- pooled bucket state (checked-out, available count, stats)

## Pooled Lifetime Model

Pooled lifetime uses a bounded bucket inside `ServicePool`:

1. On first resolve, the container builds a fresh instance and stores it in the active scope
2. On scope close, the instance is returned to the idle bucket via `releasePooled()`
3. If reset-before-reuse is enabled, `reset()` is called before the instance enters the idle bucket
4. If the pool is full, the instance is disposed instead of returned
5. On next resolve, the container checks out an idle instance before building a new one

Pooled services must implement `ResettableInterface` when reset-before-reuse is enabled.

Registration example:

```php
$container->bind(HttpClient::class, CurlHttpClient::class)
    ->pooled(maxSize: 4, scopeKind: 'request', resetBeforeReuse: true);
```

See also: [`pooled-lifetime-contracts.md`](./pooled-lifetime-contracts.md)
