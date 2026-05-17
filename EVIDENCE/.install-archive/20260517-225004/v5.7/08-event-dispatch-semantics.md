# V5.7 — 08 Event Dispatch Semantics

**Date:** 2026-05-12

## 1. Listener Order

**Rule:** Sort by priority descending (higher priority executes first).

**Tie-breaking:** Registration order (first registered executes first among same priority).

```php
onEvent(UserRegistered::class)
    ->do(AuditListener::class, priority: 100)    // Executes first
    ->do(EmailListener::class, priority: 50)     // Executes second
    ->do(MetricsListener::class, priority: 50)   // Executes third (same priority as Email)
    ->do(LogListener::class);                     // Executes last (priority 0)
```

**Deterministic order guarantee:** Same registration sequence always produces same execution order.

## 2. No Listener Case

**Behavior:** `emit($event)` returns the same event object without error.

```php
$result = emit(new SomeRareEvent());
// $result === the same event instance
// No exception, no warning — events with no listeners are valid
```

**Why:** Not every event needs listeners. Some events are emitted for future extensibility or external consumers.

## 3. Listener Failure Behavior

**Default:** Exception bubbles up. No silent swallowing.

```php
emit(new UserRegistered($userId));
// If SendWelcomeEmail throws, the exception propagates.
// Later listeners (CreateUserProjection) do NOT execute.
```

**Rules:**

- Uncaught exceptions stop further listener execution
- Exception is NOT caught and ignored
- Future: FailureBoundary integration may allow configurable failure behavior
- No catch-and-ignore pattern

**Integration with FailureBoundary (future):**

```php
#[OnFailure(ReportFailure::class)]
#[OnFailure(Retry::class, maxAttempts: 3)]
emit(new UserRegistered($userId));
```

This is NOT V5.7. This is ROADMAP.

## 4. Stoppable Events

**Behavior:** If event implements `StoppableEventInterface` and `isPropagationStopped()` returns `true`, stop executing
further listeners.

```php
final class OrderValidation implements \Psr\EventDispatcher\StoppableEventInterface
{
    public bool $isValid = true;

    public function isPropagationStopped(): bool
    {
        return ! $this->isValid;
    }
}

// Listener that stops propagation
#[ListensTo(OrderValidation::class)]
final readonly class BlockInvalidOrders
{
    public function __invoke(OrderValidation $event): void
    {
        if ($event->orderId === 'blacklisted') {
            $event->isValid = false; // Next listeners will be skipped
        }
    }
}
```

## 5. Return Values

**Rule:** Listener return values are ignored by default.

**emit() returns:** The dispatched event object (possibly modified by listeners if mutable).

```php
$event = emit(new UserRegistered($userId));
// $event is the same UserRegistered instance
// Listener return values are discarded
```

**Why:** Events are facts. Listeners react. The event object carries state, not return values.

## 6. Async/Queued Listeners

**V5.7 Decision:** NOT IMPLEMENTED.

**Status:** ROADMAP

**Future API (not active):**

```php
onEvent(UserRegistered::class)
    ->do(SendWelcomeEmail::class, mode: 'async');
```

## 7. Observability

**V5.7 Decision:** NOT MANDATORY.

Event dispatch metrics and logging are optional. If present, they must not impact the hot path.

**Future:**

- Dispatch timing metrics
- Listener execution count
- Failed listener tracking
- Integration with Operations/Observability

## 8. Container Resolution

**Rule:** Listener classes are resolved through existing container/callable resolver.

```php
// In InvokeEventListener:
$listener = $container->get($listenerClass);  // DI resolves dependencies
$listener($event);                             // Invoke
```

**Prohibited:**

- No `$listener ?? new $listener()` in dispatch flow
- No inline factory fallback in hot path
- No `$x ?? new X()` patterns

**Why:** Listeners may have dependencies (Mailer, Logger, Repository). These must be resolved properly, not instantiated
ad-hoc.

**If no container available:**

- Closure listeners execute directly
- Invokable classes without dependencies may be instantiated
- Invokable classes with dependencies require container — throw if unavailable

## Summary Table

| Scenario              | Behavior                                      |
|-----------------------|-----------------------------------------------|
| No listeners          | Return event, no error                        |
| Listener throws       | Exception propagates, stops further listeners |
| Stoppable event       | `isPropagationStopped()` = true → halt        |
| Listener return value | Ignored                                       |
| emit() return value   | The event object                              |
| Async listeners       | ROADMAP                                       |
| Container required    | Yes for class listeners with dependencies     |
| Observability         | Optional, ROADMAP                             |
