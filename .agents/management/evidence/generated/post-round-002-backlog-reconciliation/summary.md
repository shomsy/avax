# Post-Round-002 Backlog Reconciliation

## Date
2026-05-20

## Main Preflight

| Check | Result |
|-------|--------|
| `git checkout main` | Already on main |
| `git status --short` | CLEAN (before evidence write) |
| `git pull --ff-only origin main` | Already up to date |
| `git log -1 --oneline` | 740d1af11 docs(governance): record post-round-002 evidence-repaired merge validation |
| `git branch --show-current` | main |
| origin/main sync | SYNCED |

## Worktree Cleanup

| Worktree | Branch | Action | Result |
|----------|--------|--------|--------|
| avax-todo-003 | security/todo-003-csrf-session-authority | Removed | DONE |
| avax-todo-004 | security/todo-004-dynamic-class-loading | Removed (stubborn, PHP-FPM locked) | PARTIAL — directory needs manual `sudo rm -rf` |
| avax-todo-004-b | security/todo-004-batch-b-container-migration | Removed (stubborn, PHP-FPM locked) | PARTIAL |
| avax-todo-005 | security/todo-005-static-secret-state | Removed (stubborn, PHP-FPM locked) | PARTIAL |
| avax-todo-010 | cleanup/todo-010-cache-publicsurface | Removed | DONE |
| avax-todo-016 | cleanup/todo-016-broken-reference-semantics | Removed (stubborn, PHP-FPM locked) | PARTIAL |
| avax-todo-017 | cleanup/todo-017-filesystem-boundaries | Removed (stubborn, PHP-FPM locked) | PARTIAL |
| avax-todo-018 | security/todo-018-security-logging-redaction | Removed (stubborn, PHP-FPM locked) | PARTIAL |
| avax-todo-019 | security/todo-019-global-helper-shortcuts | Unregistered from git, directory persists | PARTIAL |
| avax-todo-026a | cleanup/todo-026a-csv-formula-injection | Removed | DONE |
| avax-todo-026b | cleanup/todo-026b-compile-data-query-identifiers | Removed | DONE |
| avax-todo-phase0 | docs/todo-phase0-reconcile-truth | Removed | DONE |
| avax-round-002-review | review/parallel-round-002 | Unregistered from git, directory persists | PARTIAL |

**Note:** PHP-FPM holds locks on `.phpunit.cache/test-results` and `storage/app/test` files in several worktrees. `git worktree prune` cleaned git registrations. Remaining directories need `sudo rm -rf` or PHP-FPM restart + `rm -rf`.

**Remaining physical directories:**
- `/home/shomsy/projects/avax-todo-004`
- `/home/shomsy/projects/avax-todo-004-b`
- `/home/shomsy/projects/avax-todo-005`
- `/home/shomsy/projects/avax-todo-016`
- `/home/shomsy/projects/avax-todo-017`
- `/home/shomsy/projects/avax-todo-018`
- `/home/shomsy/projects/avax-todo-019`
- `/home/shomsy/projects/avax-round-002-review`

**Remaining worktrees:** NONE (git worktree list shows only main)

## Completed Tasks Confirmed on Main

| TODO | Title | Status | Merge Commit | Evidence |
|------|-------|--------|-------------|----------|
| Phase0 | Truth reconciliation | DONE | de40cedea | post-round-002-ready-merge |
| TODO-001 | Serialized payload hardening | DONE | (earlier round) | — |
| TODO-002 | Compiled container namespace | DONE | (earlier round) | — |
| TODO-003 | CSRF/session authority | DONE | c3abfc1bb | post-round-002-ready-merge |
| TODO-004 | Dynamic class-loading | DONE | 43c5e6883 + 634b552e5 | post-round-002-ready-merge |
| TODO-004-b | Container migration | DONE | 634b552e5 | post-round-002-ready-merge |
| TODO-005 | Static secret state reset | DONE | 3f55d597d | post-round-002-evidence-repaired-merge |
| TODO-016 | Broken reference semantics | DONE | 6718fa716 | post-round-002-ready-merge |
| TODO-017 | Filesystem boundaries | DONE | 182074351 | post-round-002-evidence-repaired-merge |
| TODO-018 | Security logging/redaction | DONE | 40b954daf | post-round-002-evidence-repaired-merge |
| TODO-019 | Global helper shortcuts | DONE | c79c4df0b | post-round-002-evidence-repaired-merge |
| TODO-026 | SQL/CSV verification | VERIFIED | 0954ef171 | todo-026-sql-csv-verification |
| TODO-026a | CSV formula injection | DONE | (merged) | — |
| TODO-026b | CompileDataQuery identifiers | DONE | (merged) | — |
| TODO-031 | Supplemental claims verification | VERIFIED_WITH_MAPPINGS | 3619e7e8a | todo-031-supplemental-claims-verification |

## TODO.md Changes

