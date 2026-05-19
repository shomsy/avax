# Strict Code Review: framework/System/Capabilities/FailureBoundary

Generated: 2026-05-19T23:31:00+02:00

## 1. Scope Gate

- Review unit ID: RUF-012
- Root type: FRAMEWORK
- Path: `framework/System/Capabilities/FailureBoundary`
- Purpose inferred from code: `framework/System/Capabilities/FailureBoundary` owns a reusable capability boundary inside the AvaX framework tree.
- Primary consumers: public API callers and/or adjacent AvaX framework/component units inferred from `PublicSurface`, `Flows`, `Capabilities`, and test ownership.
- Runtime context: long-lived/runtime-sensitive.
- Lifecycle classification: reusable capability boundary.
- Public API stability requirement: public API stable boundary expected.
- Non-goals for the review: no code changes, no test changes, no folder moves, no API changes, no remediation backlog rewrite.

## 2. As-Built Execution Flow

```text
consumer/test -> framework/System/Capabilities/FailureBoundary/System/PublicSurface (if present) -> Flows (if present) -> Capabilities -> Configuration/Foundation support
```

- State is created: primarily in Configuration/Builders or constructors where present; findings below flag creation outside approved composition contexts.
- State is mutated: within flows/capabilities when behavior requires it; request/runtime sensitive units require reset proof.
- Decisions are made: by flow/capability classes inferred from folder ownership; explicit semantic branch review is limited to concrete finding evidence.
- Pure/mechanical execution: expected in Foundation/value/result helpers and simple delegation surfaces.
- Review gap note: this file is evidence-backed by repository scans, prior generated unit review, and targeted source inspection; it is not a claim that every branch is semantically proven.

## 3. Primary Axis and Secondary Axis

This unit is fundamentally organized around reusable capability boundary.

Design Risk: Dual-axis complexity detected: complexity/maintainability pressure.

## 4. Responsibility and Boundary Map

| Sub-area/class/group | Orchestrates | Executes | Holds state | Notes |
|---|---:|---:|---:|---|
| PublicSurface | yes | no | should be minimal | Present in discovery; checked against finding table. |
| Flows | partial | yes | should be minimal | Present in discovery; checked against finding table. |
| Capabilities | partial | yes | possible | Present in discovery; checked against finding table. |
| Configuration | yes | no | possible | Present in discovery; checked against finding table. |
| Foundation | partial | no | should be minimal | Present in discovery; checked against finding table. |

Responsibility boundaries are: **STRESSED**.

## 5. Mutability and Runtime Safety

- Classification: **JUSTIFIED**.
- Static mutable state/request scope/global state/lazy singleton/cache/reset safety were checked through available runtime and direct-instantiation gates plus concrete findings.
- If no finding is listed here, that means no concrete finding was produced in this pass, not unconditional runtime GREEN.

## 6. System Invariants for the Unit

| Invariant | Enforced where | Evidence | Status |
|---|---|---|---|
| Public entrypoints delegate and do not own internal assembly | PublicSurface/Configuration boundary | public surface and direct-instantiation gates | ENFORCED |
| Dependency creation belongs to Configuration/approved composition context | Configuration/ServiceProvider/builders | direct-instantiation and runtime-composition gates | PARTIAL |
| Behavior is backed by tests, not smoke-only claims | tests tree | test discovery: yes (37) | PARTIAL |
| Ownership follows flow/capability/component shape | System folder taxonomy | component suite and canonical shape gates | ENFORCED |

## 7. Correctness and Design Review

- Highest strict severity: HIGH.
- Finding count: 6.
- Review lens applied: correctness, cohesion, coupling, naming, complexity, null/error handling, type safety, immutability, side effects, public API stability, hidden behavior, extensibility cost, and maintainability.

## 8. Security Review

- Security-sensitive checks covered unsafe deserialization, file/path handling, command execution, SSRF, injection, XSS/CSRF/session/cookie risk, auth/authz, secrets/logging, crypto/hashing, unsafe defaults, and negative proof where applicable.
- Security-sensitive unresolved concrete findings are HIGH/BLOCKER unless explicitly proven otherwise.

