# FailureBoundary — Rethrow and Cleanup Proof

**Date:** 2026-05-12
**Stage:** Phase 3 — Production-Grade Closure

## Rethrow

### How It Works

`Rethrow` is the default behavior for unmapped failures. When `ClassifyFailure` finds no matching
action for an exception, it returns `FailureDecision::Rethrow`.

```php
// RunFailurePipeline.php
return match ($decision) {
    // ...
    FailureDecision::Rethrow => throw $failure,
    // ...
};
```

### Runtime Enforcement: YES

Rethrow is enforced in the pipeline. Every unmapped exception is re-thrown, not swallowed.

### Proof

`HttpFailureBoundaryMiddleware` wraps the request pipeline. If an exception is thrown with no
matching `#[OnFailure]` attribute:

1. `RunProtectedAction.run()` catches the exception
2. `RunFailurePipeline.for()` classifies it → Rethrow (no matching action)
3. Pipeline throws the original exception
4. Exception propagates to `HandleIncomingHttp` catch → 500

### Rethrow Except

The `#[Rethrow]` attribute can specify exceptions that should always rethrow, even if other
attributes match. This is compiled into `$policy->rethrowExcept` and checked by `ClassifyFailure`.

### Status: GREEN

## CleanupAfterFailure

### Current State

```php
final readonly class CleanupAfterFailure
{
    public function for(FailureContext $context): void
    {
        // Cleanup hooks can be registered here in future versions.
    }
}
```

### Called From

`RunProtectedAction.run()` always calls cleanup in the `finally` block:

```php
try {
    return $action();
} catch (Throwable $failure) {
    $result = $this->pipeline->for($failure, $context, $action);
    // ...
} finally {
    $this->cleanup->for($context);
}
```

### Assessment

The stub is **intentional**. The `finally` block ensures cleanup always runs, even if:
- No specific cleanup is needed
- Pipeline completes successfully
- Exception is rethrown

When concrete cleanup needs are identified (e.g., releasing connections, resetting state,
clearing caches), hooks can be added to `CleanupAfterFailure`.

### Classification: EXPECTED_STUB

Not a gap. Not a bug. An intentional placeholder for future extension.

### Status: YELLOW (stub is intentional, documented)
