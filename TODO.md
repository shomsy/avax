# AvaX Remediation Execution Board

Status: REMEDIATION_ACTIVE — Round 002 in progress
Execution control: `EVIDENCE/EXECUTION.md` is the canonical execution control document.
Remediation backlog: `fix-this.md` is the canonical review reconciliation backlog (34 TODOs, 7 P0).
Evidence root: `.agents/management/evidence/generated/review-reconciliation/`

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
| P0 BLOCKER | 7 (2 completed, 5 remaining) |
| P1 HIGH | 14 |
| P2 MEDIUM | 9 |
| P3 LOW | 1 |
| VERIFIED | 2 |
| ACCEPTED_YELLOW | 1 |
| NEEDS_VERIFICATION | 0 |

CURRENT_TRUTH.md is stale (GREEN claims do not match current fix-this.md RED/BLOCKED status). Phase 0 will reconcile it.

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
| Agent A | CSV/Data export security | TODO-026a | TODO-018 slice later | YES |
| Agent B | DataStack SQL safety | TODO-026b | DataStack query/test hardening later | YES |
| Agent C | Validation cleanup/runtime refs | TODO-016 | TODO-017 later | YES |
| Agent D | HTTP session security | TODO-003 | HTTP PublicSurface slice later | LIMITED |
| Agent R | Review-only | Round 002 review | all execution branches | N/A |
| Coordinator | Integration | branch setup + sequential merge | main validation | N/A |

## Current Round — Parallel Round 002

Goal:
Close small/high-value security and validation items while keeping integration controlled.

Active branches/worktrees:

| Agent | Task | Branch | Worktree | Type | Merge Order |
|-------|------|--------|----------|------|-------------|
| Agent A | TODO-026a | cleanup/todo-026a-csv-formula-injection | ../avax-todo-026a | remediation | 1 |
| Agent B | TODO-026b | cleanup/todo-026b-compile-data-query-identifiers | ../avax-todo-026b | remediation | 2 |
| Agent C | TODO-016 | cleanup/todo-016-broken-reference-semantics | ../avax-todo-016 | remediation | 3 |
| Agent D | TODO-003 | security/todo-003-csrf-session-authority | ../avax-todo-003 | analysis-first remediation | 4 |
| Agent R | Round review | review/parallel-round-002 | ../avax-round-002-review | review-only | N/A |

## Dependency and Conflict Notes

- TODO-026a and TODO-026b are parallel-safe because CSV rendering and DataStack SQL compilation are separate file sets.
- TODO-016 is parallel-safe if it only touches known broken reference semantics and does not modify HTTP session/security ownership.
- TODO-003 is analysis-first and may become sequential-only if it overlaps HTTP/PublicSurface or global helper cleanup.
- TODO-004, TODO-005, TODO-006, TODO-007 are not part of Round 002.
- TODO-004 should wait until TODO-026a/TODO-026b/TODO-016/TODO-003 are reviewed.
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
- Validation: `vendor/bin/phpunit --filter "CsvFormat|CsvFormatter|CsvInjection" --no-coverage`
- Evidence: `.agents/management/evidence/generated/todo-026-sql-csv-verification/confirmed-findings.md`
- Status: READY_FOR_ROUND_002 — Agent A lane

### Iteration 1.2 — CompileDataQuery Identifier Interpolation Hardening
- **TODO-026b**: Sanitize identifier interpolation in CompileDataQuery SQL compilation
- Foci: `components/DataStack/Persistence/System/Flows/CompileDataQuery/CompileDataQuery.php`
- Negative tests required: malicious identifiers rejected or escaped
- Validation: `vendor/bin/phpunit --filter "CompileDataQuery|SqlInjection" --no-coverage`
- Evidence: `.agents/management/evidence/generated/todo-026-sql-csv-verification/confirmed-findings.md`
- Status: READY_FOR_ROUND_002 — Agent B lane

