# Strict Code Review: components/DataStack/DataTransfer

Generated: 2026-05-19T23:31:00+02:00

## 1. Scope Gate

- Review unit ID: RUC-021
- Root type: COMPONENT
- Path: `components/DataStack/DataTransfer`
- Purpose inferred from code: `components/DataStack/DataTransfer` owns a runtime or user action flow inside the AvaX component tree.
- Primary consumers: public API callers and/or adjacent AvaX framework/component units inferred from `PublicSurface`, `Flows`, `Capabilities`, and test ownership.
- Runtime context: long-lived/runtime-sensitive.
- Lifecycle classification: runtime or user action flow.
- Public API stability requirement: public API stable boundary expected.
- Non-goals for the review: no code changes, no test changes, no folder moves, no API changes, no remediation backlog rewrite.

## 2. As-Built Execution Flow

```text
consumer/test -> components/DataStack/DataTransfer/System/PublicSurface (if present) -> Flows (if present) -> Capabilities -> Configuration/Foundation support
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
| Behavior is backed by tests, not smoke-only claims | tests tree | test discovery: yes (12) | PARTIAL |
| Ownership follows flow/capability/component shape | System folder taxonomy | component suite and canonical shape gates | ENFORCED |

## 7. Correctness and Design Review

- Highest strict severity: HIGH.
- Finding count: 17.
- Review lens applied: correctness, cohesion, coupling, naming, complexity, null/error handling, type safety, immutability, side effects, public API stability, hidden behavior, extensibility cost, and maintainability.

## 8. Security Review

- Security-sensitive checks covered unsafe deserialization, file/path handling, command execution, SSRF, injection, XSS/CSRF/session/cookie risk, auth/authz, secrets/logging, crypto/hashing, unsafe defaults, and negative proof where applicable.
- Security-sensitive unresolved concrete findings are HIGH/BLOCKER unless explicitly proven otherwise.

## 9. Performance and Memory Review

- Checked hot-path work, object graph creation, reflection, filesystem/network I/O, unbounded arrays/caches, expensive construction, and worker memory leak patterns through available gates and finding evidence.

## 10. Test Quality Review

- Tests detected: yes (12).
- Test quality is considered proven only where behavior/failure/boundary tests are visible in current evidence; scanner-only absence is not proof.

## 11. Strict Code Review Findings

### Finding: ServiceProvider coverage gate reports real code but no component ServiceProvider.

- Finding ID: SCR-0023
- Review unit: `components/DataStack/DataTransfer`
- Severity: HIGH
- Risk level: High
- Symptom: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/DataTransfer` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/DataTransfer`; original review finding `DR-0023`; validation gate `php tooling/refactor/check-service-provider-coverage.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Add a precise System/Configuration/*ServiceProvider that registers existing dependencies without creating new feature behavior.
- Test/gate proof required: php tooling/refactor/check-service-provider-coverage.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0023

### Finding: DataTransfer compiled metadata capabilities use raw file/directory operations classified NEEDS_D

- Finding ID: SCR-0045
- Review unit: `components/DataStack/DataTransfer`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: DataTransfer compiled metadata capabilities use raw file/directory operations classified NEEDS_DESIGN_DECISION.
- Root cause: The unit boundary allows security boundary pressure to appear in `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/CompileClassAttributes.php; components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/CompileDataShapeSchema.php` instead of keeping the responsibility isolated.
- Impact: Security boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/CompileClassAttributes.php; components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/CompileDataShapeSchema.php`; original review finding `DR-0046`; validation gate `php tooling/refactor/check-raw-file-operations.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Decide compiled metadata ownership; route through Filesystem/Storage or document a compile-time exception with tests.
- Test/gate proof required: php tooling/refactor/check-raw-file-operations.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0045

### Finding: PublicSurface directly instantiates collaborators (10 `new` expressions detected).

- Finding ID: SCR-0286
- Review unit: `components/DataStack/DataTransfer`
- Severity: HIGH
- Risk level: High
- Symptom: PublicSurface directly instantiates collaborators (10 `new` expressions detected).
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/DataStack/DataTransfer/System/PublicSurface/DataTransfer.php:67,84,92,97,102,110,118,141...` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/DataTransfer/System/PublicSurface/DataTransfer.php:67,84,92,97,102,110,118,141...`; original review finding `DR-0287`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0286

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0287
- Review unit: `components/DataStack/DataTransfer`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/DataTransfer/System/Flows/SerializeDataObject/ConvertDataObjectToJsonApi.php:18` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/DataTransfer/System/Flows/SerializeDataObject/ConvertDataObjectToJsonApi.php:18`; original review finding `DR-0288`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0287

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0288
- Review unit: `components/DataStack/DataTransfer`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/DataTransfer/System/Flows/SerializeDataObject/ConvertDataObjectToArray.php:23` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/DataTransfer/System/Flows/SerializeDataObject/ConvertDataObjectToArray.php:23`; original review finding `DR-0289`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0288

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0289
- Review unit: `components/DataStack/DataTransfer`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/DataTransfer/System/Flows/ReadDataObject/ReadDataObject.php:20` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/DataTransfer/System/Flows/ReadDataObject/ReadDataObject.php:20`; original review finding `DR-0290`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0289

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0290
- Review unit: `components/DataStack/DataTransfer`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/DataTransfer/System/Flows/ReadDataObject/ReadDataObject.php:21` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/DataTransfer/System/Flows/ReadDataObject/ReadDataObject.php:21`; original review finding `DR-0291`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0290

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0291
- Review unit: `components/DataStack/DataTransfer`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/AttributeCompiler.php:35` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/AttributeCompiler.php:35`; original review finding `DR-0292`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0291

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0292
- Review unit: `components/DataStack/DataTransfer`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/InspectDataShape.php:13` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/InspectDataShape.php:13`; original review finding `DR-0293`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0292

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0293
- Review unit: `components/DataStack/DataTransfer`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/DataTransfer/System/Capabilities/LegacyTransfer/SerializeLegacyDTO.php:19` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/DataTransfer/System/Capabilities/LegacyTransfer/SerializeLegacyDTO.php:19`; original review finding `DR-0294`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0293

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0294
- Review unit: `components/DataStack/DataTransfer`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/DataStack/DataTransfer/System/Capabilities/LegacyTransfer/AbstractDTO.php:27` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/DataTransfer/System/Capabilities/LegacyTransfer/AbstractDTO.php:27`; original review finding `DR-0295`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0294

### Finding: Constructor has 9 parameters.

- Finding ID: SCR-0543
- Review unit: `components/DataStack/DataTransfer`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 9 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/DataTransfer/System/Configuration/DataTransferConfig.php:15` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/DataTransfer/System/Configuration/DataTransferConfig.php:15`; original review finding `DR-0544`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0543

### Finding: Class/file is 418 lines (>300).

- Finding ID: SCR-0544
- Review unit: `components/DataStack/DataTransfer`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 418 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/DataTransfer/System/Flows/CreateDataObject/CreateDataObject.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/DataTransfer/System/Flows/CreateDataObject/CreateDataObject.php`; original review finding `DR-0545`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0544

### Finding: Class/file is 319 lines (>300).

- Finding ID: SCR-0545
- Review unit: `components/DataStack/DataTransfer`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 319 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/CompileClassAttributes.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/CompileClassAttributes.php`; original review finding `DR-0546`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0545

### Finding: Constructor has 11 parameters.

- Finding ID: SCR-0546
- Review unit: `components/DataStack/DataTransfer`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 11 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/CompiledAttributeMetadata.php:37` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/CompiledAttributeMetadata.php:37`; original review finding `DR-0547`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0546

### Finding: Constructor has 12 parameters.

- Finding ID: SCR-0547
- Review unit: `components/DataStack/DataTransfer`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 12 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/DataField.php:21` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/DataField.php:21`; original review finding `DR-0548`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0547

### Finding: Constructor has 9 parameters.

- Finding ID: SCR-0548
- Review unit: `components/DataStack/DataTransfer`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 9 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/CompiledSchemaMetadata.php:29` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/CompiledSchemaMetadata.php:29`; original review finding `DR-0549`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0548

## 12. Strict Code Review Decision

Decision: **TARGETED_REDESIGN**.

Reason: highest severity `HIGH`, strict finding count `17`, tests `yes (12)`, confidence `HIGH`. KEEP_AND_IMPROVE is not used when BLOCKER/HIGH remains.
