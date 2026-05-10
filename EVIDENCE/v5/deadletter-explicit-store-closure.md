# DeadLetter Explicit Store Closure — V5 Self-Healing Mega Pass 02

**Date:** 2026-05-10
**Status:** YELLOW

## Decision

Queue failed-job/dead-letter behavior is now explicit and reset-safe via a dedicated `FailedJobsStore` interface.

**Why YELLOW (not GREEN):**

- Dead-letter state is now backed by an explicit `FailedJobsStore` interface — GOOD
- `InMemoryFailedJobsStore` and `PdoFailedJobsStore` implementations exist — GOOD
- `Queue::reset()` clears static state — GOOD
- BUT: the `Queue` static facade still owns canonical in-memory runtime state (`$queues`)
- In long-lived workers, if `reset()` is not called between requests, queue state will leak
- Risk is documented and mitigated by `reset()` method, but static canonical state remains

## Next Action

## Changes Made

### 1. FailedJobsStore Interface

**Created:** `components/Operations/Queue/System/Capabilities/Queue/FailedJobs/FailedJobsStore.php`

Defines the explicit contract for queue failed-job storage:

- `record()` — record a failed job
- `list()` — list failed jobs by queue or all
- `count()` — count failed jobs
- `clear()` — clear failed jobs (reset-safe)

### 2. InMemoryFailedJobsStore

**Created:** `components/Operations/Queue/System/Capabilities/Queue/FailedJobs/InMemoryFailedJobsStore.php`

Instance-scoped in-memory implementation:

- Does not leak between tests (fresh instances)
- Reset-safe via `clear()`
- Preserves full payload and metadata

### 3. Queue.php Static Facade Updated

**Modified:** `components/Operations/Queue/System/Capabilities/Queue/Queue.php`

- Removed `private static array $deadLetters`
- Added `private static ?FailedJobsStore $failedJobsStore`
- Added `useFailedJobsStore()` for injecting custom stores
- `deadLetters()`, `deadLetterCount()`, `clearDeadLetters()` delegate to `FailedJobsStore`
- `release()` records to `FailedJobsStore` on max attempts
- `reset()` clears the store and nullifies it

### 4. MemoryQueue Updated

**Modified:** `components/Operations/Queue/System/Capabilities/Queue/MemoryQueue/MemoryQueue.php`

- Removed `private array $deadLetters`
- Added `private FailedJobsStore $failedJobsStore` (instance property)
- Constructor accepts optional `FailedJobsStore` (defaults to `InMemoryFailedJobsStore`)
- `retry()`, `deadLetters()`, `deadLetterCount()`, `clearDeadLetters()`, `clearAll()` delegate to store

### 5. Tests

**Created:** `tests/Unit/Components/Operations/Queue/FailedJobsStoreTest.php` — 14 tests, 34 assertions

Tests:

- Record and list failed jobs
- List all queues when empty string
- Count failed jobs
- Clear specific queue
- Clear all queues
- Store is instance-scoped (no leak between instances)
- Preserves payload and metadata

## Validation

| Check                                                                   | Result                           |
|-------------------------------------------------------------------------|----------------------------------|
| `vendor/bin/phpunit --filter "Queue"`                                   | 114 tests, 287 assertions — PASS |
| `vendor/bin/phpstan analyse Queue/FailedJobs Queue.php MemoryQueue.php` | 0 errors                         |

## Why YELLOW

- Dead-letter state is no longer inline private/static array — FIXED
- Explicit `FailedJobsStore` interface defines the contract — GOOD
- `InMemoryFailedJobsStore` provides reset-safe in-memory behavior — GOOD
- `PdoFailedJobsStore` provides SQL-backed persistent behavior — GOOD
- Both `Queue` static facade and `MemoryQueue` instance delegate to the store — GOOD
- Store is instance-scoped — no leak between tests — GOOD
- `reset()` / `clearAll()` clear the store explicitly — GOOD
- SQL identifier validation prevents table name injection — GOOD
- Custom stores can be injected — GOOD
- **YELLOW because:** `Queue::$queues` static array still owns canonical in-memory queue state; `reset()` exists but
  must be called by worker runtime; risk is documented and mitigated
