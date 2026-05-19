# V5 Dogfooding Closure Pass 01 — Final Hardening Report

**Date:** 2026-05-10
**Pass:** V5 Dogfooding Closure Pass 01

---

## 1. DeadLetter — Status: YELLOW

### Decision

Resilience/DeadLetter owns the generic dead letter store pattern (interface + InMemoryDeadLetterStore + RecordDeadLetter
flow). Queue/DeadLetter empty directory was removed.

### Evidence

- `EVIDENCE/v5/deadletter-ownership-closure.md`
- `php tooling/governance/check-component-adoption.php` — PASS (no duplicate capability folders)

### Why YELLOW, Not GREEN

Queue's dead letter tracking in `MemoryQueue.php` uses a **hidden static mutable state** (
`private array $deadLetters = []`). This is operational dead-letter state that:

- Is not a proper capability or store
- Has no explicit reset-safe storage interface
- Relies on instance-level array (not static, but inline hidden state)

The Queue dead-letter methods (`deadLetters()`, `deadLetterCount()`, `clearDeadLetters()`, `retry()`) work correctly and
tests pass, but the storage is inline private array, not an explicit store capability. This is acceptable for an
in-memory queue broker but does not meet the GREEN criteria of "explicit, reset-safe storage/capability."

### Remaining Risk

- MemoryQueue dead letter state is inline private array, not a formal store
- No worker/CLI paths use an explicit dead letter store for queues
- Tests prove record/list/retry/flush behavior (PASS) but storage model is implicit

### What Would Make It GREEN

- Extract Queue dead letter tracking to explicit capability (e.g., `QueueDeadLetterStore` interface +
  `InMemoryQueueDeadLetterStore`)
- Or: Document that MemoryQueue's inline dead letter array is acceptable for in-memory-only queue broker (not worker/CLI
  paths)

---

## 2. Redaction — Status: GREEN

### Decision

Security/Redaction is the single canonical owner. All duplicate implementations removed.

### Evidence

- `EVIDENCE/v5/redaction-ownership-closure.md`
- `php tooling/governance/check-component-adoption.php` — PASS (no Redaction duplicates)

### Proof

- Security/Redaction owns 9 files (PublicSurface, 4 Capabilities, 3 Flows, 1 Configuration)
- Deleted: `Logging/Redaction/SecretRedactor.php` (6684 bytes), `Observability/Redaction/RedactSensitiveData.php` (1171
  bytes)
- Deleted: empty `Logging/Redaction/` and `Observability/Redaction/` directories
- Deleted: `Observability/Flows/RedactSensitiveData/` Flow wrapper
- 7 consumers migrated: ErrorLogger, WriteErrorLog, FileAuditWriter, FileLogWriter, FileMetricWriter, FileTraceWriter,
  RecordObservability
- RotatingFileWriter and StructuredLogRecord already migrated (from prior session)
- `vendor/bin/phpunit --filter "redact|Redact"` — 20 tests, 43 assertions, PASS

### Redaction Verification (runtime proof)

```
password:       PASS (redacted to ***)
token:          PASS (redacted to ***)
session_id:     PASS (redacted to ***)
authorization:  PASS (redacted to ***)
nested:         PASS (nested password redacted to ***)
preserved:      PASS (non-sensitive keys preserved)
```

### No remaining references to `SecretRedactor` or `RedactSensitiveData` in `components/` or `tests/`

---

## 3. Raw File Operations — Status: YELLOW

### Gate Status Before/After

| Gate                            | Before | After |                         Change |
|---------------------------------|-------:|------:|-------------------------------:|
| `check-raw-file-operations.php` |    241 |   235 | -6 (3 priority files migrated) |

### Migrated (no longer flagged)

| File                    | Before                                                    | After                                        |
|-------------------------|-----------------------------------------------------------|----------------------------------------------|
| BladeTemplateEngine.php | `unlink()`                                                | `Filesystem::delete()`                       |
| DatabaseExporter.php    | `mkdir()` + `file_put_contents()`                         | `Filesystem::write()`                        |
| MigrationGenerator.php  | `mkdir()` + `file_put_contents()` + `file_get_contents()` | `Filesystem::write()` + `Filesystem::read()` |

### Remaining Violations: 235

**Not permanently allowed (classified as NEEDS MIGRATION or MUST MIGRATE):**

- Container compilation/cache: 72 violations (RegistrationMetadata, CreateContainerConfig, CompileContainer, etc.)
- Observability file writers: 16 violations (FileAuditWriter, FileLogWriter, FileMetricWriter, FileTraceWriter)
- Route cache: 5 violations (CacheRouteTable, LoadCachedRoutes)
- Code generator: 3 violations (CodeGenerator.php)
- Other production paths: ~10+ (MigrateCommand, FileBackedHmacKeyRingCodec, etc.)

**Allowed:**

- Filesystem internals: ~30 violations (FileCacheStore, FileSessionStore, StoreObjectsOnLocalFilesystem)
- PreCommit/tooling: ~43 violations
- Container tooling scripts: 20 violations
- Non-filesystem I/O: ~3 violations (php://input, php://temp, method name `rename`)

### Why YELLOW, Not GREEN

The gate still reports 235 violations. The 3 priority production runtime files were migrated successfully (
BladeTemplateEngine, DatabaseExporter, MigrationGenerator no longer appear in gate output), but ~100+ remaining
violations are in production runtime paths (container compilation, observability writers, route cache, code generation)
that should migrate to Filesystem but have not yet.

---

## 4. Validation Results

| Check                                                      | Result                                 |
|------------------------------------------------------------|----------------------------------------|
| `composer validate --no-check-publish`                     | PASS                                   |
| `composer dump-autoload -o`                                | PASS (9099 classes)                    |
| `vendor/bin/phpunit --no-coverage`                         | **7447 tests, 21671 assertions, PASS** |
| `vendor/bin/phpstan analyse framework components tests`    | **0 errors**                           |
| `php tooling/security/check-security-blockers.php`         | PASS                                   |
| `php tooling/governance/check-component-adoption.php`      | PASS                                   |
| `php tooling/refactor/check-component-suite-structure.php` | PASS                                   |
| `php tooling/refactor/check-duplicate-owners.php`          | PASS                                   |
| `php tooling/refactor/check-namespace-drift.php`           | PASS                                   |
| `php tooling/refactor/check-public-surface.php`            | PASS                                   |
| `php tooling/refactor/check-runtime-leaks.php`             | PASS                                   |

---

## 5. Overall Status: YELLOW

| Area         | Status     | Reason                                                                                                                |
|--------------|------------|-----------------------------------------------------------------------------------------------------------------------|
| Redaction    | **GREEN**  | One canonical owner, duplicates removed, all consumers migrated, tests prove redaction                                |
| DeadLetter   | **YELLOW** | Ownership clarified, empty dir removed, but Queue dead-letter state is still inline private array, not explicit store |
| Raw File Ops | **YELLOW** | 3 priority files migrated, gate reduced 241→235, but ~100+ production runtime violations remain unmigrated            |

### Files Changed

- **Modified:** 14 production/test files
- **Deleted:** 3 files (SecretRedactor.php, RedactSensitiveData.php, Observability RedactSensitiveData flow)
- **Deleted:** 3 empty directories (Logging/Redaction/, Observability/Redaction/, Queue/DeadLetter/)
- **Created:** 5 evidence documents

### Next Allowed Actions

1. Queue dead-letter: extract to explicit store capability OR document inline array as acceptable for in-memory broker
2. Raw file ops: migrate container compilation (72 violations), observability writers (16 violations), route cache (5
   violations)
