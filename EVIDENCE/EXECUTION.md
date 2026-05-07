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

V2 Implementation: CLOSED / GREEN

V3 Implementation: LOCKED

This status is derived from CURRENT_TRUTH.md and the latest truth-reconciliation report.

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
None — V2 Engine Implementation Phase CLOSED
```

Last completed stage:

```text
Stage V2-03: V2 Engine Implementation
```

Recent completed evidence:

```text
EVIDENCE/master-plan/stage-01-final-project-tree-freeze-report.md
EVIDENCE/component-taxonomy/stage-02-taxonomy-integrity-report.md
EVIDENCE/muscle-recovery/stage-v1-01-backup-muscle-inventory-report.md
EVIDENCE/muscle-recovery/backup-muscle-inventory.md
EVIDENCE/muscle-recovery/backup-muscle-inventory.json
EVIDENCE/muscle-recovery/component-muscle-audit.md
EVIDENCE/muscle-recovery/component-muscle-audit.json
EVIDENCE/recovery-reports/static-integrity-closure-report.md
EVIDENCE/v1-integrity/static-integrity-closure-report.md
EVIDENCE/master-plan/api-classification-matrix.md
EVIDENCE/master-plan/canonical-class-map.md
EVIDENCE/master-plan/stage-08-static-analysis-report.md
EVIDENCE/master-plan/stage-09-kernel-green-report.md
EVIDENCE/master-plan/stage-11-golden-path-app-report.md
EVIDENCE/master-plan/stage-12-public-api-governance-report.md
EVIDENCE/master-plan/stage-13-extension-plugin-architecture-report.md
EVIDENCE/master-plan/stage-14-benchmark-performance-report.md
EVIDENCE/master-plan/stage-15-observability-contract-report.md
EVIDENCE/master-plan/stage-16-to-19-enterprise-governance-report.md
EVIDENCE/recovery-reports/v2-api-engine-closure/v2-api-engine-closure-report.md
EVIDENCE/v2-naming-reconciliation-report.md
EVIDENCE/v2-engine-implementation-closure/
```

All other stages are read-only context until Stage 10 is complete.

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

V2 work is CLOSED / GREEN. V1 Kernel Green has been proven. All V2 engine components are production-ready.

V2 components completed:

```text
API Engine:       ApiBlueprint, OpenAPI, GraphQL
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
API/Surface:      REST, JSON:API, Webhooks, RPC
```

Total V2 components: 72 complete (65 V1 + 7 V2)

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
