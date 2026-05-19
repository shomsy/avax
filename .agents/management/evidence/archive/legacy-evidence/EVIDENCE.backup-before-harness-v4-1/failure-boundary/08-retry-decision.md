# FailureBoundary — Retry Decision

**Date:** 2026-05-12
**Stage:** Phase 3 — Production-Grade Closure

## Current State

`RetryFailedAction` implements a standalone retry loop with configurable backoff:

```php
for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
    try {
        $result = $originalAction();
        return FailurePipelineResult::retried($result, $attempt);
    } catch (Throwable $e) {
        $lastException = $e;
        if ($attempt < $maxAttempts) {
            $waitMs = $this->calculateDelay($backoff, $delayMs, $attempt, $jitter);
            if ($waitMs > 0) usleep($waitMs * 1000);
        }
    }
}
throw $lastException;
```

Backoff strategies: `none`, `linear`, `exponential` + optional jitter.

## Available Alternative

`Components/Operations/Resilience/System/Capabilities/Retry/RetryExecutor` exists but uses a simpler model:

- Fixed `backoffMs` with random jitter (no linear/exponential)
- Returns `RetryResult` (success/result/attempts/lastException)
- Does not throw on exhausted retries

## Comparison

| Feature            | RetryFailedAction         | Resilience RetryExecutor |
|--------------------|---------------------------|--------------------------|
| Backoff strategies | none, linear, exponential | Fixed + random jitter    |
| Jitter control     | Configurable boolean      | Always random            |
| Return type        | Mixed (result or throws)  | RetryResult DTO          |
| Integration        | Direct pipeline use       | Requires adapter         |
| Tests              | 59 tests pass             | Part of Resilience suite |

## Decision: Keep Standalone

Reasons:

1. **Feature completeness** — RetryFailedAction supports 3 backoff strategies; Resilience supports 1
2. **Pipeline integration** — Returns result directly or throws, matching pipeline expectations
3. **Test coverage** — Well-tested with current implementation
4. **Integration cost** — Would require adapter layer + backoff strategy extension in Resilience

## Future Integration Path

If Resilience is enhanced to support configurable backoff strategies:

1. Extend `RetryOptions` to include backoff strategy enum
2. Create adapter from `FailurePolicy` retry config to `RetryOptions`
3. Wrap `RetryResult` into `FailurePipelineResult`
4. Handle exhausted retry case (throw exception for pipeline)

## Status: GREEN

Standalone retry is functional, well-tested, and feature-complete for the FailureBoundary use case.
