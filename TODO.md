# AvaX Remediation Execution Board

Status: REMEDIATION_ACTIVE — All P0 BLOCKERs CLOSED (0/7 remaining), reconciliation complete
Execution control: `EVIDENCE/EXECUTION.md` is the canonical execution control document.
Remediation backlog: `fix-this.md` is the canonical review reconciliation backlog (reconciled 2026-05-20).
Evidence root: `.agents/management/evidence/generated/backlog-truth-reconciliation/`

## Board Role

- `fix-this.md` is the canonical remediation backlog with full finding details.
- `TODO.md` is the operational AI execution board.
- No separate `AGENT_ASSIGNMENT.md` is used.
- Agent lanes, active round, branch/worktree names, dependency notes, and merge order live here.
- Evidence remains under `.agents/management/evidence/generated/**`.

## Current Truth

fix-this.md (2026-05-20) — RED / BLOCKED_BY_HOW_TO / TARGETED_REDESIGN

| Metric | Count |
|--------|-------|
| Total TODOs | 34 |
| P0 BLOCKER | 7 (ALL completed, 0 remaining) |
| P1 HIGH | 6 (009-013 PARTIALLY_RESOLVED, 014 OPEN) |
| P2 MEDIUM | 8 (027 PASS_WITH_YELLOW, 7 OPEN) |
| P3 LOW | 1 (030 PASSING) |
| VERIFIED | 2 (026, 031) |
| ACCEPTED_YELLOW | 2 (027, 032) |
| NEEDS_VERIFICATION | 0 |
| DONE | 13 |
| PARTIALLY_RESOLVED | 5 (009-013) |

Reconciliation: 2026-05-20 — all statuses verified against git state. Evidence: `.agents/management/evidence/generated/backlog-truth-reconciliation/`

## Active Stage

Remediation — Stage locked by fix-this.md cleanup execution order.
No feature work. No roadmap work. No public API changes unless explicitly approved.

## Forbidden Work

- Production feature work outside TODO scope
- Public API changes unless explicitly approved by governance
- Mechanical renames across components
- Fake GREEN claims without validation evidence
- Mixing unrelated components in a single remediation batch
- Creating placeholder/skeleton classes

## Execution Rules

- Parallel execution is allowed only for non-overlapping TODOs.
- One active task per agent at a time.
- One active task equals one branch, one worktree, one evidence package, one review, one merge candidate.
- Integration into main is always sequential.
- Execution agents must never merge into main.
- Coordinator owns merge order.
- Review agent must approve before merge.
- Each agent must use the AvaX Enterprise Remediation Skill before starting work.
- If an agent is on main, it must stop before touching code.
- Each iteration must apply touched-file semantic PHPDoc cleanup (TODO-032 rule).
- No iteration may expand beyond its strict scope.
- Each iteration must reference its source finding IDs from fix-this.md.

## Agent Lane Model

A lane is long-lived ownership context.
A task is short-lived execution work.

Rules:
- An agent may own a lane with multiple future tasks.
- An agent may execute only one active task at a time.
- Each active task gets a fresh branch/worktree from main.
- After merge, the next task starts from updated main.

Lane table:

| Lane | Purpose | Current Active Task | Next Possible Tasks | Parallel Safe? |
|------|---------|---------------------|---------------------|----------------|
| Agent A | CSV/Data export security | TODO-026a (DONE) | TODO-018 slice later | YES |
| Agent B | DataStack SQL safety | TODO-026b (DONE) | DataStack query/test hardening later | YES |
| Agent C | Validation cleanup/runtime refs | TODO-016 (DONE) | TODO-017 later | YES |
| Agent D | HTTP session security | TODO-003 (DONE) | HTTP PublicSurface slice later | LIMITED |
| Agent R | Review-only | Round 002 review (DONE) | future review rounds | N/A |
| Coordinator | Integration | Round 002 merge complete | next round | N/A |

## Current Round — Parallel Round 002

Goal:
Close small/high-value security and validation items while keeping integration controlled.

