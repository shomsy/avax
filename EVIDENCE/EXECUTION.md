# AvaX Execution Control

Status: canonical execution control document  
Purpose: prevent roadmap drift, feature jumping, incomplete component work, and AI-agent overreach.  
Scope: AvaX repository execution, refactoring, stabilization, production readiness, V1/V2/V3 roadmap delivery.

---

## 0. Source Of Truth

Before any work starts, read these documents in this order:

```text
1. CURRENT_TRUTH.md
2. EXECUTION.md
3. TODO.md
4. EVIDENCE/master-plan/avax-master-development-plan-v1.md
5. EVIDENCE/master-plan/avax-master-plan-v2-pucamo-u-metu.md
6. EVIDENCE/master-plan/avax-v3-executable-system-design-framework-plan.md
7. .agents/how-to/*.md
```

If the repository has a root `AGENTS.md`, its execution rules still apply.

If there is a conflict, use this precedence:

```text
1. CURRENT_TRUTH.md
2. AGENTS.md
3. EXECUTION.md
4. TODO.md
5. .agents/how-to/*.md
6. master roadmap documents
7. older review/archive files
```

Old review files are not current truth unless CURRENT_TRUTH.md explicitly says they are.

---

## 1. Core Execution Principle

AvaX must not be developed by jumping across the roadmap.

AvaX must be developed by locked stages.

Only one stage may be active at a time.

A stage may not be marked GREEN unless implementation, validation, evidence, documentation, and TODO state agree.

Roadmap ambition is allowed.

Implementation chaos is forbidden.

---

## 2. Hard Execution Law

V1 must be green before V2 or V3 implementation.

V4 implementation must not begin before V1 Kernel Green, V2 platform baseline, and V3 SystemDesignKit are all GREEN.

Do not implement GraphQL, integration ports, SystemDesign suite, background supervisor, transcoding gateway,
recommendation gateway, or system-design simulations while any of these are RED:

```text
taxonomy
autoload
namespace integrity
tests
PHPStan/Psalm
runtime safety
component completion
public surface integrity
```

Planning may continue.

Documentation may continue.

Architecture notes may continue.

Implementation waits for Kernel Green.

No agent may bypass this rule by calling feature work:

```text
preparation
scaffolding
harmless foundation
future-proofing
small setup
temporary placeholder
```

If it creates production code for V2/V3 behavior, it waits for Kernel Green.

---

## 3. Kernel Green Definition

Kernel Green means all of these are true:

```text
[ ] CURRENT_TRUTH.md is current and trusted.
[ ] Final project tree is frozen and documented.
[ ] Component taxonomy is canonical.
[ ] No duplicate owner remains.
[ ] Composer autoload is clean.
[ ] Namespaces match final ownership.
[ ] Tests load and target canonical classes.
[ ] Full canonical test suite passes or failures are explicitly classified.
[ ] PHPStan/Psalm are green or honestly baselined.
[ ] Runtime safety is proven.
[ ] Request scope opens and closes correctly.
[ ] State reset runs after request/job.
[ ] PublicSurface does not leak internals.
[ ] Component completion rules are enforceable.
[ ] Golden Path App works through public APIs only.
[ ] Production readiness baseline is documented.
```

If any item is RED, V2/V3 implementation is forbidden.

---

## Current Execution Lock

V1 Kernel Green: PROVEN

V2 Platform Baseline: CLOSED / GREEN (all 72 components complete)

V3 Implementation: CLOSED / GREEN (SystemDesignKit promoted to components/SystemDesign)

V4 Product Runtime: CLOSED / GREEN (V4-01 through V4-17 complete)

V5 Internal Convergence: CLOSED / GREEN (V5-00 through V5-23 complete)

V5.5 Benchmark Proof: CLOSED / GREEN (V5.5-00 through V5.5-12 complete)

V5.6 Failure Boundary: CLOSED / FULL GREEN (Y1-Y7 complete, 90 tests, 4 gates GREEN)

V5.7 Events DSL: CLOSED / FULL GREEN (V5.7-00 through V5.7-13 complete, 10 event gates PASS, 8069 tests GREEN, PHPStan 0 errors, real dogfooding proven, CQRS projection proven, event-history reference proof)

