# Resilience

## Circuit Breaker

Protects external service calls from cascading failures.

### State Transitions

```
Closed ──(failures >= threshold)──► Open
  ▲                                    │
  │                                    │ (after cooldown)
  │                                    ▼
  └────(success)────────────── HalfOpen ──(failure)──► Open
```

| State    | Behavior                                                                |
|----------|-------------------------------------------------------------------------|
| Closed   | Operations execute normally. Failures counted.                          |
| Open     | Operations rejected immediately with RuntimeException.                  |
| HalfOpen | Single test operation allowed. Success closes circuit, failure reopens. |

### Usage

```php
$circuitBreaker = new CircuitBreaker(failureThreshold: 3, cooldownSeconds: 30);

$circuitBreaker->run(function () {
    // Call external service
});
```

### Important Notes

- Cooldown uses real `time()` — HalfOpen transition requires actual time to pass
- State transitions happen on `state()` check and `recordSuccess/Failure`
- HalfOpen mode allows only one test operation — no concurrent test protection

## Timeout

Enforces execution time limits on operations.

### Behavior

- Checks elapsed time **after** the callable completes (post-execution check)
- Does NOT interrupt long-running operations during execution
- Throws `OperationTimedOut` if elapsed > timeoutMs

### Usage

```php
$timeout = new Timeout(timeoutMs: 5000);

$result = $timeout->run(function () {
    // Operation that must complete within 5 seconds
});
```

### Important Notes

- If the operation blocks forever, the timeout will NOT interrupt it
- The timeout check happens after the callable returns
- Suitable for operations that eventually complete but may be slow

## Fallback

Provides alternative behavior when primary operations fail.

### Behavior

- Executes callables in order until one succeeds
- Returns the result of the first successful callable
- Throws the last exception if all callables fail

### Usage

```php
$result = Fallback::execute([
    fn() => callExternalService(),     // Primary — may fail
    fn() => getCachedResponse(),       // Fallback 1
    fn() => returnDegradedResponse(),  // Fallback 2
]);
```

### Important Notes

- No automatic retry — callables execute once each in order
- No circuit breaker integration — fallback does not check circuit state
- Last exception is thrown if all fallbacks fail — caller must handle

## Combined Resilience Pattern

The `ProcessWebhookJob` combines all three patterns:

```php
$this->circuitBreaker->run(function () {
    return $this->timeout->run(function () {
        // Process webhook payload
        // - Circuit breaker protects against repeated failures
        // - Timeout prevents slow operations from blocking
        // - Fallback chain provides degraded behavior if needed
    });
});
```

This ensures:

1. If the external service is failing, the circuit opens and stops wasting resources
2. If a single operation is slow, it times out and counts as a failure
3. If all else fails, fallback strategies provide graceful degradation
