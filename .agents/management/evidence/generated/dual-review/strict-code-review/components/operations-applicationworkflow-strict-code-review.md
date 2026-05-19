# Strict Code Review: components/Operations/ApplicationWorkflow

Generated: 2026-05-19T23:31:00+02:00

## 1. Scope Gate

- Review unit ID: RUC-057
- Root type: COMPONENT
- Path: `components/Operations/ApplicationWorkflow`
- Purpose inferred from code: `components/Operations/ApplicationWorkflow` owns a runtime or user action flow inside the AvaX component tree.
- Primary consumers: public API callers and/or adjacent AvaX framework/component units inferred from `PublicSurface`, `Flows`, `Capabilities`, and test ownership.
- Runtime context: long-lived/runtime-sensitive.
- Lifecycle classification: runtime or user action flow.
- Public API stability requirement: public API stable boundary expected.
- Non-goals for the review: no code changes, no test changes, no folder moves, no API changes, no remediation backlog rewrite.

## 2. As-Built Execution Flow

```text
consumer/test -> components/Operations/ApplicationWorkflow/System/PublicSurface (if present) -> Flows (if present) -> Capabilities -> Configuration/Foundation support
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
| Behavior is backed by tests, not smoke-only claims | tests tree | test discovery: yes (1) | PARTIAL |
| Ownership follows flow/capability/component shape | System folder taxonomy | component suite and canonical shape gates | ENFORCED |

## 7. Correctness and Design Review

- Highest strict severity: HIGH.
- Finding count: 34.
- Review lens applied: correctness, cohesion, coupling, naming, complexity, null/error handling, type safety, immutability, side effects, public API stability, hidden behavior, extensibility cost, and maintainability.

## 8. Security Review

- Security-sensitive checks covered unsafe deserialization, file/path handling, command execution, SSRF, injection, XSS/CSRF/session/cookie risk, auth/authz, secrets/logging, crypto/hashing, unsafe defaults, and negative proof where applicable.
- Security-sensitive unresolved concrete findings are HIGH/BLOCKER unless explicitly proven otherwise.

## 9. Performance and Memory Review

- Checked hot-path work, object graph creation, reflection, filesystem/network I/O, unbounded arrays/caches, expensive construction, and worker memory leak patterns through available gates and finding evidence.

## 10. Test Quality Review

- Tests detected: yes (1).
- Test quality is considered proven only where behavior/failure/boundary tests are visible in current evidence; scanner-only absence is not proof.

## 11. Strict Code Review Findings

### Finding: Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\Co

- Finding ID: SCR-0039
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: HIGH
- Risk level: High
- Symptom: Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\CompensationExecutor`.
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/Operations/ApplicationWorkflow` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow`; original review finding `DR-0040`; validation gate `php tooling/refactor/check-broken-reference-semantics.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Correct the import/type target or remove stale public-surface promise in a compatibility batch.
- Test/gate proof required: php tooling/refactor/check-broken-reference-semantics.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0039

### Finding: Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\Id

- Finding ID: SCR-0040
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: HIGH
- Risk level: High
- Symptom: Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\IdempotencyStore`.
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/Operations/ApplicationWorkflow` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow`; original review finding `DR-0041`; validation gate `php tooling/refactor/check-broken-reference-semantics.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Correct the import/type target or remove stale public-surface promise in a compatibility batch.
- Test/gate proof required: php tooling/refactor/check-broken-reference-semantics.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0040

### Finding: Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\Sa

- Finding ID: SCR-0041
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: HIGH
- Risk level: High
- Symptom: Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\SagaState`.
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/Operations/ApplicationWorkflow` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow`; original review finding `DR-0042`; validation gate `php tooling/refactor/check-broken-reference-semantics.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Correct the import/type target or remove stale public-surface promise in a compatibility batch.
- Test/gate proof required: php tooling/refactor/check-broken-reference-semantics.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0041

### Finding: Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\Sa

- Finding ID: SCR-0042
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: HIGH
- Risk level: High
- Symptom: Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\SagaStep`.
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/Operations/ApplicationWorkflow` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow`; original review finding `DR-0043`; validation gate `php tooling/refactor/check-broken-reference-semantics.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Correct the import/type target or remove stale public-surface promise in a compatibility batch.
- Test/gate proof required: php tooling/refactor/check-broken-reference-semantics.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0042

### Finding: PublicSurface directly instantiates collaborators (1 `new` expressions detected).

- Finding ID: SCR-0202
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/Operations/ApplicationWorkflow/System/PublicSurface/ApplicationWorkflow.php:18` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/PublicSurface/ApplicationWorkflow.php:18`; original review finding `DR-0203`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0202

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0203
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/ApplicationWorkflow/System/PublicSurface/Saga.php:47` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/PublicSurface/Saga.php:47`; original review finding `DR-0204`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0203

### Finding: PublicSurface directly instantiates collaborators (8 `new` expressions detected).

- Finding ID: SCR-0204
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: HIGH
- Risk level: High
- Symptom: PublicSurface directly instantiates collaborators (8 `new` expressions detected).
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/Operations/ApplicationWorkflow/System/PublicSurface/Saga.php:47,53,54,55,74,78,98,116` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/PublicSurface/Saga.php:47,53,54,55,74,78,98,116`; original review finding `DR-0205`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0204

### Finding: PublicSurface directly instantiates collaborators (1 `new` expressions detected).

- Finding ID: SCR-0205
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/Operations/ApplicationWorkflow/System/PublicSurface/Workflow.php:41` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/PublicSurface/Workflow.php:41`; original review finding `DR-0206`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0205

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0206
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/ApplicationWorkflow/System/Flows/Saga/Saga.php:38` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Flows/Saga/Saga.php:38`; original review finding `DR-0207`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0206

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0207
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/ApplicationWorkflow/System/Flows/Saga/Saga.php:39` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Flows/Saga/Saga.php:39`; original review finding `DR-0208`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0207

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0208
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/ApplicationWorkflow/System/Flows/Saga/Saga.php:40` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Flows/Saga/Saga.php:40`; original review finding `DR-0209`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0208

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0209
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/ApplicationWorkflow/System/Flows/Saga/Saga.php:42` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Flows/Saga/Saga.php:42`; original review finding `DR-0210`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0209

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0210
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/ApplicationWorkflow/System/Flows/Saga/ConfigureSagaRuntime/ConfigureSagaRuntime.php:15` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Flows/Saga/ConfigureSagaRuntime/ConfigureSagaRuntime.php:15`; original review finding `DR-0211`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0210

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0211
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/ApplicationWorkflow/System/Flows/Saga/ConfigureSagaRuntime/ConfigureSagaRuntime.php:16` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Flows/Saga/ConfigureSagaRuntime/ConfigureSagaRuntime.php:16`; original review finding `DR-0212`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0211

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0212
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/ApplicationWorkflow/System/Flows/Saga/ConfigureSagaRuntime/ConfigureSagaRuntime.php:17` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Flows/Saga/ConfigureSagaRuntime/ConfigureSagaRuntime.php:17`; original review finding `DR-0213`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0212

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0213
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/ApplicationWorkflow/System/Flows/Saga/ConfigureSagaRuntime/ConfigureSagaRuntime.php:25` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Flows/Saga/ConfigureSagaRuntime/ConfigureSagaRuntime.php:25`; original review finding `DR-0214`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0213

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0214
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/ApplicationWorkflow/System/Flows/Saga/StartSaga/SagaInstance.php:44` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Flows/Saga/StartSaga/SagaInstance.php:44`; original review finding `DR-0215`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0214

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0215
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/ApplicationWorkflow/System/Flows/Saga/InspectSaga/SagaRuntimeEvent.php:23` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Flows/Saga/InspectSaga/SagaRuntimeEvent.php:23`; original review finding `DR-0216`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0215

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0216
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/ApplicationWorkflow/System/Capabilities/Saga/Saga.php:38` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Capabilities/Saga/Saga.php:38`; original review finding `DR-0217`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0216

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0217
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/ApplicationWorkflow/System/Capabilities/Saga/Saga.php:39` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Capabilities/Saga/Saga.php:39`; original review finding `DR-0218`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0217

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0218
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/ApplicationWorkflow/System/Capabilities/Saga/Saga.php:40` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Capabilities/Saga/Saga.php:40`; original review finding `DR-0219`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0218

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0219
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/ApplicationWorkflow/System/Capabilities/Saga/Saga.php:41` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Capabilities/Saga/Saga.php:41`; original review finding `DR-0220`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0219

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0220
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/ApplicationWorkflow/System/Capabilities/Saga/Saga.php:42` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Capabilities/Saga/Saga.php:42`; original review finding `DR-0221`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0220

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0221
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/ApplicationWorkflow/System/Capabilities/SagaExecution/SagaExecution.php:56` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Capabilities/SagaExecution/SagaExecution.php:56`; original review finding `DR-0222`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0221

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0222
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/ApplicationWorkflow/System/Capabilities/SagaExecution/SagaExecution.php:57` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Capabilities/SagaExecution/SagaExecution.php:57`; original review finding `DR-0223`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0222

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0223
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/ApplicationWorkflow/System/Capabilities/SagaExecution/SagaExecution.php:58` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Capabilities/SagaExecution/SagaExecution.php:58`; original review finding `DR-0224`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0223

### Finding: PublicSurface file is 304 lines (>150).

- Finding ID: SCR-0490
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: HIGH
- Risk level: High
- Symptom: PublicSurface file is 304 lines (>150).
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/Operations/ApplicationWorkflow/System/PublicSurface/Saga.php` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/PublicSurface/Saga.php`; original review finding `DR-0491`; validation gate `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify methods as delegate vs behavior; move internal behavior behind flows/capabilities without API churn.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0490

