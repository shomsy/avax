# Strict Code Review: framework/System/PublicSurface

Generated: 2026-05-19T23:31:00+02:00

## 1. Scope Gate

- Review unit ID: RUF-080
- Root type: FRAMEWORK
- Path: `framework/System/PublicSurface`
- Purpose inferred from code: `framework/System/PublicSurface` owns a PublicSurface delegation inside the AvaX framework tree.
- Primary consumers: public API callers and/or adjacent AvaX framework/component units inferred from `PublicSurface`, `Flows`, `Capabilities`, and test ownership.
- Runtime context: long-lived/runtime-sensitive.
- Lifecycle classification: PublicSurface delegation.
- Public API stability requirement: public API stable boundary expected.
- Non-goals for the review: no code changes, no test changes, no folder moves, no API changes, no remediation backlog rewrite.

## 2. As-Built Execution Flow

```text
consumer/test -> framework/System/PublicSurface/System/PublicSurface (if present) -> Flows (if present) -> Capabilities -> Configuration/Foundation support
```

- State is created: primarily in Configuration/Builders or constructors where present; findings below flag creation outside approved composition contexts.
- State is mutated: within flows/capabilities when behavior requires it; request/runtime sensitive units require reset proof.
- Decisions are made: by flow/capability classes inferred from folder ownership; explicit semantic branch review is limited to concrete finding evidence.
- Pure/mechanical execution: expected in Foundation/value/result helpers and simple delegation surfaces.
- Review gap note: this file is evidence-backed by repository scans, prior generated unit review, and targeted source inspection; it is not a claim that every branch is semantically proven.

## 3. Primary Axis and Secondary Axis

This unit is fundamentally organized around PublicSurface delegation.

Design Risk: Dual-axis complexity detected: configuration/DI pressure.

## 4. Responsibility and Boundary Map

| Sub-area/class/group | Orchestrates | Executes | Holds state | Notes |
|---|---:|---:|---:|---|
| PublicSurface | yes | no | should be minimal | Present in discovery; checked against finding table. |

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
| Behavior is backed by tests, not smoke-only claims | tests tree | test discovery: yes (47) | PARTIAL |
| Ownership follows flow/capability/component shape | System folder taxonomy | component suite and canonical shape gates | ENFORCED |

## 7. Correctness and Design Review

- Highest strict severity: HIGH.
- Finding count: 10.
- Review lens applied: correctness, cohesion, coupling, naming, complexity, null/error handling, type safety, immutability, side effects, public API stability, hidden behavior, extensibility cost, and maintainability.

## 8. Security Review

- Security-sensitive checks covered unsafe deserialization, file/path handling, command execution, SSRF, injection, XSS/CSRF/session/cookie risk, auth/authz, secrets/logging, crypto/hashing, unsafe defaults, and negative proof where applicable.
- Security-sensitive unresolved concrete findings are HIGH/BLOCKER unless explicitly proven otherwise.

## 9. Performance and Memory Review

- Checked hot-path work, object graph creation, reflection, filesystem/network I/O, unbounded arrays/caches, expensive construction, and worker memory leak patterns through available gates and finding evidence.

## 10. Test Quality Review

- Tests detected: yes (47).
- Test quality is considered proven only where behavior/failure/boundary tests are visible in current evidence; scanner-only absence is not proof.

## 11. Strict Code Review Findings

### Finding: PublicSurface directly instantiates collaborators.

- Finding ID: SCR-0388
- Review unit: `framework/System/PublicSurface`
- Severity: HIGH
- Risk level: High
- Symptom: PublicSurface directly instantiates collaborators.
- Root cause: The unit boundary allows public API boundary pressure to appear in `framework/System/PublicSurface/App.php:207,211,254,271,272,274,275,285...` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `framework/System/PublicSurface/App.php:207,211,254,271,272,274,275,285...`; original review finding `DR-0389`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0388

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0389
- Review unit: `framework/System/PublicSurface`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `framework/System/PublicSurface/Avax.php:72` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `framework/System/PublicSurface/Avax.php:72`; original review finding `DR-0390`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move default creation to approved configuration/boot context.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0389

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0390
- Review unit: `framework/System/PublicSurface`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `framework/System/PublicSurface/Avax.php:74` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `framework/System/PublicSurface/Avax.php:74`; original review finding `DR-0391`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move default creation to approved configuration/boot context.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0390

