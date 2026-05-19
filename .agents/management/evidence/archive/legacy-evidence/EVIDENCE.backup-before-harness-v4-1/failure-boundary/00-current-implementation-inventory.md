# FailureBoundary Production-Grade Closure — Current Implementation Inventory

**Date:** 2026-05-12
**Stage:** Phase 3 — Production-Grade Closure
**Baseline Commit:** main (post proof & adoption pass)

## Purpose

Inventory every file, class, capability, and gap in the current FailureBoundary implementation
so production-grade closure work has a truthful baseline.

## Full File Inventory

| File                                                                         | Type          | Responsibility                                                          | Status   | Gap                                                  |
|------------------------------------------------------------------------------|---------------|-------------------------------------------------------------------------|----------|------------------------------------------------------|
| `Capabilities/ClassifyApplicationException/ClassifyApplicationException.php` | Capability    | Maps legacy error classifications to failure decisions                  | Exists   | Legacy bridge                                        |
| `Capabilities/ClassifyFailure/ClassifyFailure.php`                           | Capability    | Decides Retry/Fallback/MapToResult/DeadLetter/Rethrow/ReportOnly        | Exists   | Complete                                             |
| `Capabilities/CleanupAfterFailure/CleanupAfterFailure.php`                   | Capability    | Post-failure cleanup hook                                               | **STUB** | Empty body — no cleanup registered                   |
| `Capabilities/CompileFailurePolicies/CompileFailurePolicies.php`             | Capability    | Reads PHP attributes → compiles FailurePolicy (reflection allowed here) | Exists   | Complete                                             |
| `Capabilities/InvalidateCompiledPolicy/InvalidateCompiledPolicy.php`         | Capability    | Invalidates cached policy for a class::method                           | Exists   | Complete                                             |
| `Capabilities/MapFailureToResult/MapFailureToResult.php`                     | Capability    | Maps exception → HTTP response via OnFailure config                     | Exists   | Complete                                             |
| `Capabilities/ReadCompiledFailurePolicies/ReadCompiledFailurePolicies.php`   | Capability    | Reads compiled JSON from cache file                                     | Exists   | Complete                                             |
| `Capabilities/RenderApplicationError/RenderApplicationError.php`             | Capability    | Renders error for user display                                          | Exists   | Complete                                             |
| `Capabilities/ReportFailure/ReportFailure.php`                               | Capability    | Reports failure to channels                                             | **MVP**  | Uses `error_log()` — needs Observability integration |
| `Capabilities/ResolveFailurePolicy/ResolveFailurePolicy.php`                 | Capability    | Resolves policy from cache, compile-on-miss                             | Exists   | Complete                                             |
| `Capabilities/RetryFailedAction/RetryFailedAction.php`                       | Capability    | Retry loop with backoff (none/linear/exponential + jitter)              | **MVP**  | Standalone — should dogfood Resilience component     |
| `Capabilities/RunFailurePipeline/RunFailurePipeline.php`                     | Capability    | Orchestrates failure decision pipeline                                  | Exists   | Complete                                             |
| `Capabilities/RunFallbackAction/RunFallbackAction.php`                       | Capability    | Instantiates fallback class, calls `__invoke`                           | Exists   | Complete                                             |
| `Capabilities/SendFailureToDeadLetter/SendFailureToDeadLetter.php`           | Capability    | Serializes failure → dead letter queue                                  | **MVP**  | Uses `error_log()` JSON — needs Queue integration    |
| `Capabilities/WriteCompiledFailurePolicies/WriteCompiledFailurePolicies.php` | Capability    | Writes compiled JSON to cache file                                      | Exists   | Complete                                             |
| `Configuration/BuildFailureBoundary.php`                                     | Configuration | Assembles all capabilities into RunProtectedAction                      | Exists   | Complete                                             |
| `Configuration/FailureBoundaryConfiguration.php`                             | Configuration | Immutable config DTO                                                    | Exists   | Complete                                             |
| `Flows/ResolveFailurePolicy/ResolveFailurePolicy.php`                        | Flow          | Flow wrapper for policy resolution                                      | Exists   | Complete                                             |
| `Flows/RunProtectedAction/RunProtectedAction.php`                            | Flow          | Main flow: try/catch/finally → pipeline → decision                      | Exists   | Complete                                             |
| `Foundation/Attributes/DeadLetter.php`                                       | Attribute     | Marks method for dead letter on failure                                 | Exists   | Compiled, not enforced at runtime                    |
| `Foundation/Attributes/Fallback.php`                                         | Attribute     | Configures fallback class                                               | Exists   | Compiled, enforced via pipeline                      |
| `Foundation/Attributes/OnFailure.php`                                        | Attribute     | Maps exception class → status code + message key                        | Exists   | Compiled, enforced via pipeline                      |
| `Foundation/Attributes/RecoverWith.php`                                      | Attribute     | Configures recovery strategy class                                      | Exists   | **Deferred** — compiled but not enforced             |
| `Foundation/Attributes/ReportFailure.php`                                    | Attribute     | Marks method for failure reporting                                      | Exists   | Compiled, enforced via pipeline                      |
| `Foundation/Attributes/Rethrow.php`                                          | Attribute     | Marks exception for rethrow                                             | Exists   | Compiled, enforced via pipeline                      |
| `Foundation/Attributes/Retry.php`                                            | Attribute     | Configures retry (maxAttempts, backoff, delayMs, jitter)                | Exists   | Compiled, enforced via pipeline                      |
| `Foundation/Attributes/Timeout.php`                                          | Attribute     | Configures timeout (ms)                                                 | Exists   | **Deferred** — compiled but not enforced             |
| `Foundation/CompiledMethodPolicy.php`                                        | Foundation    | DTO: compiled policy for a single method                                | Exists   | Complete                                             |
| `Foundation/CompiledPolicyCache.php`                                         | Foundation    | Static cache: get/put/invalidate with staleness check                   | Exists   | Complete                                             |
| `Foundation/FailureAction.php`                                               | Foundation    | Enum: action type in policy                                             | Exists   | Complete                                             |
| `Foundation/FailureBoundaryFailed.php`                                       | Foundation    | Exception: boundary execution failed                                    | Exists   | Complete                                             |
| `Foundation/FailureBoundaryKind.php`                                         | Foundation    | Enum: http/console/queue                                                | Exists   | Complete                                             |
| `Foundation/FailureContext.php`                                              | Foundation    | DTO: failure context (kind, target, request)                            | Exists   | Complete                                             |
| `Foundation/FailureDecision.php`                                             | Foundation    | Enum: Retry/Fallback/MapToResult/DeadLetter/Rethrow/ReportOnly          | Exists   | Complete                                             |
| `Foundation/FailurePipelineResult.php`                                       | Foundation    | DTO: pipeline result (value, decision, metadata)                        | Exists   | Complete                                             |
| `Foundation/FailurePolicy.php`                                               | Foundation    | DTO: full failure policy for a method                                   | Exists   | Complete                                             |
| `Foundation/UnhandledFailure.php`                                            | Foundation    | Exception: unhandled failure wrapper                                    | Exists   | Complete                                             |
| `Integration/HttpFailureBoundaryMiddleware.php`                              | Integration   | PSR-15-style middleware wrapping request in boundary                    | Exists   | Complete                                             |
| `PublicSurface/FailureBoundary.php`                                          | PublicSurface | Static facade for easy access                                           | Exists   | Complete                                             |

