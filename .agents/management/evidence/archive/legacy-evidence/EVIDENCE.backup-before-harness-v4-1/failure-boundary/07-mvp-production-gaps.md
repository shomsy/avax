# FailureBoundary — MVP Production Gaps

**Date:** 2026-05-12
**Stage:** Phase 3 — Production-Grade Closure

## Purpose

Document every MVP gap in the current FailureBoundary implementation with specific upgrade paths,
dependencies, and risk assessments. No gap may be hidden, minimized, or marked GREEN without proof.

## Gap 1: ReportFailure uses error_log()

**File:** `framework/System/Capabilities/FailureBoundary/Capabilities/ReportFailure/ReportFailure.php`

**Current code:**

```php
error_log($message);
```

**Problem:**

- Unstructured string output
- No log level (always error_log, can't distinguish info/warning/error/critical)
- No redaction of sensitive data in context
- No structured format (JSON, NDJSON)
- No correlation ID, request ID, or trace ID
- Cannot be intercepted, filtered, or routed
- Not testable without mocking global error_log

**Target:**

```php
// Use Observability Logger with StructuredLogRecord
$logger = new Logger();
$logger->error($message, [
    'exception_class' => $failure::class,
    'exception_message' => $failure->getMessage(),
    'file' => $failure->getFile(),
    'line' => $failure->getLine(),
    'kind' => $context->kind->value,
    'target_class' => $context->targetClass,
    'target_method' => $context->targetMethod,
    'channel' => $policy->reportChannel ?? 'default',
]);
```

**Dependencies available:**

- `Components/Operations/Observability/System/Capabilities/Logging/Logger` ✅
- `Components/Operations/Observability/System/Capabilities/Logs/StructuredLogRecord` ✅
- `Components/Security/Redaction/System/PublicSurface/Redaction` ✅ (built into StructuredLogRecord::toArray)

**Integration approach:**

1. Accept `Logger` via constructor (dependency injection)
2. Create `StructuredLogRecord` with failure context
3. `StructuredLogRecord::toArray()` automatically redacts sensitive keys
4. Logger handlers receive the record for routing

**Risk:** LOW — Observability Logger exists and is stable.

**Estimated effort:** LOW — constructor change + error_log → Logger call.

## Gap 2: SendFailureToDeadLetter uses error_log() JSON

**File:**
`framework/System/Capabilities/FailureBoundary/Capabilities/SendFailureToDeadLetter/SendFailureToDeadLetter.php`

**Current code:**

```php
error_log('[DeadLetter] ' . json_encode($payload, JSON_THROW_ON_ERROR));
```

**Problem:**

- Dead letter is lost after log write (no persistence guarantee)
- No queue semantics (no retry, no consumer, no visibility)
- No message envelope structure
- No deduplication or ordering guarantee
- Not testable without mocking global error_log

**Target:**

```php
// Use SystemDesign Messaging DeadLetterQueue
$deadLetterQueue = new DeadLetterQueue();
$envelope = new MessageEnvelope(
    body: $payload,
    metadata: [
        'failure_type' => $failure::class,
        'context' => $context->kind->value,
        'timestamp' => date('c'),
    ],
);
$deadLetterQueue->enqueue($envelope);
```

**Dependencies available:**

- `Components/SystemDesign/System/Capabilities/Messaging/DeadLetters/DeadLetterQueue` ✅
- `Components/SystemDesign/System/Capabilities/Messaging/Envelope/MessageEnvelope` ✅
- `Components/SystemDesign/System/Capabilities/Messaging/Outbox/Outbox` ✅ (for reliable delivery)

**Integration approach:**

1. Accept `DeadLetterQueue` via constructor
2. Wrap failure data in `MessageEnvelope`
3. Enqueue to dead letter queue
4. Keep error_log as fallback if queue is not configured

**Risk:** MEDIUM — Messaging component is SystemDesign (theoretical/design-level), may not have real persistence.

**Estimated effort:** MEDIUM — need to verify Messaging component is production-ready.

## Gap 3: RetryFailedAction is standalone

**File:** `framework/System/Capabilities/FailureBoundary/Capabilities/RetryFailedAction/RetryFailedAction.php`

**Current code:** Standalone retry loop with backoff calculation.

**Problem:**

- Duplicates retry logic that exists in Resilience component
- Different backoff model than Resilience (attribute-driven vs options-driven)
- No integration with Resilience's retry metrics, tracing, or idempotency

**Target:**

```php
// Use Resilience RetryExecutor
$retryOptions = RetryOptionsBuilder::fromFailurePolicy($policy)->build();
$executor = new RetryExecutor($originalAction, $retryOptions);
$result = $executor->execute();
if (!$result->success) {
    throw $result->lastException;
}
return FailurePipelineResult::retried($result->result, $result->attempts);
```

**Dependencies available:**

- `Components/Operations/Resilience/System/Capabilities/Retry/RetryExecutor` ✅
- `Components/Operations/Resilience/System/Capabilities/Retry/RetryOptions` ✅
- `Components/Operations/Resilience/System/Capabilities/Retry/RetryResult` ✅

**Integration approach:**

1. Create a builder/translator from `FailurePolicy` retry config to `RetryOptions`
2. Wrap `RetryExecutor.execute()` result into `FailurePipelineResult`
3. Throw on exhausted retries (Resilience returns failure, pipeline expects exception)

**Risk:** LOW-MEDIUM — Resilience exists but model differences need adaptation.

**Estimated effort:** MEDIUM — requires adapter between attribute config and Resilience options.

**Decision:** Defer to future work. Standalone retry is functional and well-tested.

## Gap 4: CleanupAfterFailure is empty stub

**File:** `framework/System/Capabilities/FailureBoundary/Capabilities/CleanupAfterFailure/CleanupAfterFailure.php`

**Current code:**

```php
public function for(FailureContext $context): void
{
    // Cleanup hooks can be registered here in future versions.
}
```

**Problem:** No cleanup behavior, but the method is called in every `finally` block.

**Assessment:** This is intentional. The `finally` block ensures cleanup always runs,
even if no specific cleanup is needed yet. When cleanup hooks are needed (e.g., releasing
connections, resetting state), they can be added here.

**Decision:** Keep as stub. No concrete cleanup needs identified. Document as EXPECTED_STUB.

## Gap 5: Timeout attribute not enforced

**File:** `framework/System/Capabilities/FailureBoundary/Foundation/Attributes/Timeout.php`

**Problem:** Attribute is compiled into policy but never checked at runtime.

**Assessment:** Timeout enforcement requires:

- Fiber-based execution (PHP 8.1+ fibers)
- Or async runtime integration (ReactPHP, Swoole, FrankenPHP)
- Or signal-based interruption (Unix only, not portable)

**Decision:** DEFERRED to V4 runtime adapters. Document in policy as KNOWN_DEFERRED.

## Gap 6: RecoverWith attribute not enforced

**File:** `framework/System/Capabilities/FailureBoundary/Foundation/Attributes/RecoverWith.php`

**Problem:** Attribute is compiled into policy but never executed at runtime.

**Assessment:** RecoverWith requires:

- A recovery strategy component that can restore system state
- Knowledge of what "recovery" means for the specific context
- Integration with reliability engine

**Decision:** DEFERRED to V5.6 reliability engine. Document in policy as KNOWN_DEFERRED.

## Gap Summary

| Gap                      | Severity | Fix                           | Dependency                 | Effort   |
|--------------------------|----------|-------------------------------|----------------------------|----------|
| ReportFailure error_log  | MEDIUM   | Use Observability Logger      | Logger exists              | LOW      |
| DeadLetter error_log     | MEDIUM   | Use Messaging DeadLetterQueue | Messaging may not be ready | MEDIUM   |
| Retry standalone         | LOW      | Use Resilience RetryExecutor  | Resilience exists          | MEDIUM   |
| CleanupAfterFailure stub | LOW      | Add hooks when needed         | None yet                   | N/A      |
| Timeout not enforced     | LOW      | Fiber/async runtime           | V4 runtime adapters        | DEFERRED |
| RecoverWith not enforced | LOW      | Recovery strategy             | V5.6 reliability engine    | DEFERRED |

## Honest Status Classification

| Area                      | Status | Reason                                  |
|---------------------------|--------|-----------------------------------------|
| Core implementation       | GREEN  | All files exist, real code, tested      |
| Compilation/metadata      | GREEN  | Reflection confined, cache works        |
| HTTP integration          | GREEN  | Middleware integrated, correct ordering |
| OnFailure enforcement     | GREEN  | E2E proven                              |
| ReportFailure enforcement | YELLOW | Works but uses error_log                |
| Retry enforcement         | YELLOW | Works but standalone                    |
| Fallback enforcement      | GREEN  | Works, attribute-driven                 |
| DeadLetter enforcement    | YELLOW | Works but uses error_log                |
| Rethrow enforcement       | GREEN  | Works, default behavior                 |
| Timeout enforcement       | RED    | Not enforced, deferred                  |
| RecoverWith enforcement   | RED    | Not enforced, deferred                  |
| CleanupAfterFailure       | YELLOW | Stub, but intentional                   |
| Dogfooding                | YELLOW | 3 components need integration           |
