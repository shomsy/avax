# Strict Code Review: components/DataStack/Database

Generated: 2026-05-19T23:31:00+02:00

## 1. Scope Gate

- Review unit ID: RUC-022
- Root type: COMPONENT
- Path: `components/DataStack/Database`
- Purpose inferred from code: `components/DataStack/Database` owns a runtime or user action flow inside the AvaX component tree.
- Primary consumers: public API callers and/or adjacent AvaX framework/component units inferred from `PublicSurface`, `Flows`, `Capabilities`, and test ownership.
- Runtime context: long-lived/runtime-sensitive.
- Lifecycle classification: runtime or user action flow.
- Public API stability requirement: public API stable boundary expected.
- Non-goals for the review: no code changes, no test changes, no folder moves, no API changes, no remediation backlog rewrite.

## 2. As-Built Execution Flow

```text
consumer/test -> components/DataStack/Database/System/PublicSurface (if present) -> Flows (if present) -> Capabilities -> Configuration/Foundation support
```

- State is created: primarily in Configuration/Builders or constructors where present; findings below flag creation outside approved composition contexts.
- State is mutated: within flows/capabilities when behavior requires it; request/runtime sensitive units require reset proof.
- Decisions are made: by flow/capability classes inferred from folder ownership; explicit semantic branch review is limited to concrete finding evidence.
- Pure/mechanical execution: expected in Foundation/value/result helpers and simple delegation surfaces.
- Review gap note: this file is evidence-backed by repository scans, prior generated unit review, and targeted source inspection; it is not a claim that every branch is semantically proven.

## 3. Primary Axis and Secondary Axis

This unit is fundamentally organized around runtime or user action flow.

Design Risk: Dual-axis complexity detected: PublicSurface pressure, complexity/maintainability pressure, configuration/DI pressure.

## 4. Responsibility and Boundary Map

| Sub-area/class/group | Orchestrates | Executes | Holds state | Notes |
|---|---:|---:|---:|---|
| PublicSurface | yes | no | should be minimal | Present in discovery; checked against finding table. |
| Flows | partial | yes | should be minimal | Present in discovery; checked against finding table. |
| Capabilities | partial | yes | possible | Present in discovery; checked against finding table. |
| Configuration | yes | no | possible | Present in discovery; checked against finding table. |
| Foundation | partial | no | should be minimal | Present in discovery; checked against finding table. |

Responsibility boundaries are: **VIOLATED**.

## 5. Mutability and Runtime Safety

- Classification: **MISPLACED**.
- Static mutable state/request scope/global state/lazy singleton/cache/reset safety were checked through available runtime and direct-instantiation gates plus concrete findings.
- If no finding is listed here, that means no concrete finding was produced in this pass, not unconditional runtime GREEN.

## 6. System Invariants for the Unit

| Invariant | Enforced where | Evidence | Status |
|---|---|---|---|
| Public entrypoints delegate and do not own internal assembly | PublicSurface/Configuration boundary | public surface and direct-instantiation gates | PARTIAL |
| Dependency creation belongs to Configuration/approved composition context | Configuration/ServiceProvider/builders | direct-instantiation and runtime-composition gates | NOT_ENFORCED |
| Behavior is backed by tests, not smoke-only claims | tests tree | test discovery: yes (25) | PARTIAL |
| Ownership follows flow/capability/component shape | System folder taxonomy | component suite and canonical shape gates | ENFORCED |

## 7. Correctness and Design Review

- Highest strict severity: HIGH.
- Finding count: 42.
- Review lens applied: correctness, cohesion, coupling, naming, complexity, null/error handling, type safety, immutability, side effects, public API stability, hidden behavior, extensibility cost, and maintainability.

## 8. Security Review

- Security-sensitive checks covered unsafe deserialization, file/path handling, command execution, SSRF, injection, XSS/CSRF/session/cookie risk, auth/authz, secrets/logging, crypto/hashing, unsafe defaults, and negative proof where applicable.
- Security-sensitive unresolved concrete findings are HIGH/BLOCKER unless explicitly proven otherwise.

## 9. Performance and Memory Review

- Checked hot-path work, object graph creation, reflection, filesystem/network I/O, unbounded arrays/caches, expensive construction, and worker memory leak patterns through available gates and finding evidence.

## 10. Test Quality Review

- Tests detected: yes (25).
- Test quality is considered proven only where behavior/failure/boundary tests are visible in current evidence; scanner-only absence is not proof.

## 11. Strict Code Review Findings

### Finding: PublicSurface directly instantiates collaborators (3 `new` expressions detected).

- Finding ID: SCR-0266
- Review unit: `components/DataStack/Database`
- Severity: HIGH
- Risk level: High
- Symptom: PublicSurface directly instantiates collaborators (3 `new` expressions detected).
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/DataStack/Database/System/PublicSurface/shortcuts.php:48,67,86` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/PublicSurface/shortcuts.php:48,67,86`; original review finding `DR-0267`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0266

