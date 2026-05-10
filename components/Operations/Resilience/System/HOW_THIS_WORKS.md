# How This Works: Resilience Component

## What This Component Does

The Resilience component provides patterns for making operations fault-tolerant under real-world failure conditions.
It implements retry with backoff, circuit breaker, timeout, bulkhead isolation, fallback chains, rate limiting,
idempotency, backpressure signaling, load shedding, distributed locking, dead-letter storage, and an outbox pattern
stub.

The public surface exposes two entry points:

- `Resilience::retry($operation)` returns a `RetryBuilder` for fluent retry configuration.
- `Resilience::circuitBreaker($threshold, $cooldown)` returns a `CircuitBreaker` instance.

The unified `ExecuteWithReliability` flow composes retry, circuit breaker, timeout, and fallback into a single
execution pipeline.

## What This Component Does NOT Do

- Does NOT interrupt running operations on timeout. PHP has no preemption, so timeout enforcement is post-hoc only
  (checks elapsed time after the operation completes).
- Does NOT provide production-grade distributed locking. The `Lock` interface has an `InMemoryLock` implementation only.
  Real distributed locks (Redis Redlock, ZooKeeper, etc.) are not implemented.
- Does NOT provide a complete Redis rate limiter. `RedisRateLimiter` has a Redis path but the class primarily uses an
  in-memory sliding window array fallback. Full Redis Lua script rate limiting is not implemented.
- Does NOT provide adaptive circuit breaking. Thresholds are static; there is no dynamic adjustment based on traffic
  volume or error rate trends.
- Does NOT provide a global retry budget. There is no mechanism to cap total retries across all callers.
- Does NOT provide partition-level bulkhead isolation. The `Bulkhead` class tracks a single active counter; it does not
  support named partitions or queue limits.
- Does NOT provide persistent dead-letter or outbox storage. Both `DeadLetterStore` and `OutboxStore` have in-memory
  implementations only.

## Public API

### Resilience Facade

```php
use Avax\Components\Operations\Resilience\System\PublicSurface\Resilience;

// Retry with fluent builder
$result = Resilience::retry(fn() => $httpClient->get($url))
    ->times(3)
    ->backoff(200)
    ->run();

// Circuit breaker
$cb = Resilience::circuitBreaker(failureThreshold: 5, cooldownSeconds: 60);
$value = $cb->run(fn() => $database->query($sql));
```

### Retry

- `RetryBuilder` — fluent API: `times(int)`, `backoff(int)`, `run(): RetryResult`
- `RetryExecutor` — executes the retry loop with backoff and jitter
- `RetryOptions` — immutable configuration: attempts, backoffMs, timeoutMs, jitter
- `RetryResult` — outcome: `success`, `result`, `attempts`, `lastException`

### Circuit Breaker

- `CircuitBreaker` — tracks failure counts and state transitions
- `CircuitBreakerState` — enum: `Closed`, `Open`, `HalfOpen`
- States:
  - **Closed**: operations execute normally
  - **Open**: operations rejected immediately after threshold failures
  - **HalfOpen**: one probe allowed after cooldown expires; success closes, failure reopens

### Timeout

- `Timeout` — wraps an operation and checks elapsed time after completion
- Throws `OperationTimedOut` if the operation exceeded the configured limit
- **Honest limitation**: checks elapsed time post-hoc. Does NOT interrupt the running operation.

### Bulkhead

- `Bulkhead` — limits concurrent executions to `maxConcurrent`
- Throws `BulkheadLimitExceeded` when capacity is reached
- Single counter implementation (no partition support)

### Fallback

- `Fallback` — static strategy registry and fallback chain execution
- `FallbackBuilder` — registers fallback strategies by dependency name
- `FallbackStrategyItem` — single strategy registration unit
- `Fallback::execute(array $fallbacks)` — tries each fallback until one succeeds

### Rate Limiter

- `RateLimiter` — delegates to `RedisRateLimiter` for per-key rate limiting
- `RateLimit` — rate limit configuration value object
- `RateLimitDecision` — decision: `allowed`, `limit`, `remaining`, `retryAfter`, `resetSeconds`
- `RateLimitMiddleware` — HTTP middleware wrapper (if present)
- `RedisRateLimiter` — sliding window algorithm with optional Redis backend, in-memory fallback

### Idempotency

- `Idempotency` — static facade: `check()`, `record()`, `replay()`, `generate()`, `fromHeader()`
- `IdempotencyMiddleware` — HTTP middleware for idempotent request handling
- `IdempotencyStore` — interface for storage backends
- `InMemoryIdempotencyStore` — in-memory implementation

### Backoff

- `BackoffSchedule` — exponential backoff calculation

### Dead Letter

- `DeadLetterStore` — interface for permanently failed operation storage
- `InMemoryDeadLetterStore` — in-memory implementation

### Lock