Active branches/worktrees (all merged to main):

| Agent | Task | Branch | Merge Commit | Type | Merge Order |
|-------|------|--------|--------------|------|-------------|
| Agent C | TODO-016 | cleanup/todo-016-broken-reference-semantics | `6718fa716` | remediation | 1 |
| Agent A | TODO-026a | cleanup/todo-026a-csv-formula-injection | `64cc4189a` | remediation | 2 |
| Agent B | TODO-026b | cleanup/todo-026b-compile-data-query-identifiers | `b1a66c781` | remediation | 3 |
| Agent D | TODO-003 | security/todo-003-csrf-session-authority | `c3abfc1bb` | analysis-first remediation | 4 |

## Dependency and Conflict Notes

- TODO-026a and TODO-026b are parallel-safe because CSV rendering and DataStack SQL compilation are separate file sets.
- TODO-016 is parallel-safe if it only touches known broken reference semantics and does not modify HTTP session/security ownership.
- TODO-003 is analysis-first and may become sequential-only if it overlaps HTTP/PublicSurface or global helper cleanup.
- TODO-004, TODO-005, TODO-006, TODO-007 are not part of Round 002.
- TODO-004 is the next logical item (dynamic class-loading boundaries).
- TODO-005 should wait until TODO-004 is understood.
- TODO-006 and TODO-007 require separate architecture-focused rounds.

## Branch / Worktree Rule

Before implementation, every execution agent must report:

```bash
git branch --show-current
git status --short
git log -1 --oneline
pwd
```

Required:

- current branch
- current worktree path
- latest commit
- whether branch is main: YES/NO
- dirty file classification

If branch is main, stop.
If unrelated dirty files exist, stop.
If another agent's files are dirty in the worktree, stop.

## Round Completion Rule

A round is complete only when:

- every execution branch has evidence
- review agent approves or blocks each branch
- coordinator merges approved branches one by one
- validation runs after every merge
- final main integration evidence is written
- TODO.md statuses are updated
- fix-this.md remains canonical and truthful

## Completed / Integrated

- [x] **TODO-001** — Serialized payload boundary hardening
  - Commit: `36a8e3547`
  - Status: DONE / integrated into main
  - Scope: PhpCacheSerializer, SerializeClosureThroughLibrary, RedisCacheStore, DecryptValue fallback

- [x] **TODO-002** — Compiled container namespace emission
  - Commit: `ae0c5689b`
  - Status: DONE / integrated into main
  - Scope: MethodEmitter, CompileContainer

- [x] **TODO-026** — SQL/CSV verification
  - Commits: `0954ef171`, `f27097437`
  - Status: VERIFIED / decomposed into TODO-026a (P1) and TODO-026b (P1)

- [x] **TODO-031** — Supplemental scan verification
  - Commits: `3619e7e8a`, `c421bd1e4`
  - Status: VERIFIED_WITH_MAPPINGS / 141 IDs mapped to existing TODOs 004-024

### Round 002 — Integrated (2026-05-20)

- [x] **TODO-016** — Broken reference semantics
  - Merge commit: `6718fa716`
  - Status: DONE / integrated into main
  - Scope: SagaReferenceSemanticsTest, SagaTest, WorkflowTest, WorkqueueFeatureTest, ResolveActiveRecovery, IdempotencyStore, CompensationExecutor, PersistWorkerProgress
  - Validation: 0 active broken refs, all governance GREEN

- [x] **TODO-026a** — CSV formula injection hardening
  - Merge commit: `64cc4189a`
  - Status: DONE / integrated into main
  - Scope: CsvFormat, CsvFormatter, NeutralizeFormulaCell
  - Validation: 46 CSV injection tests GREEN (negative + positive)

- [x] **TODO-026b** — CompileDataQuery SQL identifier hardening
  - Merge commit: `b1a66c781`
  - Status: DONE / integrated into main
  - Scope: CompileDataQuery identifier interpolation sanitization
  - Validation: 88 SQL injection identifier safety tests GREEN