### Finding: PublicSurface directly instantiates collaborators (7 `new` expressions detected).

- Finding ID: SCR-0267
- Review unit: `components/DataStack/Database`
- Severity: HIGH
- Risk level: High
- Symptom: PublicSurface directly instantiates collaborators (7 `new` expressions detected).
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/DataStack/Database/System/PublicSurface/Database.php:36,51,56,61,66,71,76` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/PublicSurface/Database.php:36,51,56,61,66,71,76`; original review finding `DR-0268`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0267

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0268
- Review unit: `components/DataStack/Database`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/Database/System/PublicSurface/ManageEntityPersistence.php:20` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/PublicSurface/ManageEntityPersistence.php:20`; original review finding `DR-0269`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0268

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0269
- Review unit: `components/DataStack/Database`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/Database/System/PublicSurface/ManageEntityPersistence.php:21` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/PublicSurface/ManageEntityPersistence.php:21`; original review finding `DR-0270`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0269

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0270
- Review unit: `components/DataStack/Database`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/Database/System/PublicSurface/ManageEntityPersistence.php:22` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/PublicSurface/ManageEntityPersistence.php:22`; original review finding `DR-0271`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0270

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0271
- Review unit: `components/DataStack/Database`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/Database/System/PublicSurface/ManageEntityPersistence.php:27` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/PublicSurface/ManageEntityPersistence.php:27`; original review finding `DR-0272`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0271

### Finding: PublicSurface directly instantiates collaborators (4 `new` expressions detected).

- Finding ID: SCR-0272
- Review unit: `components/DataStack/Database`
- Severity: HIGH
- Risk level: High
- Symptom: PublicSurface directly instantiates collaborators (4 `new` expressions detected).
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/DataStack/Database/System/PublicSurface/ManageEntityPersistence.php:20,21,22,27` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/PublicSurface/ManageEntityPersistence.php:20,21,22,27`; original review finding `DR-0273`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0272

### Finding: PublicSurface directly instantiates collaborators (1 `new` expressions detected).

- Finding ID: SCR-0273
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/DataStack/Database/System/PublicSurface/Query.php:24` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/PublicSurface/Query.php:24`; original review finding `DR-0274`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0273

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0274
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/Database/System/Capabilities/Observability/QueryTimeline.php:44` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Observability/QueryTimeline.php:44`; original review finding `DR-0275`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0274

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0275
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/Database/System/Capabilities/Connections/MultiTenantPool.php:26` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Connections/MultiTenantPool.php:26`; original review finding `DR-0276`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0275

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0276
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/Database/System/Capabilities/Connections/Pools/DatabaseConnectionPool.php:44` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Connections/Pools/DatabaseConnectionPool.php:44`; original review finding `DR-0277`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0276

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0277
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/Database/System/Capabilities/Connections/Pools/DatabaseConnectionPool.php:45` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Connections/Pools/DatabaseConnectionPool.php:45`; original review finding `DR-0278`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0277

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0278
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/Database/System/Capabilities/Query/IR/IRBuilder.php:19` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Query/IR/IRBuilder.php:19`; original review finding `DR-0279`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0278

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0279
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/Database/System/Capabilities/Query/CreateBuilder/CreateBuilder.php:37` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Query/CreateBuilder/CreateBuilder.php:37`; original review finding `DR-0280`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0279

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0280
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/Database/System/Capabilities/Query/State/QueryState.php:75` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Query/State/QueryState.php:75`; original review finding `DR-0281`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0280

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0281
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/Database/System/Capabilities/Query/Builder/QueryBuilder.php:68` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Query/Builder/QueryBuilder.php:68`; original review finding `DR-0282`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0281

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0282
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/Database/System/Capabilities/Query/Builder/QueryBuilder.php:72` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Query/Builder/QueryBuilder.php:72`; original review finding `DR-0283`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0282

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0283
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/Database/System/Capabilities/Query/Projections/ResultMapper.php:25` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Query/Projections/ResultMapper.php:25`; original review finding `DR-0284`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0283

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0284
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/Database/System/Capabilities/Query/Advanced/BulkOperations/BulkUpsert.php:30` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Query/Advanced/BulkOperations/BulkUpsert.php:30`; original review finding `DR-0285`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0284

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0285
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/Database/System/Capabilities/Migrations/CLI/SeederCommand.php:28` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Migrations/CLI/SeederCommand.php:28`; original review finding `DR-0286`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0285

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0521
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/Database/System/PublicSurface/Database.php:23` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/PublicSurface/Database.php:23`; original review finding `DR-0522`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0521

### Finding: Constructor has 9 parameters.

- Finding ID: SCR-0522
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 9 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/Database/System/Capabilities/Observability/QueryEntry.php:14` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Observability/QueryEntry.php:14`; original review finding `DR-0523`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0522

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0523
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/Database/System/Capabilities/Observability/SlowQueryStatistics.php:12` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Observability/SlowQueryStatistics.php:12`; original review finding `DR-0524`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0523

