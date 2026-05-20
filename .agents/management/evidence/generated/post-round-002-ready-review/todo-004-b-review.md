# TODO-004 Batch B Container Migration Review

Branch: `security/todo-004-batch-b-container-migration`
HEAD: `1e7145e0e`
Reviewer: Qoder (review-only)
Date: 2026-05-20

## Scope

Container ProviderRegistry + Migration/Seeder dynamic class-loading hardening.

## Code Review

### ProviderRegistry.php — FAIL-CLOSED: PASS

**Before:** `new $providerClass($this->container)` — arbitrary class instantiation from caller-controlled string.
**After:** Checks `class_exists($providerClass)` AND `is_subclass_of($providerClass, BaseRegisterDependency::class)` before construction. Throws RuntimeException if check fails.

**Findings:**
- **PASS** — BaseRegisterDependency is the correct base class for service providers.
- **PASS** — Fail-closed: exception thrown before construction.
- **PASS** — No public API change.

### SeederCommand.php — FAIL-CLOSED: PASS

**Before:** `require_once $file; new $className()` — class names derived from filesystem scan.
**After:** Checks `class_exists($className)` AND `is_subclass_of($className, Seeder::class)` before construction. Throws RuntimeException if check fails.

**Findings:**
- **PASS** — Filesystem-derived class names are now validated against Seeder type.
- **PASS** — Fail-closed: exception prevents anonymous class instantiation.
- **INFO** — The `require_once` still executes before the check. If the file has side effects, those run. This is a minor concern — the file must be `require_once`'d to define the class, so the guard must come after. The security boundary is the subclass check, which is correct.

### Migrations.php (seed method) — FAIL-CLOSED: PASS

**Before:** `is_string($seeder) ? new $seeder() : $seeder` — then type check after construction.
**After:** Checks `class_exists($seeder)` AND `is_subclass_of($seeder, Seeder::class)` BEFORE construction. Throws InvalidArgumentException.

**Findings:**
- **PASS** — Check-before-construct pattern (fail-closed at validation).
- **PASS** — Restructured control flow is cleaner: explicit string branch with guard, else branch for instance.
- **PASS** — No behavioral change for valid seeder instances.

### Seeder.php (call method) — FAIL-CLOSED: PASS

**Before:** `new $class()->withBuilder(...)` — arbitrary class instantiation.
**After:** Checks `class_exists($class)` AND `is_subclass_of($class, self::class)` before construction.

**Findings:**
- **PASS** — Cross-seeder call validated against Seeder base class.
- **PASS** — Also fixed: `sprintf('Seeding: %s%s', $class, PHP_EOL)` preserved, named argument style fixed on `withBuilder()`.

## Test Review

### ProviderRegistrySecurityTest.php (NEW) — 3 tests

| Test | What it proves | Verdict |
|------|---------------|---------|
| test_rejects_non_existent_provider_class | Non-existent class rejected | PASS |
| test_rejects_provider_not_extending_base_register_dependency | Arbitrary class rejected | PASS |
| test_accepts_valid_provider_implementing_base_register_dependency | Valid provider accepted | PASS |

**Quality:** Clean test structure with setUp. Test doubles are minimal and correct.

### MigrationSeedSecurityTest.php (NEW) — 2 tests (plus setup)

| Test | What it proves | Verdict |
|------|---------------|---------|
| test_rejects_non_existent_seeder_string | Non-existent seeder class rejected | PASS |
| test_rejects_seeder_string_not_extending_seeder | Arbitrary class rejected | PASS |

**Quality:** Uses reflection to construct MigrationsCapability without full DB setup. Test double (MigrationInvalidSeedTarget) is minimal.

### SeederSecurityTest.php (NEW) — 3 tests

| Test | What it proves | Verdict |
|------|---------------|---------|
| test_rejects_non_existent_seeder_class_in_call | Non-existent class rejected in Seeder::call() | PASS |
| test_rejects_class_not_extending_seeder_in_call | Arbitrary class rejected | PASS |
| test_accepts_valid_seeder_class_in_call | Valid seeder accepted | PASS |

**Quality:** Uses anonymous class extending Seeder for parent. Clean assertions.

## Evidence Review

| File | Present | Accurate | Matches code |
|------|---------|----------|--------------|
| context-loaded.md | YES | YES | YES |
| implementation-summary.md | YES | YES | YES |
| validation-output.md | YES | YES | YES |
| governance-review.md | YES | YES | YES |
| final-decision.md | YES | YES | YES |
| test-proof.md | NO | N/A | N/A |
| threat-analysis.md | YES | YES | YES |

**YELLOW:** test-proof.md is missing. The tests exist in the diff and are well-structured, but the evidence summary file is not present. This is a documentation gap, not a code quality issue.

Final decision: `TODO_CLOSED (scope: Container ProviderRegistry + Migration/Seeder unguarded sites)` — Accurate.

## Governance Compliance

| Rule | Compliant |
|------|-----------|
| AGENTS.md §25 (Security) | YES |
| AGENTS.md §30 (Testing) | YES (tests present, evidence file gap) |
| how-to-system-security.md | YES |

## Verdict

**MERGE_READY**

TODO-004-b is a clean, focused P0 security hardening completing the dynamic class-loading coverage started in TODO-004. All four touched production files have correct fail-closed guards. Tests cover negative cases. Evidence is near-complete (missing test-proof.md summary file only).
