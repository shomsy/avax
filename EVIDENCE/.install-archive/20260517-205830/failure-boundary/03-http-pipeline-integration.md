# FailureBoundary — HTTP Pipeline Integration & AppKernel class_exists Guard

**Date:** 2026-05-12
**Stage:** Phase 3 — Production-Grade Closure

## Purpose

Audit the HTTP pipeline integration of FailureBoundary, prove middleware ordering is correct,
and resolve the AppKernel `class_exists` guard decision.

## Current Integration State

### AppKernel Middleware Stack

File: `components/HTTP/System/Capabilities/Kernel/AppKernel.php`

```php
// FailureBoundary: outermost error-handling middleware (framework-level, optional)
if (class_exists(\Avax\Framework\System\Capabilities\FailureBoundary\Configuration\BuildFailureBoundary::class)
    && class_exists(\Avax\Framework\System\Capabilities\FailureBoundary\Integration\HttpFailureBoundaryMiddleware::class)) {
    $fbBuilder = new \Avax\Framework\System\Capabilities\FailureBoundary\Configuration\BuildFailureBoundary();
    $middleware[] = new \Avax\Framework\System\Capabilities\FailureBoundary\Integration\HttpFailureBoundaryMiddleware(
        $fbBuilder->build(),
    );
}
```

### Middleware Order (as added to array, executed in reverse via `array_pop`)

The middleware stack is built by appending to `$middleware[]`, then executed by `array_pop()` (LIFO):

```
Request → [ip-restrict] → [session] → [log] → [cors] → [rate-limit] → [json] → [FailureBoundary] → Router
```

Wait — since `array_pop` executes in reverse order, the actual execution order is:

```
Request → FailureBoundary → json → rate-limit → cors → log → session → ip-restrict → Router
```

**This is correct.** FailureBoundary is the outermost middleware, wrapping all downstream processing.

### What FailureBoundary Wraps

`HttpFailureBoundaryMiddleware` wraps `$next($request)`, which includes:

- All subsequent middleware (json, rate-limit, cors, log, session, ip-restrict)
- Router dispatch
- Controller execution
- Response generation

### What FailureBoundary Does NOT Wrap

`HandleIncomingHttp.handleInCurrentScope()` line 73:

```php
} catch (Throwable) {
    // Returns generic 500, discards exception context
}
```

This is ABOVE the middleware stack in the call hierarchy:

```
HandleIncomingHttp → httpHandler(Closure) → AppKernel.handle() → middleware stack → Router
```

The `catch (Throwable)` at line 73 catches failures from the entire `httpHandler` closure,
which includes the AppKernel middleware pipeline. So:

1. `HandleIncomingHttp` calls `$httpHandler($runtimeRequest, $runtime)`
2. `$httpHandler` is a closure that calls `AppKernel.handle()`
3. `AppKernel` builds the middleware stack with FailureBoundary
4. If an exception is thrown in the router/controller, FailureBoundary catches it first
5. If FailureBoundary maps it to a response, no exception propagates
6. If FailureBoundary rethrows (unmapped exception), it propagates to HandleIncomingHttp's catch
7. HandleIncomingHttp returns generic 500

**This is the correct behavior.** HandleIncomingHttp's catch is a lifecycle safety net for unmapped failures.

## AppKernel class_exists Guard — Decision

### Current State: class_exists guard in place

The guard uses `class_exists()` checks before instantiating FailureBoundary classes.

### Options Considered

| Option                                | Pros                                                 | Cons                                           | Decision |
|---------------------------------------|------------------------------------------------------|------------------------------------------------|----------|
| Keep class_exists guard               | No hard coupling, works if FailureBoundary is absent | Slight runtime check, hides missing dependency | **KEEP** |
| Direct import + constructor injection | Cleaner code, fails fast if missing                  | Hard-couples components/ to framework/         | Rejected |
| Feature flag                          | Configurable, explicit                               | More complexity for no real benefit            | Rejected |

### Decision: KEEP class_exists guard

Rationale:

1. `components/` should not hard-depend on `framework/` internals
2. FailureBoundary is a framework-level capability, not a component requirement
3. The guard is lightweight (two class_exists checks, runs once per kernel construction)
4. If FailureBoundary is present, it integrates automatically
5. If absent, the system still works (falls through to HandleIncomingHttp catch)

### Risk Assessment

| Risk                                                         | Likelihood | Impact                 | Mitigation                                 |
|--------------------------------------------------------------|------------|------------------------|--------------------------------------------|
| FailureBoundary classes removed but tests expect integration | Low        | Tests fail, CI catches | E2E tests verify                           |
| Typo in class_exists namespace                               | Low        | Silent miss            | PHPStan would catch missing class in tests |
| Performance overhead                                         | Negligible | None                   | Two class_exists calls, ~microseconds      |

## Conclusion

| Check                               | Status | Evidence                                                           |
|-------------------------------------|--------|--------------------------------------------------------------------|
| FailureBoundary in middleware stack | GREEN  | AppKernel line 80-87                                               |
| Correct middleware ordering         | GREEN  | FailureBoundary added last, executes first (LIFO)                  |
| Wraps all downstream middleware     | GREEN  | HttpFailureBoundaryMiddleware wraps $next()                        |
| Wraps router dispatch               | GREEN  | Via middleware pipeline                                            |
| HandleIncomingHttp catch justified  | GREEN  | Lifecycle safety net for unmapped failures                         |
| class_exists guard decision         | GREEN  | Documented above, components/ should not hard-depend on framework/ |
| No hard coupling                    | GREEN  | class_exists prevents compile-time dependency                      |