V5.8 Database Lifecycle Events: CLOSED / FULL GREEN (V5.8-01 through V5.8-14 complete, 8231 tests GREEN, PHPStan 0 errors, all 8 database lifecycle gates PASS, EntityPersister/QueryOrchestrator/Transaction wired)

V5.8.5 AvaX Full Enterprise Cleanup: CLOSED / FULL GREEN (all cleanup phases A through M complete, 22/22 component gates PASS, PHPStan 0 errors, health checks canonical, ServiceProviders created, truth files reconciled)

V5.8.6 HTTP Response Layer Convergence: CLOSED / FULL GREEN (ResponseServiceProvider converged, CreateHttpResponse + Responses + ResponseFactoryInterface alias registered, 13 provider tests GREEN, pre-existing errors classified, GoldenPathRuntime responseFactory bug fixed)

V5.8.7 Full Suite Baseline Restoration: CLOSED / YELLOW (8351 tests GREEN, but PHPStan 308 errors existed — "FULL GREEN" claim was dishonest)

V5.8.8 PHPStan, Runtime Gate & Truth Integrity Closure: CLOSED / YELLOW_WITH_EXACT_BLOCKERS (55/308 PHPStan errors fixed → 253 remaining, PHPUnit 8351 tests GREEN, AuthBuilder ~170 errors remain, runtime gate FAIL, truth files updated)

V5.8.9 AuthBuilder Constructor Drift & Runtime Gate Closure: CLOSED / GREEN (190 PHPStan errors fixed → 63 remaining, runtime composition leaks 3 → 0, AuthBuilder constructor drift ~184 → 0, GraphQLSchema assembly 0 findings, PHPUnit 8351 tests GREEN, all gates PASS)

Current Plan Lock: GREEN (63 pre-existing PHPStan errors, out of V5.8.9 scope)

Active stage: V5.8.9 AuthBuilder Constructor Drift & Runtime Gate Closure — COMPLETE / GREEN

Next allowed stage: V5.9 Boot DSL — READY (63 pre-existing PHPStan errors may be baselined or cleaned up first)

V5.9 Boot DSL prerequisites:
1. AuthBuilder constructor drift resolved — DONE (0 errors)
2. Runtime composition leaks in DispatchConfiguredRoute resolved — DONE (0 findings)
3. GraphQLSchema runtime assembly verified — DONE (0 findings)
4. PHPStan cleanup — 63 pre-existing errors remain (array types, mixed variables, typePerfect, test assertions)

Evidence: EVIDENCE/hardening/43-v5-8-9-preflight.md through EVIDENCE/hardening/53-v5-8-9-final-summary.md

---

## 4. Stage Discipline

Each stage must have:

```text
1. stage name
2. goal
3. allowed work
4. forbidden work
5. allowed file scope
6. forbidden file scope
7. required validation commands
8. expected artifacts
9. done definition
10. stop conditions
11. final report
```

An agent must not execute a stage that is not active.

An agent must not silently expand stage scope.

An agent must not combine stages unless EXECUTION.md explicitly allows it.

---

## 5. Active Stage Lock

The current active stage is:

```text
None — V5.8.7 Full Suite Baseline Restoration COMPLETE / FULL GREEN
```

Last completed stage:

```text
V5.8.7 Full Suite Baseline Restoration — FULL GREEN (8351 tests, 24012 assertions, 0 errors, 0 failures, all gates GREEN)
```

Current Plan Lock: FULL GREEN

### V2 API Engine Closure

The V2 API Engine is now CLOSED with canonical naming. Evidence:

```text
EVIDENCE/recovery-reports/v2-api-engine-closure/v2-api-engine-closure-report.md
```

Key achievements:

- `ApiSurface` → `ApiBlueprint` (source of truth naming)
- `BuildApiSurface` → `DefineApiBlueprint`
- `ValidateApiSurface` → `VerifyApiBlueprint`
- `DetectApiCompatibilityChanges` → `AnalyzeApiEvolution`
- `GenerateApiCompatibilityChecks` → `VerifyApiCompatibility`
- `GenerateOpenApiDocument` → `ExportOpenApiDocument`
- `GenerateApiDocumentation/` → `Documentation/`

