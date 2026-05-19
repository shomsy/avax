# Strict Code Review: components/Operations/Events

Generated: 2026-05-19T23:31:00+02:00

## 1. Scope Gate

- Review unit ID: RUC-061
- Root type: COMPONENT
- Path: `components/Operations/Events`
- Purpose inferred from code: `components/Operations/Events` owns a runtime or user action flow inside the AvaX component tree.
- Primary consumers: public API callers and/or adjacent AvaX framework/component units inferred from `PublicSurface`, `Flows`, `Capabilities`, and test ownership.
- Runtime context: long-lived/runtime-sensitive.
- Lifecycle classification: runtime or user action flow.
- Public API stability requirement: public API stable boundary expected.
- Non-goals for the review: no code changes, no test changes, no folder moves, no API changes, no remediation backlog rewrite.

## 2. As-Built Execution Flow

```text
consumer/test -> components/Operations/Events/System/PublicSurface (if present) -> Flows (if present) -> Capabilities -> Configuration/Foundation support
```

- State is created: primarily in Configuration/Builders or constructors where present; findings below flag creation outside approved composition contexts.
- State is mutated: within flows/capabilities when behavior requires it; request/runtime sensitive units require reset proof.
- Decisions are made: by flow/capability classes inferred from folder ownership; explicit semantic branch review is limited to concrete finding evidence.
- Pure/mechanical execution: expected in Foundation/value/result helpers and simple delegation surfaces.
- Review gap note: this file is evidence-backed by repository scans, prior generated unit review, and targeted source inspection; it is not a claim that every branch is semantically proven.

## 3. Primary Axis and Secondary Axis

This unit is fundamentally organized around runtime or user action flow.

Design Risk: Dual-axis complexity detected: PublicSurface pressure, configuration/DI pressure.

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
| Behavior is backed by tests, not smoke-only claims | tests tree | test discovery: yes (5) | PARTIAL |
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

- Tests detected: yes (5).
- Test quality is considered proven only where behavior/failure/boundary tests are visible in current evidence; scanner-only absence is not proof.

## 11. Strict Code Review Findings

### Finding: Null-coalescing fallback instantiates a dependency.

- Finding ID: SCR-0146
- Review unit: `components/Operations/Events`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Null-coalescing fallback instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/Events/System/Foundation/GlobalEventListenerState.php:28` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/Events/System/Foundation/GlobalEventListenerState.php:28`; original review finding `DR-0147`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0146

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0147
- Review unit: `components/Operations/Events`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/Events/System/PublicSurface/Events.php:31` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/Events/System/PublicSurface/Events.php:31`; original review finding `DR-0148`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0147

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0148
- Review unit: `components/Operations/Events`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/Events/System/PublicSurface/Events.php:32` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/Events/System/PublicSurface/Events.php:32`; original review finding `DR-0149`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0148

### Finding: PublicSurface directly instantiates collaborators (4 `new` expressions detected).

- Finding ID: SCR-0149
- Review unit: `components/Operations/Events`
- Severity: HIGH
- Risk level: High
- Symptom: PublicSurface directly instantiates collaborators (4 `new` expressions detected).
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/Operations/Events/System/PublicSurface/Events.php:18,31,32,39` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/Events/System/PublicSurface/Events.php:18,31,32,39`; original review finding `DR-0150`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0149

### Finding: PublicSurface directly instantiates collaborators (1 `new` expressions detected).

- Finding ID: SCR-0150
- Review unit: `components/Operations/Events`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/Operations/Events/System/PublicSurface/functions.php:20` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/Events/System/PublicSurface/functions.php:20`; original review finding `DR-0151`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0150

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0151
- Review unit: `components/Operations/Events`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/Events/System/Flows/CompileEventListeners/CompileEventListeners.php:36` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/Events/System/Flows/CompileEventListeners/CompileEventListeners.php:36`; original review finding `DR-0152`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0151

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0152
- Review unit: `components/Operations/Events`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/Events/System/Flows/RegisterEventListeners/EventListenerDsl.php:28` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/Events/System/Flows/RegisterEventListeners/EventListenerDsl.php:28`; original review finding `DR-0153`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0152

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0153
- Review unit: `components/Operations/Events`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/Events/System/Capabilities/Psr14/Psr14ListenerProviderAdapter.php:22` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/Events/System/Capabilities/Psr14/Psr14ListenerProviderAdapter.php:22`; original review finding `DR-0154`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0153

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0154
- Review unit: `components/Operations/Events`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/Events/System/Capabilities/Psr14/Psr14ListenerProviderAdapter.php:35` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/Events/System/Capabilities/Psr14/Psr14ListenerProviderAdapter.php:35`; original review finding `DR-0155`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0154

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0155
- Review unit: `components/Operations/Events`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Operations/Events/System/Capabilities/ResolveEventListeners/ResolveEventListeners.php:28` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Operations/Events/System/Capabilities/ResolveEventListeners/ResolveEventListeners.php:28`; original review finding `DR-0156`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0155

## 12. Strict Code Review Decision

Decision: **TARGETED_REDESIGN**.

Reason: highest severity `HIGH`, strict finding count `10`, tests `yes (5)`, confidence `HIGH`. KEEP_AND_IMPROVE is not used when BLOCKER/HIGH remains.
