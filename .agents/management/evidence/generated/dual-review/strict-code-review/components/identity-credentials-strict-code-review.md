# Strict Code Review: components/Identity/Credentials

Generated: 2026-05-19T23:31:00+02:00

## 1. Scope Gate

- Review unit ID: RUC-050
- Root type: COMPONENT
- Path: `components/Identity/Credentials`
- Purpose inferred from code: `components/Identity/Credentials` owns a runtime or user action flow inside the AvaX component tree.
- Primary consumers: public API callers and/or adjacent AvaX framework/component units inferred from `PublicSurface`, `Flows`, `Capabilities`, and test ownership.
- Runtime context: long-lived/runtime-sensitive.
- Lifecycle classification: runtime or user action flow.
- Public API stability requirement: public API stable boundary expected.
- Non-goals for the review: no code changes, no test changes, no folder moves, no API changes, no remediation backlog rewrite.

## 2. As-Built Execution Flow

```text
consumer/test -> components/Identity/Credentials/System/PublicSurface (if present) -> Flows (if present) -> Capabilities -> Configuration/Foundation support
```

- State is created: primarily in Configuration/Builders or constructors where present; findings below flag creation outside approved composition contexts.
- State is mutated: within flows/capabilities when behavior requires it; request/runtime sensitive units require reset proof.
- Decisions are made: by flow/capability classes inferred from folder ownership; explicit semantic branch review is limited to concrete finding evidence.
- Pure/mechanical execution: expected in Foundation/value/result helpers and simple delegation surfaces.
- Review gap note: this file is evidence-backed by repository scans, prior generated unit review, and targeted source inspection; it is not a claim that every branch is semantically proven.

## 3. Primary Axis and Secondary Axis

This unit is fundamentally organized around runtime or user action flow.

Design Risk: Dual-axis complexity detected: complexity/maintainability pressure, configuration/DI pressure.

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
| Public entrypoints delegate and do not own internal assembly | PublicSurface/Configuration boundary | public surface and direct-instantiation gates | ENFORCED |
| Dependency creation belongs to Configuration/approved composition context | Configuration/ServiceProvider/builders | direct-instantiation and runtime-composition gates | NOT_ENFORCED |
| Behavior is backed by tests, not smoke-only claims | tests tree | test discovery: yes (1) | PARTIAL |
| Ownership follows flow/capability/component shape | System folder taxonomy | component suite and canonical shape gates | ENFORCED |

## 7. Correctness and Design Review

- Highest strict severity: HIGH.
- Finding count: 16.
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

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0348
- Review unit: `components/Identity/Credentials`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Backup/GenerateBackupCodes.php:35` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Backup/GenerateBackupCodes.php:35`; original review finding `DR-0349`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0348

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0349
- Review unit: `components/Identity/Credentials`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Backup/GenerateBackupCodes.php:36` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Backup/GenerateBackupCodes.php:36`; original review finding `DR-0350`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0349

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0552
- Review unit: `components/Identity/Credentials`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Credentials/System/Capabilities/Passkey/Passkey.php:30` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Credentials/System/Capabilities/Passkey/Passkey.php:30`; original review finding `DR-0553`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0552

### Finding: Constructor has 10 parameters.

- Finding ID: SCR-0553
- Review unit: `components/Identity/Credentials`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 10 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Credentials/System/Capabilities/Mfa/Mfa.php:32` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Credentials/System/Capabilities/Mfa/Mfa.php:32`; original review finding `DR-0554`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0553

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0554
- Review unit: `components/Identity/Credentials`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Disable/DisableMfa.php:27` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Disable/DisableMfa.php:27`; original review finding `DR-0555`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0554

### Finding: Constructor has 9 parameters.

- Finding ID: SCR-0555
- Review unit: `components/Identity/Credentials`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 9 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Recover/ConfirmMfaRecovery.php:26` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Recover/ConfirmMfaRecovery.php:26`; original review finding `DR-0556`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0555

### Finding: Constructor has 9 parameters.

- Finding ID: SCR-0556
- Review unit: `components/Identity/Credentials`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 9 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Enroll/MfaEnrollment.php:17` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Enroll/MfaEnrollment.php:17`; original review finding `DR-0557`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0556

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0557
- Review unit: `components/Identity/Credentials`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Enroll/StartMfaEnrollment.php:30` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Enroll/StartMfaEnrollment.php:30`; original review finding `DR-0558`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0557

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0558
- Review unit: `components/Identity/Credentials`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Verify/StartMfaChallenge.php:30` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Verify/StartMfaChallenge.php:30`; original review finding `DR-0559`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0558

### Finding: Constructor has 13 parameters.

- Finding ID: SCR-0559
- Review unit: `components/Identity/Credentials`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 13 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Verify/VerifyMfaChallenge.php:35` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Verify/VerifyMfaChallenge.php:35`; original review finding `DR-0560`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0559

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0560
- Review unit: `components/Identity/Credentials`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Verify/MfaChallengeRecord.php:25` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Verify/MfaChallengeRecord.php:25`; original review finding `DR-0561`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0560

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0561
- Review unit: `components/Identity/Credentials`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Records/MfaEnrollmentRecord.php:17` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Records/MfaEnrollmentRecord.php:17`; original review finding `DR-0562`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0561

### Finding: Constructor has 10 parameters.

- Finding ID: SCR-0562
- Review unit: `components/Identity/Credentials`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 10 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Credentials/System/Capabilities/Passkey/Runtime/BeginRegistration/BeginPasskeyRegistration.php:28` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Credentials/System/Capabilities/Passkey/Runtime/BeginRegistration/BeginPasskeyRegistration.php:28`; original review finding `DR-0563`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0562

### Finding: Constructor has 11 parameters.

- Finding ID: SCR-0563
- Review unit: `components/Identity/Credentials`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 11 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Credentials/System/Capabilities/Passkey/Runtime/CompleteAuthentication/CompletePasskeyAuthentication.php:28` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Credentials/System/Capabilities/Passkey/Runtime/CompleteAuthentication/CompletePasskeyAuthentication.php:28`; original review finding `DR-0564`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0563

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0564
- Review unit: `components/Identity/Credentials`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Credentials/System/Capabilities/Passkey/Runtime/BeginAuthentication/BeginPasskeyAuthentication.php:25` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Credentials/System/Capabilities/Passkey/Runtime/BeginAuthentication/BeginPasskeyAuthentication.php:25`; original review finding `DR-0565`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0564

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0565
- Review unit: `components/Identity/Credentials`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/Credentials/System/Capabilities/Passkey/Runtime/CompleteRegistration/CompletePasskeyRegistration.php:22` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/Credentials/System/Capabilities/Passkey/Runtime/CompleteRegistration/CompletePasskeyRegistration.php:22`; original review finding `DR-0566`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0565

## 12. Strict Code Review Decision

Decision: **TARGETED_REDESIGN**.

Reason: highest severity `HIGH`, strict finding count `16`, tests `yes (1)`, confidence `HIGH`. KEEP_AND_IMPROVE is not used when BLOCKER/HIGH remains.