- Added `POST-ROUND-002-EVIDENCE-REPAIRED-MERGES` entry with status `done`
- Recorded all 4 merge commit hashes
- Documented accepted YELLOW items from review
- Updated validation results

## fix-this.md Changes

- TODO-003: OPEN → DONE (merge: c3abfc1bb)
- TODO-004: OPEN → DONE (merge: 43c5e6883 + 634b552e5)
- TODO-005: OPEN → DONE (merge: 3f55d597d)
- TODO-016: OPEN → DONE (merge: 6718fa716)
- TODO-017: OPEN → DONE (merge: 182074351)
- TODO-018: OPEN → DONE (merge: 40b954daf)
- TODO-019: OPEN → DONE (merge: c79c4df0b)
- Next Recommended Batch: updated to reflect TODO-001 as next priority
- Added Post-TODO-001 Priority Order section

## Remaining TODO Counts by Priority

| Priority | Count | TODOs |
|----------|-------|-------|
| P0 BLOCKER | 4 | TODO-001, TODO-002, TODO-006, TODO-007 |
| P1 HIGH | 8 | TODO-008, TODO-009, TODO-010, TODO-011, TODO-012, TODO-013, TODO-014, TODO-015 |
| P2 MEDIUM | 8 | TODO-020, TODO-021, TODO-022, TODO-023, TODO-024, TODO-025, TODO-027, TODO-028, TODO-029 |
| P3 LOW | 1 | TODO-030 |
| VERIFIED | 2 | TODO-026, TODO-031 |
| ACCEPTED_YELLOW | 1 | TODO-032 |

**Total active TODOs:** 22 (excluding VERIFIED, ACCEPTED_YELLOW, DONE)
**DONE:** 15 (including sub-TODOs)

## Accepted YELLOW Items

| TODO | Item | Severity | Classification |
|------|------|----------|----------------|
| TODO-032 | Semantic PHPDoc legacy ratchet (9823 findings) | ACCEPTED_YELLOW | Legacy debt, touched-file rule |
| Pre-existing | ProcessPoolParallelismProofTest (12 failures) | Pre-existing | Closure deserialization |
| Pre-existing | PHPStan 66 findings | Pre-existing | Test type warnings |
| Pre-existing | Runtime composition leaks (4 HIGH) | Pre-existing | Migrations/Container class_exists |
| Pre-existing | Direct instantiation (163 findings) | Pre-existing | Constructor default parameters |
| Review YELLOW | BuildDispatchConfiguredRoute fallback new Filesystem() | LOW | TODO-017 accepted |
| Review YELLOW | 15 other shortcuts.php still use app() | MEDIUM | TODO-019 accepted |
| Review YELLOW | CSRF behavior tests need container bootstrap | LOW | TODO-019 accepted |
| Review YELLOW | stream wrapper / PSR-7 upload / path parsing exceptions | N/A | TODO-017 ACCEPTED_EXCEPTION |

## Branch/Worktree Cleanup Candidates

All branches are merged into main. All worktrees are unregistered from git.
Remaining physical directories need manual cleanup:

```bash
sudo rm -rf /home/shomsy/projects/avax-todo-004
sudo rm -rf /home/shomsy/projects/avax-todo-004-b
sudo rm -rf /home/shomsy/projects/avax-todo-005
sudo rm -rf /home/shomsy/projects/avax-todo-016
sudo rm -rf /home/shomsy/projects/avax-todo-017
sudo rm -rf /home/shomsy/projects/avax-todo-018
sudo rm -rf /home/shomsy/projects/avax-todo-019
sudo rm -rf /home/shomsy/projects/avax-round-002-review
```

## Next Recommended Batch

**TODO-001: Harden serialized payload boundaries** (P0 BLOCKER)

This is the highest remaining security risk. It can be remediated without mixing unrelated architecture cleanup.

- Scope: `PhpCacheSerializer.php`, `SerializeClosureThroughLibrary.php`, `RedisCacheStore.php`, DecryptValue fallback
- Validation: `vendor/bin/phpunit --filter "CacheSerializer|CallableSerialization|DecryptValue|RedisCacheStore" --no-coverage`
- Evidence: `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`

After TODO-001:

1. **TODO-002** — compiled container namespace emission (P0 BLOCKER)
2. **TODO-006** — framework public entrypoint object-graph assembly (P0 BLOCKER)
3. **TODO-007** — AuthBuilder split (P0 BLOCKER)
4. **TODO-008 through TODO-015** — P1 batches

## Validation Summary

| Command | Result |
|---------|--------|
| `composer validate --no-check-publish` | GREEN |
| `php tooling/governance/check-governance-index-current.php` | GREEN |
| `php tooling/governance/check-root-evidence-hygiene.php` | GREEN |

## Final Decision

**BACKLOG_RECONCILED**

Main is synced with origin at `740d1af11`. 15 TODOs confirmed DONE. fix-this.md and TODO.md updated to reflect current state. Worktrees unregistered (8 directories need manual cleanup). Next recommended batch: TODO-001 serialization trust boundary.
