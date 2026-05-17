# V3 Runtime Integration Proof Plan

Date: 2026-05-08
Stage: V3-05
Status: Plan

## Goal

Prove that `labs/SystemDesignKit` can reason about existing V2 runtime components without duplicating their ownership.

V3 remains **design-time modeling**. V2 remains **runtime execution**.

The integration proof shows:

1. V3 models can describe V2 runtime behavior
2. V3 risk detection matches realistic V2 configurations
3. V3 concepts do not duplicate V2 runtime ownership
4. V3 + V2 together form a complete design-time + runtime story

## Ownership Boundary

| Concept                  | V3 (labs/SystemDesignKit)                                             | V2 (components/Operations/*)                                                           |
|--------------------------|-----------------------------------------------------------------------|----------------------------------------------------------------------------------------|
| Message type taxonomy    | `MessagingModel`, `MessageType` — design-time vocabulary              | `MessageBus`, `CommandBus`, `EventBus`, `QueryBus` — runtime dispatch                  |
| Messaging risk detection | `DetectMessagingRisk` — analyzes config for missing patterns          | `BusMiddleware`, `BusTransactionMiddleware` — runtime enforcement                      |
| Retry policy modeling    | `RetryPolicy` — design-time VO (max retries, backoff, delays)         | `RetryBuilder`, `RetryExecutor`, `RetryOptions`, `TaskRetryPolicy` — runtime execution |
| Queue capacity modeling  | `QueueDepth`, `ConsumerThroughput` — design-time VOs                  | `TaskQueue`, `TaskRunner` — runtime queue/execution                                    |
| Outbox modeling          | `Outbox` — design-time VO (enabled, poll interval, batch size)        | `OutboxStore` interface, `InMemoryOutboxStore` — runtime storage                       |
| Dead letter modeling     | `DeadLetterQueue` — design-time VO (max retries, reprocessing window) | `DeadLetterStore` interface, `InMemoryDeadLetterStore` — runtime storage               |
| Idempotency modeling     | `Message.idempotent` flag in `MessagingModel`                         | `Idempotency`, `IdempotencyStore` — runtime check/record/replay                        |
| Circuit breaker modeling | Not yet modeled (future V3)                                           | `CircuitBreaker`, `CircuitBreakerState` — runtime state machine                        |
| Backoff modeling         | `RetryPolicy.backoffStrategy` — design-time string                    | `BackoffSchedule` — runtime schedule computation                                       |

## Integration Test Plan

### Test 1: V3 MessagingModel describes V2 MessageBus configuration

**File:** `tests/SystemDesignKit/Integration/V3MessagingModelDescribesV2MessageBusTest.php`

**Proof:** Build a `MessagingModel` from a config that mirrors V2 MessageBus's actual behavior:

- V2 `CommandBus` handles one command -> one handler -> V3 models this as `MessageType::Command` with `idempotent=true`
- V2 `EventBus` handles one event -> many handlers -> V3 models this as `MessageType::Event` with `isFireAndForget()`
- V2 `QueryBus` handles one query -> one handler -> V3 models this as `MessageType::Message` or `Command`
- V2 `MessageBusConfiguration` has `defaultRetryAttempts` -> V3 `RetryPolicy.maxRetries`
- V2 `BusTransactionMiddleware`, `BusValidationMiddleware`, `BusLoggingMiddleware` -> V3 risk detection flags missing
  outbox/inbox

**Expected:** V3 `MessagingModel` can be constructed from a config that accurately describes V2 MessageBus behavior. V3
`DetectMessagingRisk` identifies risks in a minimal V2 MessageBus config (no outbox, no inbox, no DLQ by default).

### Test 2: V3 RetryPolicy aligns with V2 TaskRetryPolicy and Resilience RetryOptions

**File:** `tests/SystemDesignKit/Integration/V3RetryPolicyAlignsV2RetryTest.php`

**Proof:**

- V3 `RetryPolicy(3, 'exponential', 100, 30000)` should produce the same worst-case as V2
  `TaskRetryPolicy(3, 1000, true)` with bounded delays
- V3 `RetryPolicy` is a design-time model; V2 `TaskRetryPolicy.delays()` produces actual delay arrays
- V3 `RetryPolicy` maps to V2 `RetryOptions(attempts, backoffMs, timeoutMs)` for attempt count and backoff

**Expected:** V3 and V2 retry concepts align on parameters (max attempts, backoff strategy) but differ in purpose (V3
models, V2 executes).

### Test 3: V3 QueueDepth models V2 TaskQueue capacity

**File:** `tests/SystemDesignKit/Integration/V3QueueDepthModelsV2TaskQueueTest.php`

**Proof:**

- V3 `QueueDepth(maxDepth, currentDepth)` models capacity utilization
- V2 `TaskQueue` has `push()`, `pop()`, `size()`, `isEmpty()` — runtime queue behavior
- V3 `ConsumerThroughput(perSecond)` models required consumer throughput
- V2 `TaskRunner` executes tasks with history tracking

**Expected:** V3 can model the capacity characteristics of a V2 TaskQueue-based system.

### Test 4: V3 Outbox/DLQ model V2 Resilience patterns

**File:** `tests/SystemDesignKit/Integration/V3OutboxDlqModelsV2ResilienceTest.php`

**Proof:**

- V3 `Outbox` VO describes outbox configuration; V2 `OutboxStore` interface provides runtime storage
- V3 `DeadLetterQueue` VO describes DLQ configuration; V2 `DeadLetterStore` interface provides runtime storage
- V3 `DetectMessagingRisk` flags missing outbox/inbox/DLQ; V2 provides the runtime implementation

**Expected:** V3 risk detection correctly identifies when V2 Resilience patterns are missing from a messaging
configuration.

### Test 5: No runtime duplication

**File:** `tests/SystemDesignKit/Integration/V3NoRuntimeDuplicationTest.php`

**Proof:**

- V3 classes are all `final readonly class` (value objects) or `final class` with pure computation (flows)
- V3 has no I/O, no state mutation, no runtime execution
- V2 classes have mutable state, I/O interfaces, runtime execution

**Expected:** Static analysis of V3 class shapes confirms design-time-only behavior.

## Success Criteria

1. All 5 integration test files pass
2. PHPStan clean on new test files
3. No new PHP classes added to `labs/SystemDesignKit/System/Capabilities/`
4. No new runtime behavior added to V3
5. README updated with integration proof status
6. CURRENT_TRUTH updated with V3-05 status

## Non-Goals

- Do not promote `labs/SystemDesignKit` to `components/SystemDesign/`
- Do not add new V3 capabilities beyond the existing Capacity, Consistency, Messaging
- Do not modify V2 component code
- Do not create V3 runtime execution code
