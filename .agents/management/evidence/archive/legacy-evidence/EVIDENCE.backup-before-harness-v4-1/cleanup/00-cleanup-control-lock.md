# AvaX Enterprise Cleanup Control Lock

Date: 2026-05-13
Program: AvaX Full Enterprise Cleanup Program
Mode: Harness-Full Mode (`uradi po pravilima .agents`)
Initial status: CLEANUP_PROGRAM_INITIAL_STATUS = YELLOW_UNTIL_FULL_BASELINE_AND_GATES_ARE_GREEN

## Branch And Commit

Current branch: `main`
Current commit: `fc499a9e5769d812b045049ec8c1834eacfdee0d`
Recent commit label: `fc499a9e5  ▪ V5.8.4 DI Classification — Final Status`

## Worktree Inventory

This cleanup includes the current worktree, not only committed files.

| Metric                                                                  | Count | Evidence command                                                                      |
|-------------------------------------------------------------------------|------:|---------------------------------------------------------------------------------------|
| Dirty worktree entries                                                  |    12 | `git status --short`                                                                  |
| Tracked files                                                           |  6651 | `git ls-files \| wc -l`                                                               |
| Visible non-git files                                                   |  6604 | `rg --files --hidden --glob '!.git/**' \| wc -l`                                      |
| Repository files excluding `.git`                                       | 37692 | `find . -type f -not -path './.git/*' \| wc -l`                                       |
| PHP files in framework/components/tests/examples/labs/tooling           |  4210 | `rg --files --glob '*.php' framework components tests examples labs tooling \| wc -l` |
| Files in framework/components/tests/examples/labs/tooling/docs/EVIDENCE |  6231 | `rg --files framework components tests examples labs tooling docs EVIDENCE \| wc -l`  |

Dirty worktree entries at control-lock creation:

```text
 M .codex
 M avax.txt
 M components/Application/Cache/System/Capabilities/Compilation/CompiledCache.php
 M components/Application/Cache/System/Capabilities/CompiledCache/DistributedCompiledCache/CompiledCacheFreshness.php
 M components/Application/Cache/System/Capabilities/CompiledCache/DistributedCompiledCache/CompiledCacheManifest.php
 M components/Application/Cache/System/Capabilities/CompiledCache/ManageCompiledCache/CompiledCacheDirectory.php
 M components/Application/Cache/System/Capabilities/Distribution/UseCacheTiers/L2DistributedCache.php
 M components/Application/Cache/System/Capabilities/Storage/StoreCachedValues/FileCacheStore.php
 M components/Application/Cache/System/Configuration/BuildCache.php
 M components/Application/Cache/System/Configuration/CacheStoreConfiguration.php
 M components/Application/Container/System/Capabilities/Composition/Assembly/AssembleRuntime.php
 M tooling/components/check-component-runtime-assembly.php
```

Targeted PHP diffs were readable. A global `git diff --name-only` failed because Git LFS attempted to clean `avax.txt`
through `.git/lfs/tmp`, which is read-only in this sandbox. This is recorded in `skipped-work-ledger.md`.

## Governance Stack Found

Local root contract:

- `AGENTS.md`
- `.agents/GOVERNANCE_INDEX.md`
- `.agents/how-to/how-to-*.md`
- `.agents/management/ACTIVE.md`
- `.agents/management/TODO.md`
- `.agents/management/BUGS.md`
- `.agents/business-logic/avax-language.md`
- `.agents/skills/{validation,refactor,testing,security,performance,review}/SKILL.md`

Reusable mounted governance:

- `.agents/.rules/AGENTS.md`
- `.agents/.rules/governance/core/**`
- `.agents/.rules/governance/profiles/languages/php.md`
- `.agents/.rules/governance/architecture/**`
- `.agents/.rules/governance/security/**`
- `.agents/.rules/governance/execution/**`
- `.agents/.rules/governance/standards/**`
- `.agents/.rules/governance/delivery/**`
- `.agents/.rules/governance/intelligence/**`
- `.agents/.rules/governance/integrations/**`

Not found:

- `.agents/AGENTS.md`
- `.agents/governance/**`

Local AvaX rules override reusable mounted rules for filesystem shape, naming, PublicSurface, Flow/Capability shape,
stage lock, and runtime-safe framework direction.

## Current Truth

`CURRENT_TRUTH.md` says V1 through V4 are green, V5 through V5.8 are complete/green, and V5.9 Boot DSL is planned
separately. `EVIDENCE/EXECUTION.md` says the next allowed stage is V5.9 Boot DSL or V5.8.x deferred items.