### Iteration 1.3 — CSRF/Session Authority
- **TODO-003**: Unify CSRF/session authority (SessionScope, NativeSessionStore, CsrfToken, CsrfTokens, CsrfTokenGenerator)
- Foci: `components/HTTP/Session`, `components/HTTP/Security`
- Tests: negative CSRF validation, token rotation, session lifecycle, duplicate helper load
- Validation: `vendor/bin/phpunit --filter "Csrf|Session" --no-coverage && php tooling/refactor/check-runtime-composition-leaks.php`
- Evidence: `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`
- Status: READY_FOR_ANALYSIS_FIRST — Agent D lane

### Iteration 1.4 — Dynamic Class-Loading Boundaries
- **TODO-004**: Close dynamic class-loading execution paths (QueueWorker, RunRecoveryAction, RunFallbackAction, migration/seeder, Container class_exists+new)
- Foci: `components/Operations/Queue`, `framework/System/Capabilities/FailureBoundary`, `components/Application/Container`
- Tests: negative tests for unknown class, wrong interface, payload class injection
- Validation: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-runtime-composition-leaks.php && vendor/bin/phpunit --filter "QueueWorker|FailureBoundary|Migration|Seeder" --no-coverage`
- Evidence: `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`

Status: PENDING — wait for Round 002 review

### Iteration 1.5 — Worker-Unsafe Static Secret State
- **TODO-005**: Remove worker-unsafe static secret/security runtime state (Secrets, Diagnostics, FailureBoundary, ExternalState, ResourceGovernor, GlobalEventListenerState)
- Foci: `components/Security/Secrets`, `components/Operations/Events`, `framework/System/Capabilities/RuntimeSafety`, `framework/System/Capabilities/ExternalState`
- Tests: long-lived worker two-request leakage tests, reset tests, secret overwrite/isolation
- Validation: `php tooling/refactor/check-runtime-composition-leaks.php && vendor/bin/phpunit --filter "Secrets|Diagnostics|FailureBoundary|ExternalState|StateReset" --no-coverage`
- Evidence: `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`

Status: PENDING — wait for TODO-004

---

## Phase 2: P0 Architecture Blockers

Goal: Close architecture-level P0 items blocking further remediation.

### Iteration 2.1 — Framework Entrypoint Composition
- **TODO-006**: Move framework public entrypoint object-graph assembly out of runtime
- Foci: `framework/System/PublicSurface`, `framework/System/Flows/RunApplication`, `framework/System/Flows/CreateApplication`
- Tests: Avax::create, BootDsl::create, App::handle, RunApplication pipeline regression
- Validation: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-runtime-composition-leaks.php && php tooling/refactor/check-public-surface.php`
- Evidence: `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`

Status: PENDING — requires separate architecture-focused round

