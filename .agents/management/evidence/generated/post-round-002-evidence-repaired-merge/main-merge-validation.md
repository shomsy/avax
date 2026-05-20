# Post-Round-002 Evidence-Repaired Merge Validation

## Preflight

- **Mode:** HARNESS-FULL
- **Prompt Type:** Coordinator / sequential integration
- **Branch target:** main
- **Worktree status:** CLEAN
- **Main pre-merge HEAD:** dd3c53936 docs(governance): review evidence-repaired post-round-002 branches
- **main ahead of origin/main by:** 1 commit (pre-existing, not from these merges)
- **git pull --ff-only origin main:** Already up to date

## Review Source

- Review evidence commit: `dd3c53936 docs(governance): review evidence-repaired post-round-002 branches`
- All 4 branches existed and contained expected commits
- Branch commits were not already on main

## Merge Order and Commits

| # | Branch | HEAD | Merge Commit | Decision |
|---|--------|------|--------------|----------|
| 1 | cleanup/todo-017-filesystem-boundaries | 5889deaf7 | 182074351 | MERGE_READY_WITH_YELLOW |
| 2 | security/todo-019-global-helper-shortcuts | f1af9908c | c79c4df0b | MERGE_READY_WITH_YELLOW |
| 3 | security/todo-018-security-logging-redaction | 4b3c7a86b | 40b954daf | MERGE_READY |
| 4 | security/todo-005-static-secret-state | cba150186 | 3f55d597d | MERGE_READY |

## Validation After TODO-017 (filesystem boundary routing)

| Gate | Result |
|------|--------|
| composer validate | GREEN |
| composer dump-autoload | GREEN (9362 classes) |
| phpunit --filter "FileSession\|FailureBoundary" | GREEN (142 tests, 303 assertions) |
| check-governance-index-current.php | GREEN |
| check-root-evidence-hygiene.php | GREEN |

**Status:** GREEN

## Validation After TODO-019 (global helper shortcuts)

| Gate | Result |
|------|--------|
| composer validate | GREEN |
| composer dump-autoload | GREEN (9363 classes) |
| phpunit tests/Unit/Components/HTTP/Security/ | GREEN (24 tests, 49 assertions) |
| check-governance-index-current.php | GREEN |
| check-root-evidence-hygiene.php | GREEN |

**Status:** GREEN

## Validation After TODO-018 (security logging and redaction)

| Gate | Result |
|------|--------|
| composer validate | GREEN |
| composer dump-autoload | GREEN (9365 classes) |
| phpunit tests/Unit/Components/Security/Cryptography/ tests/Unit/Framework/Security/RequestSigning/ | GREEN (26 tests, 40 assertions) |
| check-governance-index-current.php | GREEN |
| check-root-evidence-hygiene.php | GREEN |

**Status:** GREEN

## Validation After TODO-005 (static secret state reset)

| Gate | Result |
|------|--------|
| composer validate | GREEN |
| composer dump-autoload | GREEN (9366 classes) |
| phpunit --filter "SecretsSecurityTest\|SecretsCapabilitiesTest\|RuntimeResetProofTest\|WorkerLoopTest\|RuntimeSafetyFeatureTest" | GREEN (81 tests, 270 assertions) |
| check-runtime-composition-leaks.php | FAIL (4 HIGH pre-existing, unrelated Migrations/Container files) |
| check-governance-index-current.php | GREEN |
| check-root-evidence-hygiene.php | GREEN |

**Status:** GREEN for merged scope, pre-existing YELLOW on runtime-composition-leaks

## Final Main Validation

| Gate | Result | Notes |
|------|--------|-------|
| composer validate | GREEN | |
| composer dump-autoload | GREEN (9366 classes) | |
| phpunit --no-coverage | 8853 tests, 25176 assertions, 12 failures, 1 risky | All pre-existing |
| phpstan analyse framework components tests | 66 findings | All pre-existing, none from merged files |
| check-direct-instantiation.php | Pre-existing findings | Constructor default parameter findings, pre-existing |
| check-runtime-composition-leaks.php | FAIL (4 HIGH) | Pre-existing, unrelated Migrations/Container files |
| check-broken-reference-semantics.php | PASS (0 active) | |
| check-namespace-drift.php | PASS | |
| check-governance-index-current.php | GREEN | |
| check-root-evidence-hygiene.php | GREEN | |

### Pre-existing Test Failures (12)

All 12 failures in `ProcessPoolParallelismProofTest` — closure deserialization issue, unrelated to the 4 merged branches. The merged branches did not touch any Parallelism files.

### Pre-existing PHPStan Findings (66)

None from merged files. All in pre-existing test code (SqlInjection, SecurityShortcutsTest, QueueWorkerSecurity).

### Pre-existing Runtime Composition Leaks (4 HIGH)

- `components/DataStack/Database/System/Capabilities/Migrations/Migrations.php:174`
- `components/DataStack/Database/System/Capabilities/Migrations/CLI/SeederCommand.php:31`
- `components/DataStack/Database/System/Capabilities/Migrations/SeedDatabase/Seeder.php:25`
- `components/Application/Container/System/Capabilities/Providers/ProviderRegistry.php:26`

All unrelated to the 4 merged branches.

## Accepted YELLOW Items (from review)

| # | Branch | Item | Severity | Classification |
|---|--------|------|----------|----------------|
| 1 | TODO-017 | BuildDispatchConfiguredRoute fallback `new Filesystem()` | LOW | ACCEPTED |
| 2 | TODO-017 | stream wrapper / PSR-7 upload / path parsing exceptions | N/A | ACCEPTED_EXCEPTION |
| 3 | TODO-019 | 15 other shortcuts.php files still use `app()` | MEDIUM | ACCEPTED |
| 4 | TODO-019 | CSRF behavior tests need container bootstrap | LOW | ACCEPTED |

## Unexpected Blockers

None. All pre-existing failures classified:

- ProcessPoolParallelismProofTest (12 failures) — pre-existing closure serialization
- PHPStan 66 findings — pre-existing test code issues
- Runtime composition leaks (4 HIGH) — pre-existing Migrations/Container guarded class_exists
- Direct instantiation findings — pre-existing constructor default parameters

## TODO.md Updates

Added entry: `POST-ROUND-002-EVIDENCE-REPAIRED-MERGES` with status `done`, merge commit hashes, accepted YELLOW, and validation results.

## Final Decision

**EVIDENCE_REPAIRED_BRANCHES_MERGED_WITH_YELLOW**

Four evidence-repaired branches merged into main with focused validation after each merge. All merge-scoped validation is GREEN. Accepted YELLOW items from the review are preserved and documented. No new failures introduced by the merges. Full test suite and PHPStan show only pre-existing issues.

## Files Changed

- 4 merge commits on main
- `.agents/management/TODO.md` — added POST-ROUND-002-EVIDENCE-REPAIRED-MERGES entry
- `.agents/management/evidence/generated/post-round-002-evidence-repaired-merge/main-merge-validation.md` — this file