This cleanup program supersedes that next action for this execution: V5.9 must not start until cleanup is proven green.

## Active Stages

Cleanup active stage: Stage A - Validation Baseline Closure.

Cleanup planned stages:

- Stage A: Validation Baseline Closure
- Stage B: Component Status Lock and Scaffold Honesty
- Stage C: DI and Runtime Assembly Discipline
- Stage D: PublicSurface and Hollow Shell Cleanup
- Stage E: Static State and Long-Lived Runtime Safety
- Stage F: Router, HTTP, and Runtime Entry Stability
- Stage G: PHPStan and Type System Closure
- Stage H: Health and Doctor Checks for Core Components
- Stage I: Component Maturity Gates
- Stage J: Naming, Duplicate Owners, and Skeleton Cleanup
- Stage K: Truth, Evidence, Docs Status, and Governance Gap Reconciliation
- Stage L: Final Whole-System Acceptance Audit

Forbidden scope:

- No V5.9 Boot DSL implementation.
- No V6, EventStore, production Event Sourcing, RuntimeCompilation/JIT, SearchIndex, polyrepo migration, or final public
  docs phase.
- No new features.
- No skeleton classes.
- No broad allowlists.
- No weakening tests, PHPStan, or governance gates.

## Active Components

From `EVIDENCE/components/component-status-lock.md`:

- ACTIVE_GREEN: API/GraphQL, Application/Filesystem, CLI/Console, HTTP/Request, HTTP/Response, HTTP/Router,
  HTTP/Session, Identity/Tokens, Operations/Concurrency, Operations/Events, Operations/Logging,
  Operations/Scheduler, Security/Cryptography, Security/Redaction, Integration
- ACTIVE_YELLOW: Application/Container, DataStack/Database, Identity/Access, Integration/ObjectStorage,
  DeveloperTools/Diagnostics

## Scaffold Components

- HTTP/Middleware
- Identity/Credentials
- Operations/Mail
- Operations/Queue
- Security/Hashing
- DeveloperTools/CodeGeneration

## Roadmap Components

- DeveloperTools/Testing
- Foundation
- Presentation

## Labs / Evidence-Only Areas

- `components/SystemDesign` is EVIDENCE_ONLY.
- `labs/SystemDesignKit` is kept in PHPStan scope per user baseline command.
- `labs/API/DescribeApi` and `labs/Integration/ObjectStorage` are labs/evidence-only unless promoted by current truth.
- `EVIDENCE/archive/**` and `EVIDENCE/recovery-staging/**` are evidence/recovery sources only.

## Canonical Validation Commands

```bash
git status --short
git branch --show-current
git log -20 --oneline
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests labs/SystemDesignKit --memory-limit=1G
php tooling/security/check-security-blockers.php
php tooling/governance/check-component-adoption.php
php tooling/refactor/check-component-canonical-shape.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php
php tooling/refactor/check-advanced-pattern-folder-violations.php
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-raw-file-operations.php
php tooling/failure-boundary/check-attributes-compiled.php
php tooling/failure-boundary/check-dogfooding.php
php tooling/failure-boundary/check-local-try-catch.php
php tooling/events/check-canonical-event-owner.php
php tooling/events/check-compiled-listener-registry.php
php tooling/events/check-dispatch-runtime.php
php tooling/events/check-event-emission-api.php
php tooling/events/check-event-sourcing-not-default.php
php tooling/events/check-events-no-hot-path-reflection.php
php tooling/events/check-fluent-dsl-registration.php
php tooling/events/check-listens-to-attribute.php
php tooling/events/check-psr14-interop.php
php tooling/events/check-real-dogfooding.php
php tooling/database/gate-1-entity-lifecycle-wiring.php
php tooling/database/gate-2-query-lifecycle-wiring.php
php tooling/database/gate-3-transaction-lifecycle-wiring.php
php tooling/database/gate-4-compiled-registry-frozen.php
php tooling/database/gate-5-dsl-acceptance.php
php tooling/database/gate-6-lifecycle-integration-tests.php
php tooling/database/gate-7-database-component-shape.php
php tooling/database/gate-8-phpstan-clean.php
php tooling/components/check-component-status-lock.php
php tooling/components/check-no-unclassified-scaffolding.php
php tooling/components/check-hollow-public-surfaces.php
php tooling/components/check-component-runtime-assembly.php
php tooling/components/check-component-static-state-safety.php
php tooling/components/check-component-health-doctor-policy.php
php tooling/components/check-component-behavior-proof-map.php
php tooling/components/check-component-docs-status-policy.php
```

Additional expected gates to find or create during cleanup:

