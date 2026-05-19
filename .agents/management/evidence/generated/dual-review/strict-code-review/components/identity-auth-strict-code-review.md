# Strict Code Review: components/Identity/Auth

Generated: 2026-05-19T23:31:00+02:00

## 1. Scope Gate

- Review unit ID: RUC-049
- Root type: COMPONENT
- Path: `components/Identity/Auth`
- Purpose inferred from code: `components/Identity/Auth` owns a runtime or user action flow inside the AvaX component tree.
- Primary consumers: public API callers and/or adjacent AvaX framework/component units inferred from `PublicSurface`, `Flows`, `Capabilities`, and test ownership.
- Runtime context: long-lived/runtime-sensitive.
- Lifecycle classification: runtime or user action flow.
- Public API stability requirement: public API stable boundary expected.
- Non-goals for the review: no code changes, no test changes, no folder moves, no API changes, no remediation backlog rewrite.

## 2. As-Built Execution Flow

```text
consumer/test -> components/Identity/Auth/System/PublicSurface (if present) -> Flows (if present) -> Capabilities -> Configuration/Foundation support
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
| Behavior is backed by tests, not smoke-only claims | tests tree | test discovery: yes (3) | PARTIAL |
| Ownership follows flow/capability/component shape | System folder taxonomy | component suite and canonical shape gates | ENFORCED |

## 7. Correctness and Design Review

- Highest strict severity: BLOCKER.
- Finding count: 50.
- Review lens applied: correctness, cohesion, coupling, naming, complexity, null/error handling, type safety, immutability, side effects, public API stability, hidden behavior, extensibility cost, and maintainability.

## 8. Security Review

- Security-sensitive checks covered unsafe deserialization, file/path handling, command execution, SSRF, injection, XSS/CSRF/session/cookie risk, auth/authz, secrets/logging, crypto/hashing, unsafe defaults, and negative proof where applicable.
- Security-sensitive unresolved concrete findings are HIGH/BLOCKER unless explicitly proven otherwise.

## 9. Performance and Memory Review

- Checked hot-path work, object graph creation, reflection, filesystem/network I/O, unbounded arrays/caches, expensive construction, and worker memory leak patterns through available gates and finding evidence.

## 10. Test Quality Review

- Tests detected: yes (3).
- Test quality is considered proven only where behavior/failure/boundary tests are visible in current evidence; scanner-only absence is not proof.

## 11. Strict Code Review Findings

### Finding: PublicSurface directly instantiates collaborators (1 `new` expressions detected).

- Finding ID: SCR-0356
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/Identity/Auth/System/PublicSurface/UserRecord.php:19` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/PublicSurface/UserRecord.php:19`; original review finding `DR-0357`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0356

### Finding: PublicSurface directly instantiates collaborators (1 `new` expressions detected).

- Finding ID: SCR-0357
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/Identity/Auth/System/PublicSurface/User.php:19` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/PublicSurface/User.php:19`; original review finding `DR-0358`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0357

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0358
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/Auth/System/Foundation/Time/Expiry.php:16` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Foundation/Time/Expiry.php:16`; original review finding `DR-0359`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0358

### Finding: Null-coalescing fallback instantiates a dependency.

- Finding ID: SCR-0359
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Null-coalescing fallback instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/Auth/System/Foundation/Time/Expiry.php:16` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Foundation/Time/Expiry.php:16`; original review finding `DR-0360`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0359

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0360
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/Auth/System/Foundation/Time/Expiry.php:23` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Foundation/Time/Expiry.php:23`; original review finding `DR-0361`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0360

### Finding: Null-coalescing fallback instantiates a dependency.

- Finding ID: SCR-0361
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Null-coalescing fallback instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/Auth/System/Foundation/Time/Expiry.php:23` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Foundation/Time/Expiry.php:23`; original review finding `DR-0362`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0361

### Finding: Null-coalescing fallback instantiates a dependency.

- Finding ID: SCR-0362
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Null-coalescing fallback instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:203` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:203`; original review finding `DR-0363`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0362

### Finding: Null-coalescing fallback instantiates a dependency.

- Finding ID: SCR-0363
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Null-coalescing fallback instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:204` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:204`; original review finding `DR-0364`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0363

### Finding: Null-coalescing fallback instantiates a dependency.

- Finding ID: SCR-0364
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Null-coalescing fallback instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:208` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:208`; original review finding `DR-0365`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0364

### Finding: Null-coalescing fallback instantiates a dependency.

- Finding ID: SCR-0365
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Null-coalescing fallback instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:209` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:209`; original review finding `DR-0366`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0365

### Finding: Null-coalescing fallback instantiates a dependency.

- Finding ID: SCR-0366
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Null-coalescing fallback instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:210` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:210`; original review finding `DR-0367`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0366

### Finding: Null-coalescing fallback instantiates a dependency.

- Finding ID: SCR-0367
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Null-coalescing fallback instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:211` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:211`; original review finding `DR-0368`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0367

### Finding: Null-coalescing fallback instantiates a dependency.

- Finding ID: SCR-0368
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Null-coalescing fallback instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php:607` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php:607`; original review finding `DR-0369`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0368

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0369
- Review unit: `components/Identity/Auth`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/Auth/System/Flows/Register/CreateRegisteredUser.php:26` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Flows/Register/CreateRegisteredUser.php:26`; original review finding `DR-0370`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0369

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0370
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/Auth/System/Capabilities/SessionIdentity/SessionIdentity.php:27` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/SessionIdentity/SessionIdentity.php:27`; original review finding `DR-0371`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0370

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0371
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/Auth/System/Capabilities/Tokens/TokenCodec.php:26` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/Tokens/TokenCodec.php:26`; original review finding `DR-0372`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0371

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0372
- Review unit: `components/Identity/Auth`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/DeprovisionUser/DeprovisionUser.php:38` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/DeprovisionUser/DeprovisionUser.php:38`; original review finding `DR-0373`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0372

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0373
- Review unit: `components/Identity/Auth`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/SuspendUser/SuspendUser.php:38` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/SuspendUser/SuspendUser.php:38`; original review finding `DR-0374`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0373

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0374
- Review unit: `components/Identity/Auth`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/ReactivateUser/ReactivateUser.php:23` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/ReactivateUser/ReactivateUser.php:23`; original review finding `DR-0375`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0374

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0375
- Review unit: `components/Identity/Auth`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ReadGroups/ReadScimGroups.php:22` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ReadGroups/ReadScimGroups.php:22`; original review finding `DR-0376`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0375

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0376
- Review unit: `components/Identity/Auth`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ReadUsers/ReadScimUsers.php:30` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ReadUsers/ReadScimUsers.php:30`; original review finding `DR-0377`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0376

### Finding: Constructor has 27 parameters.

- Finding ID: SCR-0599
- Review unit: `components/Identity/Auth`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 27 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthExternalIdentityGraph.php:92` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthExternalIdentityGraph.php:92`; original review finding `DR-0600`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0599

### Finding: Constructor has 43 parameters.

- Finding ID: SCR-0600
- Review unit: `components/Identity/Auth`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 43 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:136` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:136`; original review finding `DR-0601`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0600

### Finding: Class/file is 678 lines (>300).

- Finding ID: SCR-0601
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 678 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php`; original review finding `DR-0602`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0601