Validation passed: composer (6655 classes), phpunit (599 tests), phpstan, runtime doctor.

### Stage 08 Goal

Achieve 100% green PHPStan analysis for framework, components, and tests (or honestly baseline intentional debt).

### Stage 08 Allowed Work

[x] Fix remaining PHPStan warnings in tests.
[x] Fix any remaining type-hint issues in framework/components.
[x] Ensure all return types are specified.
[x] Ensure all property types are specified.
[x] Resolve "mixed" variable warnings.
[x] Record a Stage 08 report.

### Stage 09 Goal

Prove V1 Kernel stability through final behavioral validation and runtime doctor green.

### Stage 09 Allowed Work

[ ] Finalize canonical class map verification.
[ ] Ensure all core components pass `runtime:doctor`.
[ ] Verify "Golden Path" (boot, route, request, response) works through public API.
[ ] Record a Stage 09 report.

### Stage 08 Forbidden Work

[ ] No V2 implementation.
[ ] No V3 implementation.
[ ] No feature behavior changes that aren't required for type safety.

### Stage 08 Validation

```bash
vendor/bin/phpstan analyse framework components tests --memory-limit=1G
```

### Stage 08 Done Definition

[ ] PHPStan returns 0 errors (including baseline).
[ ] No "always true" assertions in tests.
[ ] Final report declares GREEN.

### Stage 04 Forbidden Work

```text
[ ] No V2 implementation.
[ ] No V3 implementation.
[ ] No V4 implementation.
[ ] No new feature behavior beyond documenting/classifying completion state.
[ ] No placeholder classes.
[ ] No skeleton classes to make a component look complete.
[ ] No broad component implementation while completing the matrix.
[ ] No optimistic production-readiness update.
```

### Stage 04 Validation

```bash
composer validate --no-check-publish
composer dump-autoload -o
php tooling/governance/check-stage-lock.php
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php
```

If a governance command does not exist, record it as PLANNED / NOT IMPLEMENTED.

### Stage 04 Done Definition

```text
[ ] Component completion matrix is current.
[ ] Every current component has status and evidence.
[ ] No component is marked complete only because folders exist.
[ ] Placeholder/skeleton risks are named.
[ ] Required checkers pass or blockers are recorded.
[ ] Final report declares GREEN/YELLOW/RED.
[ ] Next stage is explicitly named.
```

### Stage 04 Stop Condition

Stop if marking a component complete would require implementing new production behavior outside matrix verification.

---

## 6. Stage Map

The canonical sequence is:

```text
Stage 00: Current Truth Lock
Stage 01: Final Project Tree Freeze
Stage 02: Taxonomy Integrity Green
Stage 03: API Classification and Evolution Rules
Stage 04: Component Completion
Stage 05: Canonical Class Map
Stage 06: Autoload and Namespace Repair
Stage 07: Test Layer Repair
Stage 08: Static Analysis Green
Stage 09: AvaX Kernel Green
Stage 10: Production Readiness Baseline
Stage 11: Golden Path App
Stage 12: Public API and Compatibility Governance
Stage 13: Extension and Plugin Architecture
Stage 14: Benchmark and Performance Budget Suite
Stage 15: Observability Contract
Stage 16: Security Threat Model
Stage 17: Failure Simulation and Runtime Resilience
Stage 18: Package Split Readiness
Stage 19: Release, Upgrade and Migration Policy
Stage 20: System Design Kit
Stage 21: Reference Architectures
Stage 22: System Design Example Applications
Stage 23: Final Documentation and Positioning
```

Stages 20 through 23 remain planning-only until V1 Kernel Green and V2 platform baseline are at least YELLOW/GREEN.

---

## 7. Per-Stage Execution Template

Every stage execution must use this template.

