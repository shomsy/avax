# Strict Code Review: framework/System/Capabilities/Doctor

Generated: 2026-05-19T23:31:00+02:00

## 1. Scope Gate

- Review unit ID: RUF-010
- Root type: FRAMEWORK
- Path: `framework/System/Capabilities/Doctor`
- Purpose inferred from code: `framework/System/Capabilities/Doctor` owns a reusable capability boundary inside the AvaX framework tree.
- Primary consumers: public API callers and/or adjacent AvaX framework/component units inferred from `PublicSurface`, `Flows`, `Capabilities`, and test ownership.
- Runtime context: long-lived/runtime-sensitive.
- Lifecycle classification: reusable capability boundary.
- Public API stability requirement: internal boundary; still governed by ownership and test proof.
- Non-goals for the review: no code changes, no test changes, no folder moves, no API changes, no remediation backlog rewrite.

## 2. As-Built Execution Flow

```text
consumer/test -> framework/System/Capabilities/Doctor/System/PublicSurface (if present) -> Flows (if present) -> Capabilities -> Configuration/Foundation support
```

- State is created: primarily in Configuration/Builders or constructors where present; findings below flag creation outside approved composition contexts.
- State is mutated: within flows/capabilities when behavior requires it; request/runtime sensitive units require reset proof.
- Decisions are made: by flow/capability classes inferred from folder ownership; explicit semantic branch review is limited to concrete finding evidence.
- Pure/mechanical execution: expected in Foundation/value/result helpers and simple delegation surfaces.
- Review gap note: this file is evidence-backed by repository scans, prior generated unit review, and targeted source inspection; it is not a claim that every branch is semantically proven.

## 3. Primary Axis and Secondary Axis

This unit is fundamentally organized around reusable capability boundary.

Design Risk: Dual-axis complexity detected: configuration/DI pressure.

## 4. Responsibility and Boundary Map

| Sub-area/class/group | Orchestrates | Executes | Holds state | Notes |
|---|---:|---:|---:|---|
| Capabilities | partial | yes | possible | Present in discovery; checked against finding table. |

Responsibility boundaries are: **VIOLATED**.

## 5. Mutability and Runtime Safety

- Classification: **MISPLACED**.
- Static mutable state/request scope/global state/lazy singleton/cache/reset safety were checked through available runtime and direct-instantiation gates plus concrete findings.
- If no finding is listed here, that means no concrete finding was produced in this pass, not unconditional runtime GREEN.

## 6. System Invariants for the Unit

| Invariant | Enforced where | Evidence | Status |
|---|---|---|---|
| Public entrypoints delegate and do not own internal assembly | PublicSurface/Configuration boundary | public surface and direct-instantiation gates | UNKNOWN |
| Dependency creation belongs to Configuration/approved composition context | Configuration/ServiceProvider/builders | direct-instantiation and runtime-composition gates | NOT_ENFORCED |
| Behavior is backed by tests, not smoke-only claims | tests tree | test discovery: yes (36) | PARTIAL |
| Ownership follows flow/capability/component shape | System folder taxonomy | component suite and canonical shape gates | ENFORCED |

## 7. Correctness and Design Review

- Highest strict severity: HIGH.
- Finding count: 2.
- Review lens applied: correctness, cohesion, coupling, naming, complexity, null/error handling, type safety, immutability, side effects, public API stability, hidden behavior, extensibility cost, and maintainability.

## 8. Security Review

- Security-sensitive checks covered unsafe deserialization, file/path handling, command execution, SSRF, injection, XSS/CSRF/session/cookie risk, auth/authz, secrets/logging, crypto/hashing, unsafe defaults, and negative proof where applicable.
- Security-sensitive unresolved concrete findings are HIGH/BLOCKER unless explicitly proven otherwise.

## 9. Performance and Memory Review

- Checked hot-path work, object graph creation, reflection, filesystem/network I/O, unbounded arrays/caches, expensive construction, and worker memory leak patterns through available gates and finding evidence.

## 10. Test Quality Review

- Tests detected: yes (36).
- Test quality is considered proven only where behavior/failure/boundary tests are visible in current evidence; scanner-only absence is not proof.

## 11. Strict Code Review Findings

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0403
- Review unit: `framework/System/Capabilities/Doctor`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `framework/System/Capabilities/Doctor/ExecuteDoctorChecks.php:21` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `framework/System/Capabilities/Doctor/ExecuteDoctorChecks.php:21`; original review finding `DR-0404`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0403

### Finding: Framework core references specific runtimes outside narrow runtime adapter/boundary slices.

- Finding ID: SCR-0661
- Review unit: `framework/System/Capabilities/Doctor`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Framework core references specific runtimes outside narrow runtime adapter/boundary slices.
- Root cause: The unit boundary allows runtime safety pressure to appear in `framework/System/Capabilities/Doctor/CheckRuntimeMode.php:[24, 28, 32, 36, 40]` instead of keeping the responsibility isolated.
- Impact: Runtime safety risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `framework/System/Capabilities/Doctor/CheckRuntimeMode.php:[24, 28, 32, 36, 40]`; original review finding `DR-0662`; validation gate `php tooling/refactor/check-runtime-leaks.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Confirm references are documentation/metadata only or move runtime-specific behavior behind RuntimeBoundary/Capabilities/Runtime.
- Test/gate proof required: php tooling/refactor/check-runtime-leaks.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0661

## 12. Strict Code Review Decision

Decision: **TARGETED_REDESIGN**.

Reason: highest severity `HIGH`, strict finding count `2`, tests `yes (36)`, confidence `HIGH`. KEEP_AND_IMPROVE is not used when BLOCKER/HIGH remains.
