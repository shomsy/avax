# How to Govern Data Correctness (DDIA)

## Purpose

This document operationalizes Designing Data-Intensive Applications (DDIA) principles within the framework's governance. It ensures data-sensitive decisions are deliberate, evidence-backed, and safe for production.

## Role in the Governance

The framework handles data through cache, queues, events, sessions, tokens, and persistence. Data correctness failures are among the hardest to detect and most damaging to recover from. Every data-sensitive change must be governed.

## System of Record

Every piece of data must have exactly one authoritative source. If multiple components claim authority, data conflicts are inevitable.

- Document which component is the system of record
- All other copies are derived state

## Derived Data

Derived data (caches, materialized views, indexes, computed fields) must be:
- Documented as derived
- Refreshable from the system of record
- Tolerant of staleness or explicitly consistent

## Cache Correctness

- Cache invalidation must be explicit and documented
- TTL-based caches must document stale read tolerance
- Write-through vs write-behind vs cache-aside must be chosen deliberately
- Cache key collisions must be prevented

## Idempotency

- Operations that may be retried must be idempotent
- Idempotency keys or deduplication must be documented
- State changes must be safe for replay

## Retry Semantics

- Retries must not cause duplicate side effects
- Exponential backoff with jitter is preferred
- Maximum retry limits must be documented
- Retry behavior must be tested

## Duplicate Handling

- Duplicate messages/events/requests must be handled safely
- Deduplication strategy must be documented
- At-least-once delivery requires idempotent consumers

## Ordering

- If ordering matters, document how ordering is preserved
- If ordering does not matter, document why
- Out-of-order processing must be safe or rejected

## Transaction Boundary

- Transaction isolation level must be chosen deliberately
- Transaction scope must be minimized
- Long transactions in long-lived workers are dangerous

## Isolation and Consistency

- Document expected consistency model (strong, eventual, causal)
- Document read isolation requirements
- Document write conflict resolution

## Eventual Consistency

- Document convergence guarantee
- Document maximum staleness window
- Document reconciliation mechanism

## Replication Lag / Stale Reads

- Document acceptable stale read window
- Document read-your-writes requirements
- Document fallback behavior when stale read is detected

## Read-Your-Writes

- If a user must see their own writes immediately, document how this is guaranteed
- Session affinity, primary reads, or synchronous replication

## Schema Evolution

- Backward compatibility requirements
- Migration strategy
- Rollback plan for schema changes

## Backpressure

- Document what happens when a consumer is slower than a producer
- Queue depth limits, circuit breakers, or load shedding

## Queue/Stream Semantics

- At-most-once, at-least-once, or exactly-once
- Document consumer group behavior
- Document dead letter queue policy

## Outbox/Inbox

- If using outbox pattern for reliable event publishing, document it
- Inbox pattern for reliable event consumption

## Reconciliation

- How is data inconsistency detected?
- How is it corrected?
- Automated vs manual reconciliation

## Observability

- Metrics for data integrity (lag, error rate, duplicate count)
- Alerting for data anomalies
- Audit logging for sensitive data changes

## Data Failure Matrix

| Failure | Detection | Impact | Recovery |
|---------|-----------|--------|----------|
| Cache poisoning | | | |
| Stale read | | | |
| Lost write | | | |
| Duplicate processing | | | |
| Schema mismatch | | | |

## Required Tests

- Idempotency proof tests
- Retry safety tests
- Cache invalidation tests
- Transaction isolation tests
- Failure/recovery tests

## Evidence Requirements

Changed production files with data-sensitive signals require `data-correctness.md` evidence.

Template: `.agents/templates/evidence/data-correctness.md`

## Severity Rules

| Finding | Severity |
|---------|----------|
| Missing idempotency for retryable operation | HIGH |
| Cache without documented invalidation | HIGH |
| Transaction boundary unclear or hidden | HIGH |
| Stale read without documented tolerance | MEDIUM |
| Missing failure matrix | MEDIUM |
| Missing reconciliation strategy | MEDIUM |

## Stop Conditions

- BLOCKER: Data corruption possible under normal operation
- BLOCKER: Lost writes under concurrent access
- HIGH: Missing idempotency for retryable path
- HIGH: Cache poisoning possible
