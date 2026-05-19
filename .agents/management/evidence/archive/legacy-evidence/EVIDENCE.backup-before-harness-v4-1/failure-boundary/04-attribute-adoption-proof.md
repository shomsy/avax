# FailureBoundary — Attribute Adoption & Anti-Decoration Proof

**Date:** 2026-05-12
**Stage:** Phase 3 — Production-Grade Closure

## Purpose

Prove that FailureBoundary attributes are not decorative: they affect real runtime behavior
through compiled metadata and pipeline execution.

## Attribute Inventory

| Attribute          | Defined | Compiled | Pipeline Action             | Runtime Enforced | E2E Tested |
|--------------------|---------|----------|-----------------------------|------------------|------------|
| `#[OnFailure]`     | Yes     | Yes      | MapToResult                 | Yes              | Yes        |
| `#[ReportFailure]` | Yes     | Yes      | ReportOnly (always reports) | Yes              | Yes        |
| `#[Retry]`         | Yes     | Yes      | Retry                       | Yes              | Unit only  |
| `#[Fallback]`      | Yes     | Yes      | Fallback                    | Yes              | Unit only  |
| `#[DeadLetter]`    | Yes     | Yes      | DeadLetter                  | Yes              | Unit only  |
| `#[Rethrow]`       | Yes     | Yes      | Rethrow                     | Yes              | Unit only  |
| `#[Timeout]`       | Yes     | Yes      | None                        | **No**           | No         |
| `#[RecoverWith]`   | Yes     | Yes      | None                        | **No**           | No         |

## Anti-Decoration Proof

### Test 1: Attribute changes runtime behavior

`tests/E2E/FailureBoundaryAdoptionTest::testOnFailureAttributeMapsInvalidArgumentExceptionTo422()`

- Controller method: `#[OnFailure(InvalidArgumentException::class, respondWith: 422)]`
- Action: throws `InvalidArgumentException`
- Result: Returns 422 response (not 500)
- Proof: Without the attribute, the exception would propagate as 500 via HandleIncomingHttp catch

### Test 2: Different attributes produce different behavior

`testOnFailureAttributeMapsRuntimeExceptionTo503()` vs `testOnFailureAttributeMapsInvalidArgumentExceptionTo422()`

- Same controller, different methods, different attributes
- `#[OnFailure(RuntimeException, 503)]` → 503
- `#[OnFailure(InvalidArgumentException, 422)]` → 422
- Proof: Attributes control behavior, not decoration

### Test 3: Removing attribute changes behavior

`testRemovingAttributeChangesBehavior()`

- Clears compiled cache (simulates no attribute)
- Same controller method that normally maps to 422
- Result: Exception propagates differently (not mapped to 422)
- Proof: Behavior depends on compiled policy, which comes from attributes

### Test 4: Unmapped exceptions propagate

`testUnhandledExceptionPropagatesNotSwallowed()`

- Method with NO attributes
- Throws `LogicException`
- Result: Exception propagates, not swallowed
- Proof: FailureBoundary only handles what attributes declare

### Test 5: Compiled metadata drives pipeline

`testCompiledPolicyResolvesForDemoController()`

- Compiles policy for `DemoFailureController::throwsValidation`
- Asserts policy is not null, target class/method match, has action for `InvalidArgumentException`
- Proof: Attributes are read at compile time, stored as metadata, used at runtime

## Compilation Flow

```
DemoFailureController::throwsValidation()
  ↓ (has #[OnFailure], #[ReportFailure])
CompileFailurePolicies.compile(class, method)
  ↓ (reflection — COMPILE_PATH_ALLOWED only)
Read attributes → build FailurePolicy
  ↓
CompiledPolicyCache.put(class::method, policy)
  ↓ (static cache, no reflection)
ResolveFailurePolicy.for(context)
  ↓ (reads from cache)
RunFailurePipeline.for(failure, context, action)
  ↓
ClassifyFailure.decide(failure, policy)
  ↓
match (decision):
  MapToResult → MapFailureToResult.execute() → Response(422)
  ReportOnly → ReportFailure.for() + throw UnhandledFailure
  Retry → RetryFailedAction.execute()
  Fallback → RunFallbackAction.execute()
  DeadLetter → SendFailureToDeadLetter.send()
  Rethrow → throw $failure
```

## Reflection Boundary

| Location                           | Uses Reflection? | Allowed?                                    |
|------------------------------------|------------------|---------------------------------------------|
| `CompileFailurePolicies.compile()` | Yes              | COMPILE_PATH_ALLOWED — only at compile time |
| `CompiledPolicyCache.get/put()`    | No               | Runtime cache access                        |
| `ResolveFailurePolicy.for()`       | No               | Reads from cache                            |
| `RunFailurePipeline.for()`         | No               | Uses compiled policy DTOs                   |
| All other runtime paths            | No               | N/A                                         |

## Real Attribute Usage

### Production Code

| File                                                     | Attributes Used                                | Purpose       |
|----------------------------------------------------------|------------------------------------------------|---------------|
| `examples/FailureBoundaryDemo/DemoFailureController.php` | `#[OnFailure]`, `#[ReportFailure]`, `#[Retry]` | Demo adoption |

### Middleware Default Behavior

`HttpFailureBoundaryMiddleware` uses FailureBoundary for ALL requests through the middleware stack.
Even without explicit attributes on a controller method, the boundary:

1. Compiles policies (finds none)
2. Catches failures
3. Classifies (empty policy → Rethrow)
4. Rethrows to HandleIncomingHttp catch

This means the boundary is always active; attributes customize behavior.

## Anti-Decoration Conclusion

| Claim                               | Proof                                     | Status       |
|-------------------------------------|-------------------------------------------|--------------|
| Attributes affect runtime behavior  | E2E test: 422 vs 503 vs propagate         | GREEN        |
| Attributes are not ignored          | Compilation → cache → pipeline → decision | GREEN        |
| No hot-path reflection              | Reflection only in CompileFailurePolicies | GREEN        |
| Compiled metadata is real           | Cached, stale-checked, used at runtime    | GREEN        |
| Removing attribute changes behavior | E2E test: cache clear → different result  | GREEN        |
| Timeout is decorative               | Compiled but not enforced                 | RED/deferred |
| RecoverWith is decorative           | Compiled but not enforced                 | RED/deferred |
