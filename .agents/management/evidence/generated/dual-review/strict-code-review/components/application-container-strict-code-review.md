# Strict Code Review: components/Application/Container

Generated: 2026-05-19T23:31:00+02:00

## 1. Scope Gate

- Review unit ID: RUC-008
- Root type: COMPONENT
- Path: `components/Application/Container`
- Purpose inferred from code: `components/Application/Container` owns a runtime or user action flow inside the AvaX component tree.
- Primary consumers: public API callers and/or adjacent AvaX framework/component units inferred from `PublicSurface`, `Flows`, `Capabilities`, and test ownership.
- Runtime context: long-lived/runtime-sensitive.
- Lifecycle classification: runtime or user action flow.
- Public API stability requirement: public API stable boundary expected.
- Non-goals for the review: no code changes, no test changes, no folder moves, no API changes, no remediation backlog rewrite.

## 2. As-Built Execution Flow

```text
consumer/test -> components/Application/Container/System/PublicSurface (if present) -> Flows (if present) -> Capabilities -> Configuration/Foundation support
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

Responsibility boundaries are: **STRESSED**.

## 5. Mutability and Runtime Safety

- Classification: **JUSTIFIED**.
- Static mutable state/request scope/global state/lazy singleton/cache/reset safety were checked through available runtime and direct-instantiation gates plus concrete findings.
- If no finding is listed here, that means no concrete finding was produced in this pass, not unconditional runtime GREEN.

## 6. System Invariants for the Unit

| Invariant | Enforced where | Evidence | Status |
|---|---|---|---|
| Public entrypoints delegate and do not own internal assembly | PublicSurface/Configuration boundary | public surface and direct-instantiation gates | PARTIAL |
| Dependency creation belongs to Configuration/approved composition context | Configuration/ServiceProvider/builders | direct-instantiation and runtime-composition gates | PARTIAL |
| Behavior is backed by tests, not smoke-only claims | tests tree | test discovery: yes (2) | PARTIAL |
| Ownership follows flow/capability/component shape | System folder taxonomy | component suite and canonical shape gates | ENFORCED |

## 7. Correctness and Design Review

- Highest strict severity: HIGH.
- Finding count: 33.
- Review lens applied: correctness, cohesion, coupling, naming, complexity, null/error handling, type safety, immutability, side effects, public API stability, hidden behavior, extensibility cost, and maintainability.

## 8. Security Review

- Security-sensitive checks covered unsafe deserialization, file/path handling, command execution, SSRF, injection, XSS/CSRF/session/cookie risk, auth/authz, secrets/logging, crypto/hashing, unsafe defaults, and negative proof where applicable.
- Security-sensitive unresolved concrete findings are HIGH/BLOCKER unless explicitly proven otherwise.

## 9. Performance and Memory Review

- Checked hot-path work, object graph creation, reflection, filesystem/network I/O, unbounded arrays/caches, expensive construction, and worker memory leak patterns through available gates and finding evidence.

## 10. Test Quality Review

- Tests detected: yes (2).
- Test quality is considered proven only where behavior/failure/boundary tests are visible in current evidence; scanner-only absence is not proof.

## 11. Strict Code Review Findings

### Finding: PublicSurface directly instantiates collaborators (2 `new` expressions detected).

- Finding ID: SCR-0102
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: PublicSurface directly instantiates collaborators (2 `new` expressions detected).
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/Application/Container/System/PublicSurface/Container.php:59,137` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/PublicSurface/Container.php:59,137`; original review finding `DR-0103`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0102

### Finding: Null-coalescing fallback instantiates a dependency.

- Finding ID: SCR-0103
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Null-coalescing fallback instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Container/System/Capabilities/Resolution/ResolveDependencies.php:92` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Resolution/ResolveDependencies.php:92`; original review finding `DR-0104`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0103

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0104
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Container/System/Capabilities/Providers/ProviderRegistry.php:26` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Providers/ProviderRegistry.php:26`; original review finding `DR-0105`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0104

### Finding: Null-coalescing fallback instantiates a dependency.

- Finding ID: SCR-0105
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Null-coalescing fallback instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Container/System/Capabilities/Execution/Injection/Invocation/FunctionCaller.php:62` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Execution/Injection/Invocation/FunctionCaller.php:62`; original review finding `DR-0106`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0105

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0106
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Container/System/Capabilities/Composition/Compilation/ServiceCompiler.php:29` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Composition/Compilation/ServiceCompiler.php:29`; original review finding `DR-0107`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0106

### Finding: Null-coalescing fallback instantiates a dependency.

- Finding ID: SCR-0107
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Null-coalescing fallback instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Container/System/Capabilities/Declaration/Bindings/DependencyRegistry.php:122` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Declaration/Bindings/DependencyRegistry.php:122`; original review finding `DR-0108`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0107

### Finding: Null-coalescing fallback instantiates a dependency.

- Finding ID: SCR-0108
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Null-coalescing fallback instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Container/System/Capabilities/Declaration/Bindings/DependencyRegistry.php:274` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Declaration/Bindings/DependencyRegistry.php:274`; original review finding `DR-0109`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0108

### Finding: Null-coalescing fallback instantiates a dependency.

- Finding ID: SCR-0109
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Null-coalescing fallback instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistry.php:122` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistry.php:122`; original review finding `DR-0110`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0109

### Finding: Null-coalescing fallback instantiates a dependency.

- Finding ID: SCR-0110
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Null-coalescing fallback instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistry.php:273` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistry.php:273`; original review finding `DR-0111`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0110

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0111
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Container/System/Capabilities/Declaration/Bindings/DependencyRegistration.php:82` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Declaration/Bindings/DependencyRegistration.php:82`; original review finding `DR-0112`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0111

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0112
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistration.php:85` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistration.php:85`; original review finding `DR-0113`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0112

### Finding: Class/file is 538 lines (>300).

- Finding ID: SCR-0438
- Review unit: `components/Application/Container`
- Severity: LOW
- Risk level: Low
- Symptom: Class/file is 538 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Container/System/Foundation/DIContainer.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Foundation/DIContainer.php`; original review finding `DR-0439`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: NO
- Related how-to deviation IDs if known: HTD-0438

