# Async Boundaries

This document covers async boundary behavior when/if supported.

## Current State

The container is currently **synchronous-only**. Async support is **not implemented** and is marked for future
consideration.

## Future Async Support (If/When Implemented)

### AC-104: Async Boundary Behavior Is Explicit, Safe, and Documented

If async support is added, the following contracts apply:

#### Async-Safe Services

A service must be explicitly marked as async-safe:

```php
$container->singleton(HttpClient::class)
    ->asyncSafe(); // Mark as safe for async context
```

#### Async Boundary Rules

1. **No shared state crossing async boundaries**: Shared services with mutable state cannot be used across async
   boundaries without explicit context isolation
2. **Scope isolation**: Each async task gets its own scope frame
3. **Pool isolation**: Pooled services used in async contexts must be isolated per-task

#### Error Handling

- Async resolution failures produce structured async-specific exceptions
- Timeout and cancellation are explicit lifecycle events
- Diagnostics include async context chain

#### Documentation Requirements

When async is implemented, docs must include:

- Migration guide from sync to async services
- Debugging async resolution issues
- Performance implications of async boundaries

## Current Workarounds

Until async is implemented:

1. Use deferred providers for lazy async initialization
2. Use scope isolation for request-bound async work
3. Use pooled services with explicit reset for high-frequency async handlers

---

*AC-104 is marked as "if/when supported" - implementation is future work.*