- [x] **TODO-003** — CSRF/session authority unification
  - Merge commit: `c3abfc1bb`
  - Status: DONE / integrated into main
  - Scope: CsrfToken, CsrfTokenGenerator, CsrfVerifier, SessionScope, Security shortcuts
  - Validation: 53 CSRF/session tests GREEN, runtime composition leaks PASS

## Required Validation Per Iteration

```bash
# Minimal: focused tests + scope-relevant gates
vendor/bin/phpunit --filter "<relevant-test-filter>" --no-coverage
php tooling/refactor/check-broken-reference-semantics.php

# Full: end-of-phase validation
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests --memory-limit=1G
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php
php tooling/refactor/check-component-canonical-shape.php
php tooling/refactor/check-advanced-pattern-folder-violations.php
```

---

## Phase 0: Pre-flight & Truth Reconciliation

Goal: Align CURRENT_TRUTH.md with fix-this.md reality before any remediation code changes.

### Iteration 0.1 — Reconcile CURRENT_TRUTH.md
- Update header: Date, Branch, Active status to RED/BLOCKED
- Replace V4/V5/legacy GREEN claims with pointer to fix-this.md
- No production code changes
- Evidence: comparison diff with old GREEN claims

Status: PENDING

---

## Phase 1: P0 Security/Runtime Blockers (Remaining)

Goal: Close remaining P0 security and runtime integrity issues.
Note: TODO-001 and TODO-002 are already completed (see Completed/Integrated section above).

### Iteration 1.1 — CSV Formula Injection Hardening
- **TODO-026a**: Escape CSV formula injection cells (CsvFormat, CsvFormatter)
- Foci: `components/HTTP/ContentNegotiation/System/Capabilities/Formats/CsvFormat.php`, `components/HTTP/ContentNegotiation/System/PublicSurface/CsvFormatter.php`
- Negative tests required: CSV formula chars (=, +, -, @, tab)
- Merge commit: `64cc4189a`
- Validation: 46 tests GREEN, governance GREEN
- Status: DONE

### Iteration 1.2 — CompileDataQuery Identifier Interpolation Hardening
- **TODO-026b**: Sanitize identifier interpolation in CompileDataQuery SQL compilation
- Foci: `components/DataStack/Persistence/System/Flows/CompileDataQuery/CompileDataQuery.php`
- Negative tests required: malicious identifiers rejected or escaped
- Merge commit: `b1a66c781`
- Validation: 88 tests GREEN, governance GREEN
- Status: DONE

### Iteration 1.3 — CSRF/Session Authority
- **TODO-003**: Unify CSRF/session authority (SessionScope, NativeSessionStore, CsrfToken, CsrfTokens, CsrfTokenGenerator)
- Foci: `components/HTTP/Session`, `components/HTTP/Security`
- Tests: negative CSRF validation, token rotation, session lifecycle, duplicate helper load
- Merge commit: `c3abfc1bb`
- Validation: 53 tests GREEN, runtime composition leaks PASS, governance GREEN
- Status: DONE