## Capability Summary

| Capability     | Files | Real Implementation | MVP            | Stub       | Deferred                         |
|----------------|-------|---------------------|----------------|------------|----------------------------------|
| Classification | 2     | Yes                 | -              | -          | -                                |
| Compilation    | 3     | Yes                 | -              | -          | -                                |
| Pipeline       | 1     | Yes                 | -              | -          | -                                |
| Reporting      | 1     | Yes                 | error_log      | -          | Observability integration needed |
| Retry          | 1     | Yes                 | standalone     | -          | Resilience dogfooding needed     |
| Fallback       | 1     | Yes                 | -              | -          | -                                |
| DeadLetter     | 1     | Yes                 | error_log JSON | -          | Queue integration needed         |
| Cleanup        | 1     | -                   | -              | Empty stub | -                                |
| Rendering      | 1     | Yes                 | -              | -          | -                                |

## Integration Summary

| Integration                  | Status                          | Evidence                                    |
|------------------------------|---------------------------------|---------------------------------------------|
| HTTP middleware in AppKernel | Integrated (class_exists guard) | `components/HTTP/.../AppKernel.php`         |
| Real attribute usage         | DemoFailureController           | `examples/FailureBoundaryDemo/`             |
| E2E tests                    | 7 tests                         | `tests/E2E/FailureBoundaryAdoptionTest.php` |
| Unit tests                   | 52 tests / 106 assertions       | `tests/Unit/Framework/FailureBoundary/`     |
| PHPStan                      | Clean                           | Verified                                    |

## Deferred Features

| Feature                            | Reason                                   | Priority                        |
|------------------------------------|------------------------------------------|---------------------------------|
| Timeout enforcement                | Requires fiber/async runtime integration | Deferred to V4 runtime adapters |
| RecoverWith enforcement            | Requires recovery strategy component     | Deferred to reliability engine  |
| CleanupAfterFailure implementation | No concrete cleanup needs identified yet | Deferred until actual need      |

## MVP Upgrades Needed

| MVP                     | Current               | Target                                                                  | Dependency                       |
|-------------------------|-----------------------|-------------------------------------------------------------------------|----------------------------------|
| ReportFailure           | `error_log()` string  | `Observability/Logging/Logger` with `StructuredLogRecord` + `Redaction` | Observability component exists   |
| SendFailureToDeadLetter | `error_log()` JSON    | `SystemDesign/Messaging/DeadLetters/DeadLetterQueue` or Outbox          | Messaging/Queue component exists |
| RetryFailedAction       | Standalone retry loop | `Operations/Resilience/Retry/RetryExecutor`                             | Resilience component exists      |
