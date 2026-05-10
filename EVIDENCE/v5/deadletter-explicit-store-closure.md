# DeadLetter Explicit Store Closure — V5 Self-Healing Mega Pass 02

**Date:** 2026-05-10
**Status:** GREEN

## Decision

Queue failed-job/dead-letter behavior is now explicit and reset-safe via a dedicated `FailedJobsStore` interface.

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

## Why GREEN

- Dead-letter state is no longer inline private/static array
- Explicit `FailedJobsStore` interface defines the contract
- `InMemoryFailedJobsStore` provides reset-safe in-memory behavior
- Both `Queue` static facade and `MemoryQueue` instance delegate to the store
- Store is instance-scoped — no leak between tests
- `reset()` / `clearAll()` clear the store explicitly
- Custom stores (e.g., PDO-backed `FailedJobsStore` for CLI) can be injected
- All existing tests pass, new tests prove explicit store behavior