- `tooling/runtime/check-callable-resolution.php`
- `tooling/governance/check-truth-consistency.php`
- `tooling/refactor/check-empty-production-classes.php`

If absent during baseline, they must be recorded as PLANNED / NOT IMPLEMENTED, not PASS.

## Known Blockers

- Current baseline validation has not yet been run for this cleanup.
- The worktree is dirty and must be included in validation and evidence.
- Global `git diff --name-only` currently fails through Git LFS on `avax.txt`.
- V5.8.4 evidence reported 23 PHPStan baseline errors; current validation must prove whether they still exist.

## Known Yellow Items

- `Application/Container` status is ACTIVE_YELLOW.
- `DataStack/Database` status is ACTIVE_YELLOW.
- `Identity/Access` status is ACTIVE_YELLOW.
- `Integration/ObjectStorage` status is ACTIVE_YELLOW.
- `DeveloperTools/Diagnostics` status is ACTIVE_YELLOW.
- V5.5 benchmark evidence is historical unless re-run.
- V5.9 remains blocked until cleanup is green.

## Known PHPStan Issues

Known from `EVIDENCE/v5.8.4/16-final-acceptance-audit.md`, pending current validation:

- ResolveCallable strict comparison.
- EntityRepository generic type.
- DispatchRouteAction method_exists type.
- Router is_callable always true.
- InMemoryUserSource private property access.
- QueueServiceProvider invalid types.
- RegisterQueueCommands mixed/undefined methods.

Current cleanup must not accept these as "pre-existing" without owner, expiry, and proof.

## Known PHPUnit Issues

No active PHPUnit issue is proven yet for this cleanup. V5.8.4 evidence reports 8289 tests green, but that is stale
until Stage A reruns PHPUnit on the current worktree.

## Known Component Maturity Issues

- ACTIVE_YELLOW components require honest blockers and decisions.
- SCAFFOLD components must not be counted as production-ready.
- Component maturity gates exist, but must be rerun on the dirty worktree.

## Known DI / Runtime Assembly Issues

Dirty worktree includes DI/runtime assembly changes in:

- Cache compiled cache and file cache paths.
- Cache configuration assembly.
- Container runtime assembly.
- Component runtime assembly gate.

These must be validated before code remediation proceeds beyond Stage A.

## Known Health / Doctor Gaps

V5.8.4 says component health/doctor policy gate passes, while doctor checks are recommended but not required for pass.
Cleanup Stage H must verify whether active runtime-critical components have real health/doctor stories, not fake
always-green checks.

## Known Static State Risks

Known static holders from prior evidence include Container, LazyProxy, GlobalEventListenerState, and
GlobalDatabaseLifecycleState. Stage E must rescan current code for static mutable state and prove reset safety.

## Known Docs / Truth Drift

`CURRENT_TRUTH.md` and `EVIDENCE/EXECUTION.md` currently point toward V5.9 readiness after V5.8.4. This cleanup program
intentionally changes current execution status to pre-V5.9 cleanup. Truth files must be reconciled in Stage K.

## Known Governance Gaps

- Missing local `.agents/AGENTS.md` is acceptable because root `AGENTS.md` and `.agents/.rules/AGENTS.md` exist, but it
  is
  recorded.
- Missing `.agents/governance/**` is acceptable because this repo uses `.agents/.rules/governance/**`, but it is
  recorded.
- Global diff/LFS failure needs a documented policy or local environment fix if whole-worktree diff is mandatory.

## Stop Conditions

Stop expansion and report YELLOW/RED if:

- Composer validation or autoload cannot run.
- PHPUnit has failures/errors that are unsafe to fix immediately.
- PHPStan has current-stage errors without accepted, owned, expiring exception.
- Any current-stage gate fails and cannot be fixed safely.
- Runtime assembly leaks remain in active runtime paths.
- Unsafe static state remains in request/job runtime paths.
- Router/HTTP tests fail.
- Component status ambiguity remains.
- Skipped work contains V5.9 blockers.

## Final Acceptance Criteria

FULL_GREEN_READY_FOR_V5_9 requires:

- Composer valid.
- Autoload has 0 warnings.
- PHPUnit has 0 failures/errors.
- PHPStan has 0 errors or formally accepted non-blocking baseline with owner and expiry.
- All current-stage gates pass.
- No scaffold ambiguity.
- No hollow active PublicSurface.
- No unsafe static state.
- No runtime infrastructure assembly leaks.
- Router/HTTP stable.
- Component status lock current.
- Health/doctor story for active core components.
- Skipped-work ledger has no V5.9 blockers.
- Governance gaps documented and non-blocking.
- Truth files updated.

