# DeadLetter Ownership Closure — V5 Dogfooding Closure Pass 01

**Date:** 2026-05-10
**Status:** YELLOW — ownership clarified, but Queue dead-letter state is inline private array, not explicit store
**Gate:** `php tooling/governance/check-component-adoption.php` — PASS

## Finding

Two DeadLetter-related implementations existed:

### Resilience/DeadLetter (canonical, kept)

- `DeadLetterStore.php` — Interface for generic dead letter storage
- `InMemoryDeadLetterStore.php` — In-memory implementation
- `RecordDeadLetter.php` — Flow to record dead letters
- **Ownership:** Generic resilience pattern for any component that needs dead letter storage

### Queue/DeadLetter (empty directory, removed)

- `components/Operations/Queue/System/Capabilities/DeadLetter/` — **EMPTY DIRECTORY**
- No files, no implementation
- Dead letter behavior lives inline in `MemoryQueue.php` as `private array $deadLetters = []`
- **Decision:** Queue's inline dead letter tracking is queue-specific behavior, not a duplicate capability folder

## Action

- Removed empty `components/Operations/Queue/System/Capabilities/DeadLetter/` directory
- No code changes needed — Queue's inline dead letter tracking remains functional
- Resilience's DeadLetterStore remains as the generic reusable pattern

## Why YELLOW, Not GREEN

Queue's dead letter tracking in `MemoryQueue.php` uses `private array $deadLetters = []` — inline hidden state, not an
explicit store capability. The methods (`deadLetters()`, `deadLetterCount()`, `clearDeadLetters()`, `retry()`,
`clearAll()`) work correctly and tests pass, but the storage model is implicit private array rather than an explicit
reset-safe store. This meets the "ownership is clarified" criterion but not the "explicit, reset-safe
storage/capability" GREEN criterion.

## Validation

- `php tooling/governance/check-component-adoption.php` — PASS
- No empty DeadLetter directories remain
- Queue's MemoryQueue dead letter methods remain functional
- Tests prove record/list/retry/flush behavior