```md
# Stage Report: <stage number and name>

## Goal

<one clear goal>

## Scope

### Allowed

- ...

### Forbidden

- ...

## Files Changed

- ...

## Files Intentionally Not Touched

- ...

## Validation Commands

```bash
...
```

## Validation Result

```text
GREEN / YELLOW / RED
```

## Evidence

- command outputs
- generated reports
- changed files
- relevant paths

## Remaining Risks

- ...

## Next Allowed Stage

<stage number and name>
```

---

## 8. Validation Command Baseline

Do not run all commands blindly on every stage.

Use commands appropriate to the stage.

Full production-readiness baseline:

```bash
git status --short
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit
vendor/bin/phpstan analyse framework components tests
vendor/bin/psalm

php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php
php tooling/refactor/check-docs-mirror.php
php tooling/refactor/check-forbidden-folders.php
php tooling/refactor/check-vendor-monolith-isolation.php
php tooling/refactor/check-compat-aliases.php
```

If a command does not exist, record it as missing infrastructure.

Do not pretend it passed.

---

## 9. Component Design Governance

All component design and implementation must comply with every applicable document in:

```text
.agents/how-to/*.md
```

The agent must discover these files before implementation.

The agent must not cherry-pick rules.

The agent must especially enforce:

```text
folder says flow or capability
unit says responsibility
function says exact action
PublicSurface receives
Flows execute
Capabilities power
Configuration assembles
Foundation supports
no generic Manager/Service/Helper/Util/Support naming
no placeholder classes
no describeResponsibility-only classes
no component marked complete without proof
```

If `.agents/how-to/how-to-design-components.md` exists, it is mandatory for all component work.

---

## 10. Component Completion Gate

No component may be marked complete only because folders exist.

A platform engine is complete only when it has:

```text
1. public contract
2. internal runtime behavior
3. fake/local adapter
4. production adapter boundary
5. configuration schema
6. health/doctor check
7. failure model
8. retry/timeout/circuit/backoff policy when external I/O exists
9. observability events
10. contract tests
11. failure tests
12. runtime-safety rules
13. example usage
14. documentation
15. operator diagnostics
```

If these are not present, the component must be marked:

```text
draft
experimental
internal
planned
partial
```

It must not be marked production-ready.

---

## 11. V2 Implementation Lock

V2 Platform Baseline is CLOSED / GREEN. V1 Kernel Green has been proven. All 72 components are production-ready.

V2 platform components promoted from LOCKED_NON_V1 to COMPLETE:

```text
Realtime:          ConnectClient, DisconnectClient, BroadcastToChannel, SubscribeToChannel, HandleRealtimeMessage
RuntimeSupervision: Supervisor, WorkerLifecycle, WorkerRestart, ProcessRegistry, Health, MonitorSupervisor
MemoryLifecycle:   MemoryBudget, MemoryTracker, AllocateMemory, ReleaseMemory, CheckMemoryHealth, RunGarbageCollection
Delivery:          BuildManifest, CompileApplication, CompileContainer, CompileRoutes, RunSmokeChecks, VerifyRelease
```

Full V2 inventory:

```text
API Contract:     ApiBlueprint, OpenAPI, GraphQL
API/Surface:      REST, JSON:API, Webhooks, RPC
Integration:      ObjectStorage
Resilience:       Timeout, Bulkhead, DeadLetter, Outbox, Lock, Lease, Backpressure, LoadShedding
Observability:    Logging, Telemetry, Redaction, Tracing
RuntimeSupervision: Supervisor, WorkerLifecycle, WorkerRestart, Health
MessageBus:       Command, Query, Event, Transactional dispatch
Delivery:         Build, Compile, SmokeChecks, Evidence, Rollback
Realtime:         ConnectClient, DisconnectClient, BroadcastToChannel, SubscribeToChannel
MemoryLifecycle:  AllocateMemory, ReleaseMemory, CheckMemoryHealth, RunGarbageCollection
Tasks:            TaskRunner, TaskQueue, TaskScheduler, TaskRetry
Filesystem:       ReadFile, WriteFile, DeleteFile, ListDirectory
DevX:             CodeGeneration, DumpDebugger, Testing, Dx
```

Total components: 72 complete (65 V1 + 7 V2 platform)
LOCKED_NON_V1 remaining: 0