### Finding: Constructor has 9 parameters.

- Finding ID: SCR-0524
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 9 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/Database/System/Capabilities/Observability/SlowQueryDetector.php:15` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Observability/SlowQueryDetector.php:15`; original review finding `DR-0525`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0524

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0525
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/Database/System/Capabilities/Observability/SlowQueryDetector.php:94` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Observability/SlowQueryDetector.php:94`; original review finding `DR-0526`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0525

### Finding: Class/file is 394 lines (>300).

- Finding ID: SCR-0526
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 394 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/Database/System/Capabilities/Observability/SlowQueryDetector.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Observability/SlowQueryDetector.php`; original review finding `DR-0527`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0526

### Finding: Constructor has 9 parameters.

- Finding ID: SCR-0527
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 9 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/Database/System/Capabilities/Observability/SlowQueryReport.php:15` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Observability/SlowQueryReport.php:15`; original review finding `DR-0528`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0527

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0528
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/Database/System/Capabilities/Transactions/DeadlockReport.php:14` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Transactions/DeadlockReport.php:14`; original review finding `DR-0529`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0528

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0529
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/Database/System/Capabilities/Transactions/DeadlockDetector.php:17` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Transactions/DeadlockDetector.php:17`; original review finding `DR-0530`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0529

### Finding: Class/file is 326 lines (>300).

- Finding ID: SCR-0530
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 326 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/Database/System/Capabilities/Transactions/DeadlockDetector.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Transactions/DeadlockDetector.php`; original review finding `DR-0531`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0530

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0531
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/Database/System/Capabilities/Connections/ConnectionTypes/ConnectionConfig.php:37` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Connections/ConnectionTypes/ConnectionConfig.php:37`; original review finding `DR-0532`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0531

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0532
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/Database/System/Capabilities/Connections/Pools/PdoConnectionPool/PdoConnectionPool.php:28` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Connections/Pools/PdoConnectionPool/PdoConnectionPool.php:28`; original review finding `DR-0533`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0532

### Finding: Class/file is 510 lines (>300).

- Finding ID: SCR-0533
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 510 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/Database/System/Capabilities/Transactions/RunTransaction/Transaction.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Transactions/RunTransaction/Transaction.php`; original review finding `DR-0534`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0533

### Finding: Constructor has 16 parameters.

- Finding ID: SCR-0534
- Review unit: `components/DataStack/Database`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 16 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/Database/System/Capabilities/Query/State/QueryState.php:71` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Query/State/QueryState.php:71`; original review finding `DR-0535`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0534

### Finding: Class/file is 368 lines (>300).

- Finding ID: SCR-0535
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 368 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/Database/System/Capabilities/Query/State/QueryState.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Query/State/QueryState.php`; original review finding `DR-0536`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0535

### Finding: Class/file is 589 lines (>300).

- Finding ID: SCR-0536
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 589 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/Database/System/Capabilities/Query/Builder/QueryBuilder.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Query/Builder/QueryBuilder.php`; original review finding `DR-0537`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0536

### Finding: Class/file is 345 lines (>300).

- Finding ID: SCR-0537
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 345 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/Database/System/Capabilities/Query/Execution/QueryOrchestrator.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Query/Execution/QueryOrchestrator.php`; original review finding `DR-0538`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0537

### Finding: Class/file is 500 lines (>300).

- Finding ID: SCR-0538
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 500 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/Database/System/Capabilities/Query/Grammar/Grammar.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Query/Grammar/Grammar.php`; original review finding `DR-0539`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0538

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0539
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/Database/System/Capabilities/Telemetry/OpenTelemetry/QuerySpan.php:9` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Telemetry/OpenTelemetry/QuerySpan.php:9`; original review finding `DR-0540`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0539

### Finding: Constructor has 10 parameters.

- Finding ID: SCR-0540
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 10 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/Database/System/Capabilities/ORM/Metadata/RelationMetadata.php:14` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/ORM/Metadata/RelationMetadata.php:14`; original review finding `DR-0541`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0540

### Finding: Class/file is 598 lines (>300).

- Finding ID: SCR-0541
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 598 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/Database/System/Capabilities/ORM/Persisters/EntityPersister.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/ORM/Persisters/EntityPersister.php`; original review finding `DR-0542`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0541

### Finding: Class/file is 893 lines (>300).

- Finding ID: SCR-0542
- Review unit: `components/DataStack/Database`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 893 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/Database/System/Capabilities/Migrations/Design/Table/Blueprint.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/Database/System/Capabilities/Migrations/Design/Table/Blueprint.php`; original review finding `DR-0543`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0542

## 12. Strict Code Review Decision

Decision: **TARGETED_REDESIGN**.

Reason: highest severity `HIGH`, strict finding count `42`, tests `yes (25)`, confidence `HIGH`. KEEP_AND_IMPROVE is not used when BLOCKER/HIGH remains.
