# FailureBoundary — Dogfooding Proof

**Date:** 2026-05-12
**Stage:** Phase 3 — Production-Grade Closure

## Purpose

Prove which existing AvaX components FailureBoundary already reuses,
and identify where it duplicates behavior that should be replaced with component integration.

## Already Dogfooded

| Behavior | Reused Component | Usage Location | Status |
|----------|-----------------|----------------|--------|
| Response building | `Components/HTTP/Response/ResponseFactory` | `MapFailureToResult.execute()` | GREEN |
| Response building | `Components/HTTP/Response/System/PublicSurface/Response` | `HttpFailureBoundaryMiddleware.handle()` | GREEN |
| Middleware contract | `Components/HTTP/Middleware/System/PublicSurface/MiddlewareInterface` | `HttpFailureBoundaryMiddleware` implements | GREEN |
| Request interface | `Components/HTTP/Request/System/PublicSurface/RequestInterface` | `FailureContext.forHttp()` | GREEN |
| HTTP context type | `FailureBoundaryKind::Http` | `FailureContext.forHttp()` | GREEN |

## MVP — Needs Dogfooding Integration

### 1. ReportFailure → Observability Logging

**Current:**
```php
// ReportFailure.php
error_log($message);
```

**Target:**
```php
// Should use:
Components/Operations/Observability/System/Capabilities/Logging/Logger
Components/Operations/Observability/System/Capabilities/Logs/StructuredLogRecord
Components/Security/Redaction/System/PublicSurface/Redaction
```

**Available components:**
- `Logger` — structured logging with handlers, supports info/warning/error/debug levels
- `StructuredLogRecord` — redacts sensitive keys automatically via `Redaction::redactLog()`
- `FileLogWriter` — NDJSON file output with redaction
- `Redaction` — automatic sensitive key redaction (password, secret, token, api_key, etc.)

**Gap:** ReportFailure uses raw `error_log()` with no structured context, no redaction, no level.
**Integration plan:** Replace `error_log()` with `Logger::error()` using `StructuredLogRecord` containing:
  - Exception class, message, file, line
  - Failure context (kind, target class/method)
  - Channel from policy
  - Automatic redaction of sensitive context

**Risk:** Adding Logger dependency increases coupling. Mitigation: accept Logger via constructor or use optional integration.

**Status: YELLOW** — MVP works, production needs Observability integration.

### 2. SendFailureToDeadLetter → Messaging Dead Letter Queue

**Current:**
```php
// SendFailureToDeadLetter.php
error_log('[DeadLetter] ' . json_encode($payload, JSON_THROW_ON_ERROR));
```

**Target:**
```php
// Should use:
Components/SystemDesign/System/Capabilities/Messaging/DeadLetters/DeadLetterQueue
```

**Available components:**
- `DeadLetterQueue` (SystemDesign/Messaging) — canonical dead letter queue for messaging
- `MessageEnvelope` — message wrapper with metadata
- `Outbox` — outbox pattern for reliable message delivery

**Gap:** Dead letter is logged as JSON, not sent to an actual queue.
**Integration plan:** Replace `error_log()` with `DeadLetterQueue::enqueue()` or Outbox pattern.
Payload should use `MessageEnvelope` with failure metadata.

**Risk:** Messaging component may not be production-ready. Mitigation: keep error_log as fallback.

**Status: YELLOW** — MVP works, production needs Messaging integration.

### 3. RetryFailedAction → Resilience Retry

**Current:**
```php
// RetryFailedAction.php — standalone retry loop
for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
    try {
        $result = $originalAction();
        return FailurePipelineResult::retried($result, $attempt);
    } catch (Throwable $e) {
        // backoff calculation...
    }
}
throw $lastException;
```

**Target:**
```php
// Should use:
Components/Operations/Resilience/System/Capabilities/Retry/RetryExecutor
Components/Operations/Resilience/System/Capabilities/Retry/RetryOptions
Components/Operations/Resilience/System/Capabilities/Retry/RetryResult
```

**Available components:**
- `RetryExecutor` — accepts `Closure $operation` + `RetryOptions`, returns `RetryResult`
- `RetryOptions` — attempts, backoffMs configuration
- `RetryResult` — success/failure with attempt count and last exception
- `BackoffSchedule` — configurable backoff patterns

**Gap:** RetryFailedAction implements its own retry loop with backoff (none/linear/exponential + jitter).
Resilience has `RetryExecutor` but uses a simpler model (fixed backoff with random jitter).

**Integration complexity:** MEDIUM
- FailureBoundary `Retry` attribute has: maxAttempts, backoff (none/linear/exponential), delayMs, jitter
- Resilience `RetryOptions` has: attempts, backoffMs (simpler)
- Need a translator between attribute config and Resilience options
- Resilience `RetryExecutor` returns `RetryResult`, not the raw result — needs adaptation

**Decision:** Keep standalone for now. Resilience integration would require:
1. Creating a `RetryOptions` builder from `FailurePolicy` retry config
2. Adapting `RetryResult` to `FailurePipelineResult`
3. Handling the case where Resilience returns failure (needs to throw for pipeline)

**Status: YELLOW** — MVP standalone retry is functional. Resilience integration is a future improvement.

## Not Applicable for Dogfooding

| Behavior | Why Not Dogfood |
|----------|-----------------|
| Fallback execution | Attribute-driven model unique to FailureBoundary |
| Classification logic | Decision engine specific to FailureBoundary |
| Compilation/Reflection | PHP attribute reading, not a reusable component |
| Pipeline orchestration | Internal to FailureBoundary |

## Dogfooding Summary

| Area | Status | Gap | Priority |
|------|--------|-----|----------|
| Response building | GREEN | None | - |
| Middleware contract | GREEN | None | - |
| Request interface | GREEN | None | - |
| Failure reporting | YELLOW | Needs Observability Logger integration | Medium |
| Dead letter queue | YELLOW | Needs Messaging DeadLetterQueue integration | Medium |
| Retry execution | YELLOW | Needs Resilience RetryExecutor integration | Low |

## Overall Dogfooding Status: YELLOW

FailureBoundary reuses HTTP components correctly but has 3 MVP capabilities that should integrate
with existing AvaX components for production readiness.