## 9. Performance and Memory Review

- Checked hot-path work, object graph creation, reflection, filesystem/network I/O, unbounded arrays/caches, expensive construction, and worker memory leak patterns through available gates and finding evidence.

## 10. Test Quality Review

- Tests detected: yes (37).
- Test quality is considered proven only where behavior/failure/boundary tests are visible in current evidence; scanner-only absence is not proof.

## 11. Strict Code Review Findings

### Finding: FailureBoundary compiled policy files use raw file operations classified NEEDS_DESIGN_DECISION.

- Finding ID: SCR-0044
- Review unit: `framework/System/Capabilities/FailureBoundary`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: FailureBoundary compiled policy files use raw file operations classified NEEDS_DESIGN_DECISION.
- Root cause: The unit boundary allows security boundary pressure to appear in `framework/System/Capabilities/FailureBoundary/Capabilities/WriteCompiledFailurePolicies/WriteCompiledFailurePolicies.php:30-34; CompileFailurePolicies.php:39; ReadCompiledFailurePolicies.php:25-29; CompiledMethodPolicy.php:116` instead of keeping the responsibility isolated.
- Impact: Security boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `framework/System/Capabilities/FailureBoundary/Capabilities/WriteCompiledFailurePolicies/WriteCompiledFailurePolicies.php:30-34; CompileFailurePolicies.php:39; ReadCompiledFailurePolicies.php:25-29; CompiledMethodPolicy.php:116`; original review finding `DR-0045`; validation gate `php tooling/refactor/check-raw-file-operations.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify as approved compiled-artifact I/O or migrate to Filesystem/Storage boundary with path safety tests.
- Test/gate proof required: php tooling/refactor/check-raw-file-operations.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0044

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0650
- Review unit: `framework/System/Capabilities/FailureBoundary`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `framework/System/Capabilities/FailureBoundary/Configuration/FailureBoundaryConfiguration.php:12` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `framework/System/Capabilities/FailureBoundary/Configuration/FailureBoundaryConfiguration.php:12`; original review finding `DR-0651`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0650

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0651
- Review unit: `framework/System/Capabilities/FailureBoundary`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `framework/System/Capabilities/FailureBoundary/Foundation/CompiledMethodPolicy.php:12` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `framework/System/Capabilities/FailureBoundary/Foundation/CompiledMethodPolicy.php:12`; original review finding `DR-0652`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0651

### Finding: Constructor has 9 parameters.

- Finding ID: SCR-0652
- Review unit: `framework/System/Capabilities/FailureBoundary`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 9 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `framework/System/Capabilities/FailureBoundary/Foundation/FailureAction.php:15` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `framework/System/Capabilities/FailureBoundary/Foundation/FailureAction.php:15`; original review finding `DR-0653`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0652

### Finding: Constructor has 12 parameters.

- Finding ID: SCR-0653
- Review unit: `framework/System/Capabilities/FailureBoundary`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 12 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `framework/System/Capabilities/FailureBoundary/Foundation/FailurePolicy.php:17` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `framework/System/Capabilities/FailureBoundary/Foundation/FailurePolicy.php:17`; original review finding `DR-0654`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0653

### Finding: Constructor has 9 parameters.

- Finding ID: SCR-0654
- Review unit: `framework/System/Capabilities/FailureBoundary`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 9 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `framework/System/Capabilities/FailureBoundary/Capabilities/RunFailurePipeline/RunFailurePipeline.php:27` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `framework/System/Capabilities/FailureBoundary/Capabilities/RunFailurePipeline/RunFailurePipeline.php:27`; original review finding `DR-0655`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0654

## 12. Strict Code Review Decision

Decision: **TARGETED_REDESIGN**.

Reason: highest severity `HIGH`, strict finding count `6`, tests `yes (37)`, confidence `HIGH`. KEEP_AND_IMPROVE is not used when BLOCKER/HIGH remains.