### Finding: Class/file is 318 lines (>300).

- Finding ID: SCR-0439
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 318 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Container/System/Capabilities/Runtime/ServicePool.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Runtime/ServicePool.php`; original review finding `DR-0440`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0439

### Finding: Class/file is 318 lines (>300).

- Finding ID: SCR-0440
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 318 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Container/System/Capabilities/Runtime/DependencyPool.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Runtime/DependencyPool.php`; original review finding `DR-0441`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0440

### Finding: Constructor has 10 parameters.

- Finding ID: SCR-0441
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 10 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Container/System/Capabilities/Resolution/LifetimePlan.php:33` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Resolution/LifetimePlan.php:33`; original review finding `DR-0442`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0441

### Finding: Constructor has 16 parameters.

- Finding ID: SCR-0442
- Review unit: `components/Application/Container`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 16 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Container/System/Capabilities/Composition/CreateContainerConfig.php:87` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Composition/CreateContainerConfig.php:87`; original review finding `DR-0443`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0442

### Finding: Class/file is 516 lines (>300).

- Finding ID: SCR-0443
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 516 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Container/System/Capabilities/Composition/CreateContainerConfig.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Composition/CreateContainerConfig.php`; original review finding `DR-0444`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0443

### Finding: Class/file is 653 lines (>300).

- Finding ID: SCR-0444
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 653 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Container/System/Capabilities/ContextualContainer/ContextContainer.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/ContextualContainer/ContextContainer.php`; original review finding `DR-0445`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0444

### Finding: Constructor has 19 parameters.

- Finding ID: SCR-0445
- Review unit: `components/Application/Container`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 19 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Container/System/Capabilities/ContainerObservability/Observability/RuntimeReport.php:35` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/ContainerObservability/Observability/RuntimeReport.php:35`; original review finding `DR-0446`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0445