- `Lock` — interface: `acquire()`, `release()`, `isAcquired()`
- `InMemoryLock` — in-memory implementation with TTL and expired lock cleanup

### Backpressure

- `BackpressurePolicy` — signals consumers to slow down based on queue size and load threshold
- `shouldReject()`, `shouldDelay()` decision methods

### Load Shedding

- `LoadShedder` — sheds load for low-priority endpoints when system load exceeds threshold

### Outbox

- `OutboxStore` — interface for transactional outbox pattern
- `InMemoryOutboxStore` — in-memory implementation

### Unified Reliability Flow

- `ExecuteWithReliability` — composes circuit breaker, timeout, retry, and fallback in a single pipeline
  - Execution order: circuit breaker gate → timeout wrapper → retry loop → fallback on exhaustion
  - Fluent API: `withMaxAttempts()`, `withBackoffMs()`, `withTimeoutMs()`, `withCircuitBreaker()`, `withFallback()`

### Flows

- `RetryOperation` — execute with retry policy
- `BreakCircuit` — circuit breaker state transitions
- `ApplyTimeout` — wrap with timeout
- `ApplyBulkhead` — partition-limited execution
- `ApplyFallback` — fallback chain execution
- `CheckRateLimit` — rate limit decision
- `ApplyIdempotency` — idempotent execution
- `RecordDeadLetter` — record permanently failed operation
- `ExecuteWithReliability` — combined reliability pipeline

### Foundation

- `ResilienceException` — base exception (extends `RuntimeException`)
- `BulkheadLimitExceeded` — thrown when bulkhead capacity is reached
- `OperationTimedOut` — thrown when operation exceeds timeout
- `ResilienceLimitExceeded` — thrown when a resilience limit is exceeded
- `ReliabilityResult` — value object: `success`, `result`, `attempts`, `lastException`, `fallbackUsed`, `elapsedMs`

### Configuration

- `ResilienceConfiguration` — immutable configuration with defaults for retry attempts, backoff, circuit breaker
  thresholds, timeout, rate limits, and idempotency toggle

## Internal Flow

### Retry Flow

1. `Resilience::retry($operation)` creates a `RetryBuilder` with the closure
2. `RetryBuilder::times()` and `backoff()` configure `RetryOptions`
3. `RetryBuilder::run()` creates a `RetryExecutor` and calls `execute()`
4. `RetryExecutor` loops up to `attempts`, catching `Throwable`, applying backoff with jitter between attempts
5. Returns `RetryResult` with success/failure, result, attempt count, and last exception

### Circuit Breaker Flow

1. `Resilience::circuitBreaker()` creates a `CircuitBreaker` with threshold and cooldown
2. `CircuitBreaker::run()` first checks `canRun()` against current state
3. If state is `Open`, throws `RuntimeException` immediately
4. Executes the operation; on success calls `recordSuccess()` (resets failures, closes circuit)
5. On failure calls `recordFailure()` (increments counter; opens circuit if threshold reached)
6. `state()` method transitions `Open` to `HalfOpen` when cooldown expires

### Unified Reliability Flow (ExecuteWithReliability)

1. Creates a `CircuitBreaker` with configured threshold and cooldown
2. Circuit breaker gate: if open, throws immediately
3. Inside circuit breaker: enters retry loop with optional timeout wrapping
4. Each retry attempt: if `timeoutMs` is set, wraps operation in `Timeout::run()`
5. On retry exhaustion: calls fallback closure if provided
6. Returns result or throws last exception

### Timeout Flow

1. Records start time with `hrtime(true)`
2. Executes the operation (runs to completion)
3. Calculates elapsed time after operation returns
4. Throws `OperationTimedOut` if elapsed exceeds `timeoutMs`

### Fallback Flow

1. `Fallback::for($dependency)` creates a `FallbackBuilder`
2. `FallbackBuilder::use($fallbackClass)` registers a `FallbackStrategyItem` in the static strategies array
3. `Fallback::execute($fallbacks)` iterates through fallback closures, returning on first success
4. If all fail, throws the last exception (or `RuntimeException` if none)

### Rate Limiter Flow

1. `RateLimiter::attempt()` delegates to `RedisRateLimiter::attempt()`
2. `RedisRateLimiter` uses sliding window algorithm
3. If Redis extension is available and configured, uses Redis sorted sets
4. Otherwise falls back to in-memory array of hit timestamps
5. Returns `RateLimitDecision` with allowance, remaining count, and retry-after

## Dependencies

- **PHP standard library only** for core functionality
- **ext-redis** (optional) — enables Redis-backed rate limiting in `RedisRateLimiter`
- **Observability** (optional) — no built-in metrics emission; consumers can instrument retry/circuit-break events

## Failure Behavior