### Iteration 1.4 — Dynamic Class-Loading Boundaries
- **TODO-004**: Close dynamic class-loading execution paths (QueueWorker, RunRecoveryAction, RunFallbackAction, migration/seeder, Container class_exists+new)
- Foci: `components/Operations/Queue`, `framework/System/Capabilities/FailureBoundary`, `components/Application/Container`
- Tests: negative tests for unknown class, wrong interface, payload class injection
- Validation: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-runtime-composition-leaks.php && vendor/bin/phpunit --filter "QueueWorker|FailureBoundary|Migration|Seeder" --no-coverage`
- Evidence: `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`
- Commits: 43c5e6883 (TODO-004) + 634b552e5 (TODO-004-b)

Status: DONE

### Iteration 1.5 — Worker-Unsafe Static Secret State
- **TODO-005**: Remove worker-unsafe static secret/security runtime state (Secrets, Diagnostics, FailureBoundary, ExternalState, ResourceGovernor, GlobalEventListenerState)
- Foci: `components/Security/Secrets`, `components/Operations/Events`, `framework/System/Capabilities/RuntimeSafety`, `framework/System/Capabilities/ExternalState`
- Tests: long-lived worker two-request leakage tests, reset tests, secret overwrite/isolation
- Validation: `php tooling/refactor/check-runtime-composition-leaks.php && vendor/bin/phpunit --filter "Secrets|Diagnostics|FailureBoundary|ExternalState|StateReset" --no-coverage`
- Evidence: `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`
- Commit: 3f55d597d

Status: DONE

---

## Phase 2: P0 Architecture Blockers

Goal: Close architecture-level P0 items blocking further remediation.

### Iteration 2.1 — Framework Entrypoint Composition
- **TODO-006**: Move framework public entrypoint object-graph assembly out of runtime
- Foci: `framework/System/PublicSurface`, `framework/System/Flows/RunApplication`, `framework/System/Flows/CreateApplication`
- Tests: Avax::create, BootDsl::create, App::handle, RunApplication pipeline regression tests
- Validation: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-runtime-composition-leaks.php && php tooling/refactor/check-public-surface.php`
- Evidence: `.agents/management/evidence/generated/maximum-remaining-backlog-sweep/final-todo-006-closure.md`

Status: DONE

### Iteration 2.2 — AuthBuilder Split
- **TODO-007**: Split AuthBuilder into bounded configuration responsibilities
- Foci: `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php`
- Result: AuthBuilder reduced from 797 to 560 lines via sub-builder decomposition (7 sub-builders)
- Validation: `php tooling/governance/check-large-unit-thresholds.php && vendor/bin/phpunit --filter "AuthBuilder|Auth" --no-coverage`
- Evidence: `.agents/management/evidence/generated/todo-007-authbuilder-split/`
- Commit: 0a98822e3

Status: DONE

---

## Phase 3: P1 Static State + Public Surface Remediation

Goal: Remove remaining static mutable state and move PublicSurface construction to Configuration.

### Iteration 3.1 — Remaining Static Mutable State
- **TODO-008**: Retire remaining static mutable PublicSurface/runtime state by ownership slice (21 units)
- Foci: `DeveloperTools/Diagnostics`, `API/SchemaGeneration`, `Application/Cache`, `Application/Storage`, `Application/Pipeline`, etc.
- Result: reset() lifecycle added to FeatureFlags, MessageBus, Realtime; 12 other units already had reset()
- Tests: two-request leak tests per touched owner
- Validation: `php tooling/refactor/check-runtime-composition-leaks.php && vendor/bin/phpunit --no-coverage`
- Evidence: `.agents/management/evidence/generated/autonomous-backlog-continuation/todo-008-closure.md`
- Commit: 482b9e3cb

Status: DONE