### Finding: Class/file is 341 lines (>300).

- Finding ID: SCR-0446
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 341 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Container/System/Capabilities/ContainerObservability/Observability/GraphExporter.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/ContainerObservability/Observability/GraphExporter.php`; original review finding `DR-0447`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0446

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0447
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Container/System/Capabilities/Composition/Assembly/RuntimeAssembly.php:21` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Composition/Assembly/RuntimeAssembly.php:21`; original review finding `DR-0448`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0447

### Finding: Constructor has 35 parameters.

- Finding ID: SCR-0448
- Review unit: `components/Application/Container`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 35 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Container/System/Capabilities/Composition/Compilation/ArtifactMetadata.php:35` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Composition/Compilation/ArtifactMetadata.php:35`; original review finding `DR-0449`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0448

### Finding: Class/file is 436 lines (>300).

- Finding ID: SCR-0449
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 436 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Container/System/Capabilities/Composition/Compilation/ArtifactMetadata.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Composition/Compilation/ArtifactMetadata.php`; original review finding `DR-0450`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0449

### Finding: Constructor has 33 parameters.

- Finding ID: SCR-0450
- Review unit: `components/Application/Container`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 33 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Container/System/Capabilities/Composition/Compilation/CompileReport.php:25` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Composition/Compilation/CompileReport.php:25`; original review finding `DR-0451`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0450

### Finding: Constructor has 20 parameters.

- Finding ID: SCR-0451
- Review unit: `components/Application/Container`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 20 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Container/System/Capabilities/Composition/Compilation/CompileContainer.php:63` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Composition/Compilation/CompileContainer.php:63`; original review finding `DR-0452`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0451

### Finding: Class/file is 1310 lines (>300).

- Finding ID: SCR-0452
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 1310 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Container/System/Capabilities/Composition/Compilation/CompileContainer.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Composition/Compilation/CompileContainer.php`; original review finding `DR-0453`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0452

### Finding: Constructor has 20 parameters.

- Finding ID: SCR-0453
- Review unit: `components/Application/Container`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 20 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Container/System/Capabilities/Declaration/Ownership/RegistrationMetadata.php:62` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Declaration/Ownership/RegistrationMetadata.php:62`; original review finding `DR-0454`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0453

### Finding: Class/file is 454 lines (>300).

- Finding ID: SCR-0454
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 454 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Container/System/Capabilities/Declaration/Ownership/RegistrationMetadata.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Declaration/Ownership/RegistrationMetadata.php`; original review finding `DR-0455`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0454

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0455
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Container/System/Capabilities/Declaration/Blueprints/DependencyBlueprint.php:23` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Declaration/Blueprints/DependencyBlueprint.php:23`; original review finding `DR-0456`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0455

### Finding: Class/file is 1150 lines (>300).

- Finding ID: SCR-0456
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 1150 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Container/System/Capabilities/Declaration/Bindings/DependencyRegistry.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Declaration/Bindings/DependencyRegistry.php`; original review finding `DR-0457`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0456

### Finding: Class/file is 1148 lines (>300).

- Finding ID: SCR-0457
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 1148 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistry.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistry.php`; original review finding `DR-0458`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0457

### Finding: Class/file is 423 lines (>300).

- Finding ID: SCR-0458
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 423 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Container/System/Capabilities/Declaration/Bindings/DependencyRegistration.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Declaration/Bindings/DependencyRegistration.php`; original review finding `DR-0459`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0458

### Finding: Class/file is 425 lines (>300).

- Finding ID: SCR-0459
- Review unit: `components/Application/Container`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 425 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistration.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistration.php`; original review finding `DR-0460`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0459

## 12. Strict Code Review Decision

Decision: **TARGETED_REDESIGN**.

Reason: highest severity `HIGH`, strict finding count `33`, tests `yes (2)`, confidence `HIGH`. KEEP_AND_IMPROVE is not used when BLOCKER/HIGH remains.