| Pattern        | Failure Behavior                                                                 |
|----------------|----------------------------------------------------------------------------------|
| Retry          | Exponential backoff with jitter; configurable attempts; returns `RetryResult`    |
| Circuit Breaker| Opens after threshold failures; rejects with `RuntimeException`; half-open retry |
| Timeout        | Post-hoc detection only; throws `OperationTimedOut` after operation completes    |
| Bulkhead       | Rejects immediately with `BulkheadLimitExceeded` when capacity reached           |
| Fallback       | Tries each fallback in order; fails if all exhausted                             |
| Rate Limiter   | Returns `RateLimitDecision` with `allowed = false` and `retryAfter` delay        |
| Backpressure   | Returns rejection/delay signals based on queue size and load threshold           |
| Load Shedding  | Returns `shouldShed = true` for low-priority endpoints under high load           |
| Lock           | Returns `false` on `acquire()` if lock is held; TTL auto-expires                 |

## Runtime Safety

- **Retry is bounded**: maximum attempts are always configured; no infinite retry loops
- **Circuit breaker prevents cascades**: stops calling failing services after threshold is reached
- **Bulkhead isolates consumers**: limits concurrent executions to prevent resource exhaustion
- **Rate limiter prevents abuse**: per-key rate limiting with sliding window
- **Idempotency prevents duplicates**: key-based deduplication for repeated requests
- **Locks have TTL**: in-memory locks auto-expire to prevent deadlocks
- **Fallback has finite chain**: fallback execution stops when chain is exhausted
- **Load shedding protects priority**: low-priority endpoints are shed first under pressure

## Examples

### Basic Retry

```php
$result = Resilience::retry(fn() => $httpClient->post($url, $payload))
    ->times(3)
    ->backoff(200)
    ->run();

if ($result->success) {
    return $result->result;
}

throw $result->lastException;
```

### Circuit Breaker

```php
$breaker = Resilience::circuitBreaker(failureThreshold: 5, cooldownSeconds: 60);

try {
    $data = $breaker->run(fn() => $externalApi->fetch());
} catch (RuntimeException $e) {
    // Circuit is open — use cached data or reject
}
```

### Unified Reliability

```php
$reliability = (new ExecuteWithReliability())
    ->withMaxAttempts(3)
    ->withBackoffMs(100)
    ->withTimeoutMs(5000)
    ->withCircuitBreaker(failureThreshold: 5, cooldownSeconds: 30)
    ->withFallback(fn(Throwable $e) => $cache->get('fallback-data'));

$result = $reliability->execute(fn() => $service->call());
```

### Rate Limiting

```php
$limiter = new RateLimiter();
$decision = $limiter->attempt(key: 'user:123', maxAttempts: 60, decaySeconds: 60);

if (! $decision->allowed) {
    return response(
        status: 429,
        headers: [
            'Retry-After' => $decision->retryAfter,
            'X-RateLimit-Remaining' => $decision->remaining,
        ],
    );
}
```

### Idempotency

```php
$key = Idempotency::fromHeader($request->header('Idempotency-Key'));

if ($key !== null && Idempotency::check($key)) {
    return Idempotency::replay($key);
}

$result = $service->process($request);
Idempotency::record($key, $result);
```

## Known Limits

| Limitation                                      | Reason                                     |
|-------------------------------------------------|--------------------------------------------|
| Timeout is post-hoc only                        | PHP has no thread/process preemption       |
| `Fallback` uses static strategies array         | Process-wide state; not ideal for testing  |
| `RedisRateLimiter` is incomplete                | Redis path exists but no Lua script impl   |
| `Lock` is in-memory only                        | No Redis/ZooKeeper distributed lock impl   |
| `Bulkhead` has no partition support             | Single counter, no named partitions        |
| No retry budget                                 | No global cap on total retries             |
| Circuit breaker is not adaptive                 | Static thresholds, no traffic-aware tuning |
| `DeadLetterStore` is in-memory only             | No persistent storage implementation       |
| `OutboxStore` is in-memory only                 | No persistent outbox implementation        |
| `BackoffSchedule` is minimal                    | Basic exponential only, no custom curves   |

## Current Status

**Status: YELLOW**

All resilience patterns are implemented with test coverage:

- Retry with backoff and jitter: implemented, tested
- Circuit breaker with state transitions: implemented, tested
- Timeout (post-hoc): implemented, tested, honestly documented
- Bulkhead: implemented, tested
- Fallback chain: implemented, tested
- Rate limiter with sliding window: implemented, tested
- Idempotency: implemented, tested
- Backpressure: implemented, tested
- Load shedding: implemented, tested
- Lock (in-memory): implemented, tested
- Dead letter (in-memory): implemented
- Outbox (in-memory): interface defined, in-memory stub
- Unified reliability flow: implemented, tested

Gaps before GREEN:

- Redis rate limiter needs full Lua script implementation
- Distributed lock needs Redis/ZooKeeper driver
- Persistent dead-letter and outbox stores need database/Redis drivers
- This documentation file was missing (this file)
- Bulkhead needs partition-level isolation