### Iteration 2.2 — AuthBuilder Split
- **TODO-007**: Split AuthBuilder into bounded configuration responsibilities
- Foci: `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php`
- Note: first slice already extracted (AssembleAuthIdentityGraph, commit 8f9d0ff5c). Remaining: 889 lines, Phase 4 OAuth/OIDC/Federation extraction.
- Validation: `php tooling/governance/check-large-unit-thresholds.php && vendor/bin/phpunit --filter "AuthBuilder|Auth" --no-coverage`
- Evidence: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`

Status: PENDING — requires separate architecture-focused round

---

## Phase 3: P1 Static State + Public Surface Remediation

Goal: Remove remaining static mutable state and move PublicSurface construction to Configuration.

### Iteration 3.1 — Remaining Static Mutable State
- **TODO-008**: Retire remaining static mutable PublicSurface/runtime state by ownership slice (21 units)
- Foci: `DeveloperTools/Diagnostics`, `API/SchemaGeneration`, `Application/Cache`, `Application/Storage`, `Application/Pipeline`, etc.
- Tests: two-request leak tests per touched owner
- Validation: `php tooling/refactor/check-runtime-composition-leaks.php && vendor/bin/phpunit --no-coverage`

Status: PENDING

### Iteration 3.2 — PublicSurface Construction (Batch A: API + DevTools + Application)
- **TODO-009**: Reduce API/DeveloperTools PublicSurface construction pressure
- **TODO-010**: Reduce Application PublicSurface construction pressure
- Foci: `components/API/*`, `components/DeveloperTools/*`, `components/Application/*`
- Validation: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`

Status: PENDING

### Iteration 3.3 — PublicSurface Construction (Batch B: HTTP + Operations)
- **TODO-011**: Reduce HTTP PublicSurface construction pressure
- **TODO-012**: Reduce Operations PublicSurface construction pressure
- Foci: `components/HTTP/*`, `components/Operations/*`
- Validation: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php && vendor/bin/phpunit --filter "HTTP|Http" --no-coverage`

Status: PENDING

### Iteration 3.4 — PublicSurface Construction (Batch C: Security + Identity + DataStack)
- **TODO-013**: Reduce Security/Identity/DataStack PublicSurface construction pressure
- Foci: `components/Security/*`, `components/Identity/*`, `components/DataStack/*`
- Validation: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`

Status: PENDING

### Iteration 3.5 — Constructor Defaults + ServiceProviders
- **TODO-014**: Move constructor default dependency creation into approved Configuration owners (53 units)
- **TODO-015**: Add missing ServiceProvider assembly owners (25 units)
- Foci: CROSS_CUTTING — per-component-owner approach
- Validation: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-service-provider-coverage.php && vendor/bin/phpunit --filter "ServiceProvider|Provider" --no-coverage`

Status: PENDING

---

## Phase 4: P1 Cross-Cutting Remediation

Goal: Close cross-cutting P1 items that span multiple components.

### Iteration 4.1 — Broken Refs + Filesystem Paths
- **TODO-016**: Fix broken reference semantics in public/runtime namespaces
- **TODO-017**: Route raw filesystem/path operations through approved first-party boundaries
- Foci: `components/Operations/ApplicationWorkflow`, `components/HTTP`, `framework/System/Configuration/Builders`, `framework/System/Capabilities/FailureBoundary`
- Validation: `php tooling/refactor/check-broken-reference-semantics.php && composer dump-autoload -o && php tooling/refactor/check-public-surface.php`
- Status: READY_FOR_ROUND_002 — Agent C lane (TODO-016 only)

### Iteration 4.2 — Security Logging + Global Helpers
- **TODO-018**: Harden security logging, redaction, and secret parameter handling
- **TODO-019**: Replace global helper service-locator shortcuts with testable boundaries
- Foci: `tests/Unit/Components/Security/Cryptography`, `components/Application/Text`, `HTTP/Security`, `Security/RequestSigning`
- Validation: `vendor/bin/phpunit --filter "Redaction|Secrets|Cryptography|RequestSignature|Logging|csrf" --no-coverage && php tooling/refactor/check-runtime-composition-leaks.php`

Status: PENDING

---

## Phase 5: P2 Architecture & Maintainability

Goal: Improve architecture, test coverage, and maintainability.

### Iteration 5.1 — Constructor Bloat + Forbidden Folders
- **TODO-020**: Classify and reduce constructor bloat by owner (41 units)
- **TODO-022**: Resolve forbidden concept folder names through approved governance decisions
- Foci: `components/Identity/Auth`, `components/DataStack`, all forbidden-named folders
- Validation: `php tooling/refactor/check-constructor-bloat.php && php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-component-suite-structure.php && php tooling/refactor/check-namespace-drift.php`

Status: PENDING

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
- Validation: `php tooling/governance/check-semantic-phpdoc.php && vendor/bin/phpunit --filter "ResetApplicationState|ShutdownRuntime|ConfigureRuntime" --no-coverage`

Status: PENDING

### Iteration 5.5 — DI Container Performance
- **TODO-029**: Measure and reduce DI/container object graph performance pressure
- Foci: DIContainer, runtime paths with repeated short-lived object graphs
- Note: Measure first; change only after benchmark/test proof.
- Validation: `php tooling/refactor/check-direct-instantiation.php && php tooling/governance/check-large-unit-thresholds.php`

Status: PENDING

---

## Phase 6: P3 + Accepted Debt

Goal: Close low-risk items and maintain accepted-yellow debt.

### Iteration 6.1 — Low-Risk Cleanup + PHPDoc Ratchet
- **TODO-030**: Close low-risk compat/version/style cleanup with evidence
- **TODO-032**: Maintain semantic PHPDoc legacy ratchet while cleaning touched files
- Foci: `ApplicationWorkflow`, `compat.php`, `AvaxVersion.php`; all touched files in prior iterations
- Note: TODO-030 must not displace P0/P1 work. Apply touched-file cleanup only.
- Validation: `composer validate --no-check-publish && php tooling/governance/check-root-evidence-hygiene.php && php tooling/governance/check-semantic-phpdoc.php`

Status: PENDING

---

## Appendix A: TODO Priority Summary

| ID | Title | Priority | Phase | Status |
|----|-------|----------|-------|--------|
| TODO-001 | Serialized payload hardening | P0 | — | DONE |
| TODO-002 | Compiled container namespace | P0 | — | DONE |
| TODO-003 | CSRF/session authority | P0 | 1.3 | READY_FOR_ANALYSIS |
| TODO-004 | Dynamic class-loading boundaries | P0 | 1.4 | PENDING |
| TODO-005 | Static secret state | P0 | 1.5 | PENDING |
| TODO-006 | Framework entrypoint composition | P0 | 2.1 | PENDING |
| TODO-007 | AuthBuilder split | P0 | 2.2 | PENDING |
| TODO-008 | Remaining static mutable state | P1 | 3.1 | PENDING |
| TODO-009 | API/DevTools PublicSurface | P1 | 3.2 | PENDING |
| TODO-010 | Application PublicSurface | P1 | 3.2 | PENDING |
| TODO-011 | HTTP PublicSurface | P1 | 3.3 | PENDING |
| TODO-012 | Operations PublicSurface | P1 | 3.3 | PENDING |
| TODO-013 | Security/Identity/DataStack PublicSurface | P1 | 3.4 | PENDING |
| TODO-014 | Constructor defaults | P1 | 3.5 | PENDING |
| TODO-015 | ServiceProvider assembly | P1 | 3.5 | PENDING |
| TODO-016 | Broken reference semantics | P1 | 4.1 | READY_FOR_ROUND_002 |
| TODO-017 | Filesystem/path boundaries | P1 | 4.1 | PENDING |
| TODO-018 | Security logging/redaction | P1 | 4.2 | PENDING |
| TODO-019 | Global helper service-locators | P1 | 4.2 | PENDING |
| TODO-020 | Constructor bloat | P2 | 5.1 | PENDING |
| TODO-021 | Missing behavior proof/tests | P2 | 5.3 | PENDING |
| TODO-022 | Forbidden concept folder names | P2 | 5.1 | PENDING |
| TODO-023 | Duplicate ownership | P2 | 5.2 | PENDING |
| TODO-024 | Hidden superglobal/IO access | P2 | 5.2 | PENDING |
| TODO-025 | Error handling | P2 | 5.3 | PENDING |
| TODO-026a | CSV formula injection | P1 | 1.1 | READY_FOR_ROUND_002 |
| TODO-026b | CompileDataQuery identifiers | P1 | 1.2 | READY_FOR_ROUND_002 |
| TODO-027 | Interface contract docs | P2 | 5.4 | PENDING |
| TODO-028 | Empty stubs/no-ops | P2 | 5.4 | PENDING |
| TODO-029 | DI container performance | P2 | 5.5 | PENDING |
| TODO-030 | Low-risk compat/style | P3 | 6.1 | PENDING |
| TODO-032 | Semantic PHPDoc ratchet | ACCEPTED_YELLOW | 6.1 | PENDING |

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