---

## 12. V3 Implementation Lock

V3 work is locked until:

```text
[ ] V1 Kernel Green.
[ ] V2 platform baseline is at least YELLOW/GREEN.
[ ] labs/SystemDesignKit MVP scope is approved.
```

V3 planning may continue.

V3 production implementation is forbidden until the lock is lifted.

V3 must start in:

```text
labs/SystemDesignKit/
```

Promotion to:

```text
components/SystemDesign/
```

requires proof.

---

## 13. TODO.md Relationship

TODO.md is not the roadmap.

TODO.md is the active execution queue.

TODO.md must:

```text
[ ] point to EXECUTION.md
[ ] list only current and next actionable stages
[ ] keep V2/V3 as locked planning sections
[ ] require .agents/how-to/*.md compliance
[ ] record stage status
[ ] never replace CURRENT_TRUTH.md
```

If TODO.md and EXECUTION.md disagree, EXECUTION.md wins.

---

## 14. Agent Output Contract

Every agent execution must end with:

```text
Stage:
Status:
Files changed:
Validation commands:
Validation summary:
Remaining risks:
Next allowed action:
```

No raw conversational summary is enough.

No “done” claim is accepted without evidence.

---

## 15. Final Law

AvaX execution is stage-locked.

V1 proves the kernel.

V2 implements platform engines.

V3 validates large-system behavior.

Planning can be broad.

Implementation must be narrow.

No proof, no progress.

---

## 16. V4 Implementation Lock

V4 Product Runtime & Enterprise Muscle — COMPLETE / GREEN

V4-00 Integrity Lock & Stage Definition: COMPLETE
V4-01 Runtime App Layer: COMPLETE / GREEN (main branch)
V4-02 ReactPHP Runtime Foundation: COMPLETE / GREEN (main branch)
V4-03 Warm Worker Safety: COMPLETE / GREEN (main branch) — full hardening
V4-04 Developer Experience: COMPLETE / GREEN (main branch) — config as code, doctor, route cache plan
V4-05 Data Platform Productization: GREEN
V4-06 Storage Platform: GREEN
V4-07 Database Muscle: GREEN
V4-08 Queue & Worker Runtime: GREEN
V4-09 Reliability Engine: GREEN
V4-10 Messaging & Consistency: GREEN
V4-11 Observability & Telemetry: GREEN
V4-12 Security & Policy Runtime: COMPLETE / GREEN
V4-13 System Design Runtime Kit: COMPLETE / GREEN
V4-14 Runtime Doctor & Control Plane: COMPLETE / GREEN
V4-15 Reference Applications: COMPLETE / GREEN
V4-16 Benchmarks & Production Proof: COMPLETE / GREEN
V4-17 Optional Runtime Adapters: COMPLETE / GREEN

Note: V5 dogfooding/performance convergence is planned separately.
V4 production-ready: GREEN.

V4-03 validation evidence (2026-05-09):
- PHPUnit V4-03: 44 tests, 104 assertions — GREEN
- PHPUnit V4-02/03 baseline: 20 tests, 46 assertions — GREEN
- Full suite: 1528 tests, 5504 assertions, 0 failures, 0 errors, 0 skipped
- Total new: 64 warm worker safety tests
- 0 skipped tests
- PHPStan: 0 errors (framework, components, tests, labs/SystemDesignKit)
- Architecture checks: 7/7 PASS
- Route cache: NOT implemented, planned for V4-04

V4-04 validation evidence (2026-05-09):
- PHPUnit V4-04 unit: 13 tests, 28 assertions — GREEN
- PHPUnit V4-04 composition: 9 tests, 342 assertions — GREEN
- PHPStan: 0 errors (framework, components, tests)
- Architecture checks: GREEN
- Route cache plan: EVIDENCE/route-cache-plan.md
- Route cache proof slice: CacheRouteTable, LoadCachedRoutes, route:cache, route:clear commands

V4 master plan: `EVIDENCE/.PLANS/V4_PRODUCT_RUNTIME_AND_ENTERPRISE_MUSCLE.md`