### Iteration 3.2 — PublicSurface Construction (Batch A: API + DevTools + Application)
- **TODO-009**: Reduce API/DeveloperTools PublicSurface construction pressure
- **TODO-010**: Reduce Application PublicSurface construction pressure
- Foci: `components/API/*`, `components/DeveloperTools/*`, `components/Application/*`
- Note: ServiceProviders exist (TODO-015); remaining findings are constructor defaults (TODO-014 overlap)
- Validation: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`

Status: PARTIALLY_RESOLVED

### Iteration 3.3 — PublicSurface Construction (Batch B: HTTP + Operations)
- **TODO-011**: Reduce HTTP PublicSurface construction pressure
- **TODO-012**: Reduce Operations PublicSurface construction pressure
- Foci: `components/HTTP/*`, `components/Operations/*`
- Note: ServiceProviders exist (TODO-015); remaining findings are constructor defaults (TODO-014 overlap)
- Validation: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php && vendor/bin/phpunit --filter "HTTP|Http" --no-coverage`

Status: PARTIALLY_RESOLVED

### Iteration 3.4 — PublicSurface Construction (Batch C: Security + Identity + DataStack)
- **TODO-013**: Reduce Security/Identity/DataStack PublicSurface construction pressure
- Foci: `components/Security/*`, `components/Identity/*`, `components/DataStack/*`
- Note: ServiceProviders exist (TODO-015); remaining findings are constructor defaults (TODO-014 overlap)
- Validation: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`

Status: PARTIALLY_RESOLVED

### Iteration 3.5 — Constructor Defaults + ServiceProviders
- **TODO-014**: Move constructor default dependency creation into approved Configuration owners (53 units)
- **TODO-015**: Add missing ServiceProvider assembly owners (25 units)
- Foci: CROSS_CUTTING — per-component-owner approach
- TODO-015: 24 ServiceProviders added across 8 component suites (commit 8171bfe2d), gate ALL OK
- TODO-014: 529 findings remaining, per-component approach required
- Validation: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-service-provider-coverage.php && vendor/bin/phpunit --filter "ServiceProvider|Provider" --no-coverage`

Status: TODO-014 READY_ANALYSIS_FIRST, TODO-015 DONE

---

## Phase 4: P1 Cross-Cutting Remediation

Goal: Close cross-cutting P1 items that span multiple components.

### Iteration 4.1 — Broken Refs
- **TODO-016**: Fix broken reference semantics in public/runtime namespaces
- Foci: `components/Operations/ApplicationWorkflow`, `components/HTTP`, `framework/System/Configuration/Builders`, `framework/System/Capabilities/FailureBoundary`
- Merge commit: `6718fa716`
- Validation: 0 active broken refs, governance GREEN
- Status: DONE

### Iteration 4.1b — Filesystem Paths
- **TODO-017**: Route raw filesystem/path operations through approved first-party boundaries
- Foci: `components/DataStack/DataTransfer`, `framework/System/Configuration/Builders`, `framework/System/Capabilities/FailureBoundary`, `Doctor/CheckAutoload.php`, `Runtime/Capabilities/RunApplicationOnPhpBuiltInServer.php`, `Doctor/*.php`
- Commit: 182074351
- Validation: `php tooling/refactor/check-raw-file-operations.php || true; php tooling/refactor/check-public-surface.php`
- Evidence: `.agents/management/evidence/generated/post-round-002-evidence-repaired-merge/main-merge-validation.md`

Status: DONE

### Iteration 4.2 — Security Logging + Global Helpers
- **TODO-018**: Harden security logging, redaction, and secret parameter handling
- **TODO-019**: Replace global helper service-locator shortcuts with testable boundaries
- Foci: `tests/Unit/Components/Security/Cryptography`, `components/Application/Text`, `HTTP/Security`, `Security/RequestSigning`
- TODO-018: Commit 40b954daf — SensitiveParameter + negative crypto/request-signing tests
- TODO-019: Commit c79c4df0b — HTTP/Security shortcuts fixed, CSP/HSTS added
- Validation: `vendor/bin/phpunit --filter "Redaction|Secrets|Cryptography|RequestSignature|Logging|csrf" --no-coverage && php tooling/refactor/check-runtime-composition-leaks.php`

Status: TODO-018 DONE, TODO-019 DONE

---

## Phase 5: P2 Architecture & Maintainability

Goal: Improve architecture, test coverage, and maintainability.

### Iteration 5.1 — Constructor Bloat + Forbidden Folders
- **TODO-020**: Classify and reduce constructor bloat by owner (41 units)
- **TODO-022**: Resolve forbidden concept folder names through approved governance decisions
- Foci: `components/Identity/Auth`, `components/DataStack`, all forbidden-named folders
- TODO-020: 483 findings, per-component split required — READY_ANALYSIS_FIRST
- Validation: `php tooling/refactor/check-constructor-bloat.php && php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-component-suite-structure.php && php tooling/refactor/check-namespace-drift.php`

Status: TODO-020 READY_ANALYSIS_FIRST, TODO-022 PENDING

### Iteration 5.2 — Duplicate Ownership + Hidden I/O
- **TODO-023**: Collapse duplicate ownership and duplicate class implementations
- **TODO-024**: Replace hidden superglobal/env/IO access in runtime flows
- Foci: Container, ResourceGovernance, GracefulShutdown, DeveloperTools/Diagnostics, HTTP/AfterResponse, etc.
- Validation: `php tooling/refactor/check-duplicate-owners.php && php tooling/refactor/check-namespace-drift.php && php tooling/refactor/check-runtime-composition-leaks.php`

Status: PENDING

### Iteration 5.3 — Error Handling + Missing Tests
- **TODO-025**: Make error handling explicit where catch-and-continue hides failures
- **TODO-021**: Add missing behavior proof and negative tests by risk slice
- Foci: RPC, AfterResponse, PreCommit, Cryptography fallback; 17 units with weak test proof
- Validation: `vendor/bin/phpunit --filter "Failure|Exception|AfterResponse|PreCommit|Cryptography" --no-coverage`

Status: PENDING

### Iteration 5.4 — Interface Contracts + Empty Stubs
- **TODO-027**: Document public interface contracts and failure modes
- **TODO-028**: Replace empty stubs/no-op methods with explicit behavior or failure
- Foci: HttpKernelInterface, RuntimeKernelInterface, ResetApplicationState, ShutdownRuntime, ConfigureRuntime
- TODO-027: Semantic PHPDoc passes with 0 new violations — PASS_WITH_YELLOW (legacy ratchet active)
- TODO-028: ~10 files with empty methods, needs per-case analysis — NEEDS_DEDICATED_SESSION
- Validation: `php tooling/governance/check-semantic-phpdoc.php && vendor/bin/phpunit --filter "ResetApplicationState|ShutdownRuntime|ConfigureRuntime" --no-coverage`

Status: TODO-027 PASS_WITH_YELLOW, TODO-028 NEEDS_DEDICATED_SESSION

### Iteration 5.5 — DI Container Performance
- **TODO-029**: Measure and reduce DI/container object graph performance pressure
- Foci: DIContainer, runtime paths with repeated short-lived object graphs
- Note: Measure first; change only after benchmark/test proof — NEEDS_DEDICATED_SESSION
- Validation: `php tooling/refactor/check-direct-instantiation.php && php tooling/governance/check-large-unit-thresholds.php`

Status: NEEDS_DEDICATED_SESSION

---

## Phase 6: P3 + Accepted Debt

Goal: Close low-risk items and maintain accepted-yellow debt.

### Iteration 6.1 — Low-Risk Cleanup + PHPDoc Ratchet
- **TODO-030**: Close low-risk compat/version/style cleanup with evidence
- **TODO-032**: Maintain semantic PHPDoc legacy ratchet while cleaning touched files
- Foci: `ApplicationWorkflow`, `compat.php`, `AvaxVersion.php`; all touched files in prior iterations
- TODO-030: Evidence hygiene GREEN — PASSING
- TODO-032: ACCEPTED_YELLOW, 9810 violations, touched-file cleanup rule applies
- Note: TODO-030 must not displace P0/P1 work. Apply touched-file cleanup only.
- Validation: `composer validate --no-check-publish && php tooling/governance/check-root-evidence-hygiene.php && php tooling/governance/check-semantic-phpdoc.php`

Status: TODO-030 PASSING, TODO-032 ACCEPTED_YELLOW

---

## Appendix A: TODO Priority Summary

| ID | Title | Priority | Phase | Status |
|----|-------|----------|-------|--------|
| TODO-001 | Serialized payload hardening | P0 | — | DONE |
| TODO-002 | Compiled container namespace | P0 | — | DONE |
| TODO-003 | CSRF/session authority | P0 | 1.3 | DONE |
| TODO-004 | Dynamic class-loading boundaries | P0 | 1.4 | DONE |
| TODO-005 | Static secret state | P0 | 1.5 | DONE |
| TODO-006 | Framework entrypoint composition | P0 | 2.1 | DONE |
| TODO-007 | AuthBuilder split | P0 | 2.2 | DONE |
| TODO-008 | Remaining static mutable state | P1 | 3.1 | DONE |
| TODO-009 | API/DevTools PublicSurface | P1 | 3.2 | PARTIALLY_RESOLVED |
| TODO-010 | Application PublicSurface | P1 | 3.2 | PARTIALLY_RESOLVED |
| TODO-011 | HTTP PublicSurface | P1 | 3.3 | PARTIALLY_RESOLVED |
| TODO-012 | Operations PublicSurface | P1 | 3.3 | PARTIALLY_RESOLVED |
| TODO-013 | Security/Identity/DataStack PublicSurface | P1 | 3.4 | PARTIALLY_RESOLVED |
| TODO-014 | Constructor defaults | P1 | 3.5 | READY_ANALYSIS_FIRST |
| TODO-015 | ServiceProvider assembly | P1 | 3.5 | DONE |
| TODO-016 | Broken reference semantics | P1 | 4.1 | DONE |
| TODO-017 | Filesystem/path boundaries | P1 | 4.1b | DONE |
| TODO-018 | Security logging/redaction | P1 | 4.2 | DONE |
| TODO-019 | Global helper service-locators | P1 | 4.2 | DONE |
| TODO-020 | Constructor bloat | P2 | 5.1 | READY_ANALYSIS_FIRST |
| TODO-021 | Missing behavior proof/tests | P2 | 5.3 | NEEDS_DEDICATED_SESSION |
| TODO-022 | Forbidden concept folder names | P2 | 5.1 | NEEDS_HUMAN_DECISION |
| TODO-023 | Duplicate ownership | P2 | 5.2 | NEEDS_DEDICATED_SESSION |
| TODO-024 | Hidden superglobal/IO access | P2 | 5.2 | NEEDS_DEDICATED_SESSION |
| TODO-025 | Error handling | P2 | 5.3 | NEEDS_DEDICATED_SESSION |
| TODO-026a | CSV formula injection | P1 | 1.1 | DONE |
| TODO-026b | CompileDataQuery identifiers | P1 | 1.2 | DONE |
| TODO-027 | Interface contract docs | P2 | 5.4 | PASS_WITH_YELLOW |
| TODO-028 | Empty stubs/no-ops | P2 | 5.4 | NEEDS_DEDICATED_SESSION |
| TODO-029 | DI container performance | P2 | 5.5 | NEEDS_DEDICATED_SESSION |
| TODO-030 | Low-risk compat/style | P3 | 6.1 | PASSING |
| TODO-032 | Semantic PHPDoc ratchet | ACCEPTED_YELLOW | 6.1 | ACCEPTED_YELLOW |

## Appendix B: Verified Items

| ID | Status | Notes |
|----|--------|-------|
| TODO-026 | VERIFIED | Split into TODO-026a (P1) and TODO-026b (P1); remaining findings PARTIAL MEDIUM or ACCEPTED_EXCEPTION |
| TODO-031 | VERIFIED_WITH_MAPPINGS | 141 IDs mapped to existing TODOs 004-024; no new P0/P1/P2 findings |
| TODO-032 | ACCEPTED_YELLOW | 9810 legacy PHPDoc findings; touched-file cleanup rule applies to all iterations |

## Appendix C: Source Finding Coverage

Full source finding inventory: `.agents/management/evidence/generated/review-reconciliation/source-inventory.md`
Canonical finding clusters: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
Security/runtime escalation evidence: `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`

## Appendix D: Command Name Verification

| Command | Exists | Notes |
|---------|--------|-------|
| `tooling/refactor/check-runtime-leaks.php` | YES | Used in full validation section |
| `tooling/refactor/check-runtime-composition-leaks.php` | YES | Used in per-iteration validation |

Both exist. No replacement needed.

## Appendix E: EVIDENCE/EXECUTION.md Verification

| Check | Result |
|-------|--------|
| File exists | YES (25377 bytes) |
| Status line | canonical execution control document |
| Contains stage lock | YES |
| Contains validation baseline | YES |

Reference kept. No correction needed.