### Finding: PublicSurface directly instantiates collaborators.

- Finding ID: SCR-0391
- Review unit: `framework/System/PublicSurface`
- Severity: HIGH
- Risk level: High
- Symptom: PublicSurface directly instantiates collaborators.
- Root cause: The unit boundary allows public API boundary pressure to appear in `framework/System/PublicSurface/Avax.php:72,74,75,76,77,78,79,80...` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `framework/System/PublicSurface/Avax.php:72,74,75,76,77,78,79,80...`; original review finding `DR-0392`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move collaborator creation behind framework configuration.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0391

### Finding: Null-coalescing fallback instantiates a dependency.

- Finding ID: SCR-0392
- Review unit: `framework/System/PublicSurface`
- Severity: HIGH
- Risk level: High
- Symptom: Null-coalescing fallback instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `framework/System/PublicSurface/BootDsl.php:150` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `framework/System/PublicSurface/BootDsl.php:150`; original review finding `DR-0393`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Make the dependency explicit and fail during boot/verification.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0392

### Finding: PublicSurface directly instantiates collaborators.

- Finding ID: SCR-0393
- Review unit: `framework/System/PublicSurface`
- Severity: HIGH
- Risk level: High
- Symptom: PublicSurface directly instantiates collaborators.
- Root cause: The unit boundary allows public API boundary pressure to appear in `framework/System/PublicSurface/BootDsl.php:55,59,150,151,155,156,159,164` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `framework/System/PublicSurface/BootDsl.php:55,59,150,151,155,156,159,164`; original review finding `DR-0394`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move Boot DSL assembly behind framework configuration without changing public API.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0393

### Finding: Hidden fallback construction in public DSL.

- Finding ID: SCR-0394
- Review unit: `framework/System/PublicSurface`
- Severity: HIGH
- Risk level: High
- Symptom: Hidden fallback construction in public DSL.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `framework/System/PublicSurface/BootDsl.php:150` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `framework/System/PublicSurface/BootDsl.php:150`; original review finding `DR-0395`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Replace fallback with injected/default boot dependency.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0394

### Finding: PublicSurface file is 366 lines (>150).

- Finding ID: SCR-0637
- Review unit: `framework/System/PublicSurface`
- Severity: HIGH
- Risk level: High
- Symptom: PublicSurface file is 366 lines (>150).
- Root cause: The unit boundary allows public API boundary pressure to appear in `framework/System/PublicSurface/App.php` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `framework/System/PublicSurface/App.php`; original review finding `DR-0638`; validation gate `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify behavior leaks and move internal behavior behind flows/capabilities.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0637

### Finding: PublicSurface file exceeds review threshold.

- Finding ID: SCR-0638
- Review unit: `framework/System/PublicSurface`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: PublicSurface file exceeds review threshold.
- Root cause: The unit boundary allows public API boundary pressure to appear in `framework/System/PublicSurface/Avax.php` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `framework/System/PublicSurface/Avax.php`; original review finding `DR-0639`; validation gate `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify delegate vs behavior methods.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0638

### Finding: PublicSurface file exceeds review threshold.

- Finding ID: SCR-0639
- Review unit: `framework/System/PublicSurface`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: PublicSurface file exceeds review threshold.
- Root cause: The unit boundary allows public API boundary pressure to appear in `framework/System/PublicSurface/BootDsl.php` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `framework/System/PublicSurface/BootDsl.php`; original review finding `DR-0640`; validation gate `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify delegate vs behavior methods.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0639

## 12. Strict Code Review Decision

Decision: **TARGETED_REDESIGN**.

Reason: highest severity `HIGH`, strict finding count `10`, tests `yes (47)`, confidence `HIGH`. KEEP_AND_IMPROVE is not used when BLOCKER/HIGH remains.
