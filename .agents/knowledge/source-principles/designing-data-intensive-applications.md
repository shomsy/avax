# Source Principles: Designing Data-Intensive Applications

## Role in Governance

Use this source family for persistence, cache, queue, event, stream, token/session state, retry, consistency, and reconciliation work.

## Reliability

The system must keep its promises under partial failure. State transitions need failure guarantees, not only happy paths.

## Scalability

Scalability claims need load shape, bottleneck, bounded resource use, and proof. Do not add queues or caches as decoration.

## Maintainability

Data workflows must be explainable years later: source of truth, derived state, schemas, and repair paths must be documented.

## System of Record

Every important fact has one authoritative owner. Review asks: what is authoritative, who writes it, and how conflict is resolved.

## Derived Data

Derived data must be rebuildable or reconciled. If it cannot be rebuilt, it is effectively a system of record and must be governed as one.

## Cache Correctness

Cache evidence must state key shape, owner, invalidation, TTL, stale-read risk, memory bound, worker lifecycle, and fallback.

## Idempotency

Commands that may retry must define idempotency key, duplicate behavior, and safe completion semantics.

## Retry Semantics

Retry must classify retryable vs fatal failure, backoff, maximum attempts, and side-effect safety.

## Ordering

If order matters, document ordering source, partitioning, reordering behavior, and stale event handling.

## Duplicate Handling

Messages, jobs, webhooks, and events must tolerate duplicates or state why duplicates cannot occur.

## Exactly-Once Skepticism

Do not claim exactly-once delivery without proof. Prefer at-least-once plus idempotency and reconciliation.

## Transactions and Isolation

State isolation must match invariant risk. Review asks which invariant can be violated under concurrent writes.

## Consistency Models

Document whether reads are strong, eventual, monotonic, or best-effort. User-visible staleness must be intentional.

## Replication Lag

If replicas exist, define stale read behavior, read routing, and repair path.

## Read-Your-Writes

User flows that expect immediate visibility must document how read-your-writes is achieved.

## Schema Evolution

Schema changes must be backward-compatible or have migration/rollout evidence.

## Backpressure

Queues and streams need overload behavior, rejection policy, retry pressure, and observability.

## Queues and Streams

Async boundaries require ownership, message schema, poison-message handling, retry, ordering, idempotency, and dead-letter behavior.

## Outbox/Inbox Pattern

Use outbox/inbox when cross-boundary state and message publication must be reconciled. Do not fake atomicity across systems.

## Reconciliation

Every derived or asynchronous state must have a way to detect and repair drift.

## Observability for Data Workflows

Record correlation ID, state transition, retry count, failure category, lag, and reconciliation outcome.

## Failure Mode Matrix

For each data workflow, list partial write, duplicate delivery, stale read, lost message, poison message, timeout, retry exhaustion, and manual recovery.

## Required Evidence

- scenario input for state mutation
- failure semantics
- cache design when caching
- test proof for retry/duplicate/stale behavior

## Required Tests

Positive mutation, invalid mutation, duplicate, retry, stale-read, rollback/recovery, and reconciliation tests where applicable.

## Checker Mapping

Current enforcement is semi-automated through scenario, coupling, architecture fitness, and test-quality gates.

## Severity Rules

Data corruption risk is BLOCKER. Unclear system of record is HIGH. Cache without lifecycle is HIGH.

## Stop Conditions

Stop when authoritative state, retry behavior, duplicate handling, or reconciliation cannot be explained.