### Finding: Configuration builder is 797 lines (>300).

- Finding ID: SCR-0602
- Review unit: `components/Identity/Auth`
- Severity: BLOCKER
- Risk level: Rewrite Risk
- Symptom: Configuration builder is 797 lines (>300).
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php`; original review finding `DR-0603`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split assembly by exact responsibility without changing public API or runtime behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0602

### Finding: Constructor has 12 parameters.

- Finding ID: SCR-0603
- Review unit: `components/Identity/Auth`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 12 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Flows/ChangePassword/ChangePassword.php:34` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Flows/ChangePassword/ChangePassword.php:34`; original review finding `DR-0604`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0603

### Finding: Constructor has 9 parameters.

- Finding ID: SCR-0604
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 9 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Flows/ChangeEmail/BeginEmailChange.php:23` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Flows/ChangeEmail/BeginEmailChange.php:23`; original review finding `DR-0605`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0604

### Finding: Constructor has 11 parameters.

- Finding ID: SCR-0605
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 11 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Flows/ChangeEmail/ConfirmEmailChange.php:23` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Flows/ChangeEmail/ConfirmEmailChange.php:23`; original review finding `DR-0606`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0605

### Finding: Constructor has 9 parameters.

- Finding ID: SCR-0606
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 9 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Flows/RecoverAccess/PasswordReset/ResetPassword.php:23` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Flows/RecoverAccess/PasswordReset/ResetPassword.php:23`; original review finding `DR-0607`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0606

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0607
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Flows/CheckAuthentication/AuthenticateRequest/AuthenticateRequest.php:24` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Flows/CheckAuthentication/AuthenticateRequest/AuthenticateRequest.php:24`; original review finding `DR-0608`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0607

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0608
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Flows/CheckAuthentication/AuthenticateRequest/AuthenticatedUser.php:29` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Flows/CheckAuthentication/AuthenticateRequest/AuthenticatedUser.php:29`; original review finding `DR-0609`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0608

### Finding: Constructor has 12 parameters.

- Finding ID: SCR-0609
- Review unit: `components/Identity/Auth`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 12 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Flows/CheckAuthentication/AuthenticateRequest/AuthenticationContext.php:16` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Flows/CheckAuthentication/AuthenticateRequest/AuthenticationContext.php:16`; original review finding `DR-0610`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0609

### Finding: Constructor has 10 parameters.

- Finding ID: SCR-0610
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 10 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Capabilities/Identity/Identity.php:39` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/Identity/Identity.php:39`; original review finding `DR-0611`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0610

### Finding: Class/file is 871 lines (>300).

- Finding ID: SCR-0611
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 871 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Capabilities/Authentication/DefaultAuth.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/Authentication/DefaultAuth.php`; original review finding `DR-0612`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0611

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0612
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Capabilities/SessionIdentity/SessionIdentity.php:14` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/SessionIdentity/SessionIdentity.php:14`; original review finding `DR-0613`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0612