### Finding: Constructor has 10 parameters.

- Finding ID: SCR-0491
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 10 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Operations/ApplicationWorkflow/System/Flows/Saga/Saga.php:24` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Flows/Saga/Saga.php:24`; original review finding `DR-0492`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0491

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0492
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Operations/ApplicationWorkflow/System/Flows/Saga/StoreSagaState/SagaEvent.php:15` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Flows/Saga/StoreSagaState/SagaEvent.php:15`; original review finding `DR-0493`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0492

### Finding: Constructor has 14 parameters.

- Finding ID: SCR-0493
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 14 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Operations/ApplicationWorkflow/System/Flows/Saga/StartSaga/SagaInstance.php:26` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Flows/Saga/StartSaga/SagaInstance.php:26`; original review finding `DR-0494`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0493

### Finding: Constructor has 16 parameters.

- Finding ID: SCR-0494
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 16 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Operations/ApplicationWorkflow/System/Flows/Saga/DefineSaga/SagaDefinition.php:31` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Flows/Saga/DefineSaga/SagaDefinition.php:31`; original review finding `DR-0495`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0494

### Finding: Constructor has 13 parameters.

- Finding ID: SCR-0495
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 13 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Operations/ApplicationWorkflow/System/Flows/Saga/DefineSaga/SagaStepDefinition.php:23` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Flows/Saga/DefineSaga/SagaStepDefinition.php:23`; original review finding `DR-0496`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0495

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0496
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Operations/ApplicationWorkflow/System/Flows/Saga/RunSagaStep/SagaStepResult.php:19` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Flows/Saga/RunSagaStep/SagaStepResult.php:19`; original review finding `DR-0497`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0496

### Finding: Constructor has 10 parameters.

- Finding ID: SCR-0497
- Review unit: `components/Operations/ApplicationWorkflow`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 10 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Operations/ApplicationWorkflow/System/Capabilities/Saga/Saga.php:24` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/ApplicationWorkflow/System/Capabilities/Saga/Saga.php:24`; original review finding `DR-0498`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0497

## 12. Strict Code Review Decision

Decision: **TARGETED_REDESIGN**.

Reason: highest severity `HIGH`, strict finding count `34`, tests `yes (1)`, confidence `HIGH`. KEEP_AND_IMPROVE is not used when BLOCKER/HIGH remains.
