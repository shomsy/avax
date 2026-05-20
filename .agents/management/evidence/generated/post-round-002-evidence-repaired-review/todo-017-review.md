# Review — TODO-017 Filesystem Boundaries

**Branch:** cleanup/todo-017-filesystem-boundaries
**HEAD:** 5889deaf7
**Work commit:** 50a0d9ff2
**Evidence repair commit:** 5889deaf7

---

## Scope Verification

| Check | Result |
|---|---|
| Branch is not main | PASS |
| Only scoped files touched | PASS — 4 production files + evidence |
| No unrelated cleanup | PASS |
| No public API drift | PASS — Filesystem injection is additive |
| No forbidden files | PASS |

---

## Code Review

### FileSessionStore::gc() — PASS

- `filemtime($file)` → `$this->filesystem->modificationTime($file)`
- Class already had Filesystem injected and used for 10+ other operations
- Single line change, no behavioral change

### ReadCompiledFailurePolicies — PASS

- Injected `Filesystem $filesystem` into constructor
- `file_exists()` → `$this->filesystem->exists()`
- `file_get_contents()` → `$this->filesystem->read()`
- Removes manual `if ($json === false)` check — `read()` throws on failure (correct behavior)

### WriteCompiledFailurePolicies — PASS

- Injected `Filesystem $filesystem` into constructor
- `is_dir()` → `$this->filesystem->isDirectory()`
- `mkdir()` → `$this->filesystem->createDirectory()`
- `file_put_contents(..., LOCK_EX)` → `$this->filesystem->write()` (LOCK_EX is internal to WriteFile flow)

### BuildDispatchConfiguredRoute — PASS (with YELLOW)

- Added `public function __construct(private Filesystem|null $filesystem = null)`
- `is_file()` → `$filesystem->isFile()` with fallback `new Filesystem()`
- **YELLOW:** Default parameter `new Filesystem()` is fallback construction outside approved Configuration zone, but this is a builder class and the fallback is the same pattern used elsewhere for optional dependencies

**Verification:** `new BuildDispatchConfiguredRoute()` is called in ApplicationBuilder.php with no arguments — backward compatible.

---

## Test Review

No new tests added. Existing tests cover the affected behavior:
- FileSession tests: session storage lifecycle including GC
- FailureBoundary tests: compiled failure policy read/write
- 136 focused tests pass GREEN

Tests prove behavior preservation through replacement.

---

## Evidence Review

| File | Complete | Truthful |
|---|---|---|
| context-loaded.md | YES | YES |
| implementation-summary.md | YES | YES — matches diff |
| validation-output.md | YES | YES — verified by rerun |
| governance-review.md | YES | YES — accepted exceptions documented |
| test-proof.md | YES | YES |
| final-decision.md | YES | YES — notes accepted exceptions |

---

## Validation Rerun

`vendor/bin/phpunit --filter "FileSession|FailureBoundary" --no-coverage` → **136 tests, 291 assertions, GREEN**

---

## Accepted YELLOW

| Item | Severity | Notes |
|---|---|---|
| BuildDispatchConfiguredRoute fallback `new Filesystem()` | LOW | Optional dependency with fallback; same pattern used elsewhere |
| Stream wrapper ops accepted | ACCEPTED_EXCEPTION | php://input, php://temp not persistent filesystem I/O |
| PSR-7 upload idiom accepted | ACCEPTED_EXCEPTION | move_uploaded_file is PSR-7 standard pattern |
| Path parsing accepted | ACCEPTED_EXCEPTION | dirname/basename are string ops, not filesystem I/O |

---

## Decision

**MERGE_READY_WITH_YELLOW**

No blockers. Filesystem boundary routing is correct and consistent. Accepted exceptions are documented and justified. BuildDispatchConfiguredRoute fallback construction is LOW risk.
