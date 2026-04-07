# Runtime Hardening

This document hardens runtime behavior without changing canonical architecture
boundaries.

## Determinism Contract

- same input plus same starting state must produce the same canonical result
- replay from a known checkpoint must produce the same result
- the system must not infer canonical state from incidental transport or UI artifacts
- request-local or interaction-local state may be temporary, but durable truth
  must live in an owned external or canonical state boundary

## Execution Phases

1. resolve input payload
2. validate preconditions and allowed operations
3. restore or allocate a known state boundary
4. apply canonical mutations
5. project outputs
6. emit diagnostic events and final status

## State Ownership Contract

- runtime memory is disposable unless the local architecture explicitly defines
  a durable state owner
- session continuity, business records, queues, and other durable truth must
  survive process restart, worker replacement, or page reconnect where that is
  part of the product contract
- do not let caches, browser state, or in-process registries become accidental
  sources of canonical truth
- if a runtime keeps checkpoints, snapshots, or compiled artifacts, the source
  of truth and regeneration path must remain explicit
- if a process must be replaced, another process should be able to reconstruct
  the owned state without tribal knowledge

## Async And Background Work

- move expensive or slow work out of the hot user path when the product contract
  allows it
- producer ownership, consumer ownership, acknowledgement, retry limit, and
  quarantine or dead-letter behavior must be explicit for queue-backed work
- retries are valid only for operations whose safety and idempotency posture are
  understood
- backpressure, queue depth, worker lag, or equivalent saturation signals must
  be visible for serious async systems
- background jobs must fail into a diagnosable lane, not disappear into silent
  retry loops

## Lifecycle Ownership

- every listener, timer, observer, subscription, reactive effect, or spawned
  background handle must belong to a lifecycle owner
- lifecycle teardown must clean those resources deterministically
- detached UI slices, stopped workers, or ended scopes must not keep mutating
  shared state accidentally
- if a bind or registration exists, the cleanup path must also exist
- re-entry after teardown must not depend on stale closures, stale DOM nodes, or
  stale in-memory handles

## Runtime Isolation And Hardening

- fail closed when mandatory configuration, secrets, or policy inputs are
  missing
- when containerized or sandboxed, prefer non-root identities, minimal writable
  paths, explicit read-only roots, capability drop, and blocked privilege
  escalation where the platform supports it
- writable scratch areas must be narrow and intentional, not a blanket escape
  hatch
- do not rely on mutable in-place runtime state for correctness; rebuild and
  replace disposable runtimes when the platform model allows it
- promoted or production-like runtimes should be validated against the real
  hardening contract, not only against source intent

## Undo and Restore

- use checkpoints plus inverse or compensating actions when feasible
- create checkpoints at stable boundaries
- derive inverse behavior from pre-state when feasible
- replay from a known checkpoint remains the safe fallback

## Reconnect Strategy

- restore nearest valid checkpoint
- replay deterministic deltas to the active point
- only then resume external effects or presentation layers

## Recovery And Hard Stops

- if state consistency, queue ownership, or runtime isolation is ambiguous, stop
  mutation before continuing
- recovery decisions should be GO or NO-GO based on evidence, not operator
  guesswork
- rollback, compensation, or replay paths must be explicit before claiming
  production readiness
- if a degraded mode exists, document what correctness is preserved and what is
  intentionally unavailable
- recovery proof should validate the real runtime or data path, not only a unit
  test approximation

## Performance and Safety

- timeouts, retries, and degraded modes must be explicit
- long-running work must be stoppable or recoverable
- non-functional polish must not degrade correctness or recovery
- uncontrolled timeout growth, retry storms, queue runaway, or UI ghost updates
  are runtime defects, not acceptable noise

## Observability

Runtime should emit events for:

- input accepted or rejected
- state transition start, finish, and failure
- checkpoint create and restore
- rollback or compensation paths
- queue publish, consume, retry, and quarantine when async work exists
- lifecycle attach and teardown failures when owned runtime resources exist
