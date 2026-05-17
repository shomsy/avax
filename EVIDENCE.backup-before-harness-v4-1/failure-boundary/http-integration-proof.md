# HTTP Integration Proof

Date: 2026-05-12

## Integration Point

**Component:** `HttpFailureBoundaryMiddleware`
**File:** `framework/System/Capabilities/FailureBoundary/Integration/HttpFailureBoundaryMiddleware.php`
**Interface:** `Avax\Components\HTTP\Middleware\System\PublicSurface\MiddlewareInterface`

## Call Flow

```
HTTP Request
  ↓
FailureBoundaryMiddleware.handle(request, next)
  ↓
  Creates FailureContext::forHttp(request)
  ↓
  RunProtectedAction.run(
    action: fn() => next(request),
    context: failureContext
  )
    ↓
    ┌─ try {
    │   action()  →  next(request)  →  full pipeline + router dispatch
    │
    ├─ } catch (Throwable $failure) {
    │   RunFailurePipeline.for(failure, context)
    │     ↓
    │     1. ResolveFailurePolicy → CompiledPolicyCache (no reflection)
    │     2. ClassifyFailure → matches against policy rules
    │     3. ReportFailure → reports to configured channels
    │     4. Decision routing:
    │        - Retry → retry with backoff
    │        - Fallback → invoke fallback handler
    │        - MapToResult → ResponseFactory.createErrorResponse()
    │        - DeadLetter → serialize to dead letter
    │        - Rethrow → throw $failure (propagates)
    │        - ReportOnly → throw UnhandledFailure
    │
    ├─ } finally {
    │   CleanupAfterFailure.for(context)
    │ }
  ↓
ResponseInterface (or rethrows)
```

## What Is Wrapped

- All downstream middleware (cors, rate-limit, json, session, etc.)
- Router dispatch
- Controller/action execution
- Any exception thrown by the above

## What Is Not Wrapped

- `HandleIncomingHttp` outer catch (line 73) — kept as lifecycle safety net
- Boot-time failures (BootApplication)
- Worker lifecycle failures (WorkerLoop)
- Database transaction failures (Transactions)

These are legitimate lifecycle/domain boundaries documented in `try-catch-inventory.md`.

## How Policy/Policy Is Resolved

1. **Compile time (once):** `CompileFailurePolicies` reads attributes via reflection, builds `CompiledMethodPolicy`,
   stores in `CompiledPolicyCache`
2. **Runtime (every request):** `ResolveFailurePolicy` reads from `CompiledPolicyCache` — no reflection
3. **Cache miss:** Compile-on-demand, then cache
4. **Staleness:** `sourceMtime` compared against current file `filemtime()`

## How Unmapped Failures Behave

When an exception is caught but **not** matched by any `#[OnFailure]` rule:

1. `ClassifyFailure` finds no matching action
2. No retry, fallback, or deadletter configured
3. Decision: `Rethrow`
4. Original exception propagates
5. `HandleIncomingHttp` outer catch returns generic 500 (lifecycle safety net)

**The failure is NOT swallowed.** The middleware is transparent to unmapped exceptions.

## How Cleanup Is Guaranteed

The `finally` block in `RunProtectedAction.run()` always executes `CleanupAfterFailure.for(context)`, regardless of
success or failure. This is guaranteed by PHP language semantics.

## Middleware Registration

The middleware is available but not auto-registered in `AppKernel`'s default stack (components must not hard-depend on
framework classes). Registration is done via class_exists guard pattern when the framework boots the application kernel.