### Finding: Constructor has 12 parameters.

- Finding ID: SCR-0613
- Review unit: `components/Identity/Auth`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 12 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/SCIM.php:38` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/SCIM.php:38`; original review finding `DR-0614`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0613

### Finding: Constructor has 13 parameters.

- Finding ID: SCR-0614
- Review unit: `components/Identity/Auth`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 13 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Directories/ScimDirectory.php:17` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Directories/ScimDirectory.php:17`; original review finding `DR-0615`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0614

### Finding: Constructor has 9 parameters.

- Finding ID: SCR-0615
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 9 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/DeprovisionUser/DeprovisionUser.php:22` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/DeprovisionUser/DeprovisionUser.php:22`; original review finding `DR-0616`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0615

### Finding: Constructor has 9 parameters.

- Finding ID: SCR-0616
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 9 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/SuspendUser/SuspendUser.php:22` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/SuspendUser/SuspendUser.php:22`; original review finding `DR-0617`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0616

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0617
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ProvisionUser/ScimProvisioningResult.php:14` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ProvisionUser/ScimProvisioningResult.php:14`; original review finding `DR-0618`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0617

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0618
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ProvisionUser/ProvisionScimUserData.php:18` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ProvisionUser/ProvisionScimUserData.php:18`; original review finding `DR-0619`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0618

### Finding: Constructor has 10 parameters.

- Finding ID: SCR-0619
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 10 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ProvisionUser/ProvisionScimUser.php:34` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ProvisionUser/ProvisionScimUser.php:34`; original review finding `DR-0620`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0619

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0620
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ReadUsers/ScimUserProjection.php:16` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ReadUsers/ScimUserProjection.php:16`; original review finding `DR-0621`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0620

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0621
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Capabilities/Identity/User/User.php:21` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/Identity/User/User.php:21`; original review finding `DR-0622`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0621

### Finding: Constructor has 11 parameters.

- Finding ID: SCR-0622
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 11 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Capabilities/Identity/Session/SessionIdentity.php:32` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/Identity/Session/SessionIdentity.php:32`; original review finding `DR-0623`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0622

### Finding: Class/file is 311 lines (>300).

- Finding ID: SCR-0623
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 311 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Capabilities/Identity/Session/SessionIdentity.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/Identity/Session/SessionIdentity.php`; original review finding `DR-0624`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0623

### Finding: Constructor has 10 parameters.

- Finding ID: SCR-0624
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 10 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Capabilities/Identity/Jwt/JwtIdentity.php:38` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/Identity/Jwt/JwtIdentity.php:38`; original review finding `DR-0625`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0624

### Finding: Class/file is 390 lines (>300).

- Finding ID: SCR-0625
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 390 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Capabilities/Identity/Jwt/JwtIdentity.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/Identity/Jwt/JwtIdentity.php`; original review finding `DR-0626`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0625

### Finding: Constructor has 11 parameters.

- Finding ID: SCR-0626
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 11 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Capabilities/Identity/Sessions/Runtime/ActiveSession.php:17` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/Identity/Sessions/Runtime/ActiveSession.php:17`; original review finding `DR-0627`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0626

### Finding: Constructor has 11 parameters.

- Finding ID: SCR-0627
- Review unit: `components/Identity/Auth`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 11 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Auth/System/Capabilities/Identity/Sessions/Registry/SessionRecord.php:17` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Auth/System/Capabilities/Identity/Sessions/Registry/SessionRecord.php:17`; original review finding `DR-0628`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0627

## 12. Strict Code Review Decision

Decision: **TARGETED_REDESIGN**.

Reason: highest severity `BLOCKER`, strict finding count `50`, tests `yes (3)`, confidence `HIGH`. KEEP_AND_IMPROVE is not used when BLOCKER/HIGH remains.