V4 execution rules:

```text
V4-01 through V4-17 are all COMPLETE / GREEN.
V4 production-ready: GREEN.
Next major phase: V5 dogfooding / performance convergence.
```

Next allowed action (2026-05-10):
- V5 dogfooding / performance convergence (next planned major phase)
- Release-grade merge of main into master when approved

V4 North Star:

```text
Make AvaX usable as a real framework for building production-grade, system-design-grade applications.
```

V4 global acceptance: 30 criteria (see V4 master plan, section 24) + 8 composition proof requirements (section 28.8) +
14 framework maturity gates (sections 27 + 28 + 29).

Canonical source rule:
V4_PRODUCT_RUNTIME_AND_ENTERPRISE_MUSCLE.md is the canonical V4 plan.
v4-intelligence-governance-observability-plan.md is source material only (merged as section 29).
No parallel V4 plans. All future V4 changes update the canonical file.

Route Cache Planning Note (V4-02 / V4-04):

```text
Route cache proof slice IS implemented in V4-04:
  - CacheRouteTable capability — compiles and writes route cache
  - LoadCachedRoutes capability — loads and validates cached routes
  - route:cache CLI command — generates cache with sample routes
  - route:clear CLI command — clears cached route files
  - Route cache plan: EVIDENCE/route-cache-plan.md

Full route cache integration requires V4-05+:
  - ApplicationBuilder detects and uses cached routes when available
  - Route cache invalidation on file change detection
  - Route cache warm-up hook for worker boot
  - Performance benchmarks proving cache benefit

When fully implemented, route cache must:
  - Support warm worker safety (cache invalidation between deployments)
  - Work with ReactPHP, RoadRunner, Swoole, FrankenPHP runtimes
  - Not bypass existing MatchHttpRoute component
  - Include cache:warm, cache:clear, cache:status commands
  - Prove performance improvement with benchmarks
  - Not introduce stale route matching in long-lived workers
```

---

## 17. V4 Branch Policy

V4 development follows a strict branch strategy.

### 17.1 Branch Roles

```text
master = stable protected branch.
         Holds the current clean baseline, including the official V4 plan.
         Receives V4 only when V4 is production-ready.

main   = active V4 development / integration branch.
         Must be updated by merging master.
         V4 development happens on main through stage branches.
```

### 17.2 Branch Rules

```text
- master keeps the current clean baseline, including the official V4 plan.
- main must be updated by merging master.
- V4 development happens on main through stage branches.
- No direct feature work on master.
- No V4 implementation directly on master.
- master receives V4 only when V4 is production-ready.
- Every V4 stage branch starts from main.
- Every merge into main must pass full validation.
- Every merge into master must be a release-grade merge.
```

### 17.3 Feature Branch Naming

```text
v4/01-runtime-app-layer
v4/02-reactphp-runtime
v4/03-warm-worker-safety
v4/04-developer-experience
v4/05-data-platform-productization
v4/06-storage-platform
v4/07-database-muscle
v4/08-queue-worker-runtime
v4/09-reliability-engine
v4/10-messaging-consistency
v4/11-observability-telemetry
v4/12-security-policy-runtime
v4/13-system-design-runtime-kit
v4/14-runtime-doctor-control-plane
v4/15-reference-applications
v4/16-benchmarks-production-proof
v4/17-optional-runtime-adapters
```

### 17.4 Required Validation Before Merging Any V4 Branch into main

```bash
composer validate --no-check-publish
composer dump-autoload -o
composer test:full
vendor/bin/phpstan analyse framework components tests labs/SystemDesignKit --memory-limit=1G
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php
php tooling/refactor/check-component-canonical-shape.php
php tooling/refactor/check-advanced-pattern-folder-violations.php
```

### 17.5 Merge Rules

```text
V4 stage branch -> main: requires full validation GREEN.
main -> master: release-grade merge only, after V4 production-ready proof.
master -> main: merge master into main before starting any new V4 stage branch.
```

### 17.6 Next Allowed Git Actions

1. Commit V4-01 implementation on main.
2. Start V4-02 from main after V4-01 is committed.
