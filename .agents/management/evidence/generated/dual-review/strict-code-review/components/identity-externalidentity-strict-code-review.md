# Strict Code Review: components/Identity/ExternalIdentity

Generated: 2026-05-19T23:31:00+02:00

## 1. Scope Gate

- Review unit ID: RUC-051
- Root type: COMPONENT
- Path: `components/Identity/ExternalIdentity`
- Purpose inferred from code: `components/Identity/ExternalIdentity` owns a runtime or user action flow inside the AvaX component tree.
- Primary consumers: public API callers and/or adjacent AvaX framework/component units inferred from `PublicSurface`, `Flows`, `Capabilities`, and test ownership.
- Runtime context: long-lived/runtime-sensitive.
- Lifecycle classification: runtime or user action flow.
- Public API stability requirement: public API stable boundary expected.
- Non-goals for the review: no code changes, no test changes, no folder moves, no API changes, no remediation backlog rewrite.

## 2. As-Built Execution Flow

```text
consumer/test -> components/Identity/ExternalIdentity/System/PublicSurface (if present) -> Flows (if present) -> Capabilities -> Configuration/Foundation support
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
- Finding count: 30.
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

- Finding ID: SCR-0354
- Review unit: `components/Identity/ExternalIdentity`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/UpdateClient/UpdateClient.php:31` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/UpdateClient/UpdateClient.php:31`; original review finding `DR-0355`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0354

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0355
- Review unit: `components/Identity/ExternalIdentity`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/ReadWorkloadIdentities/ReadWorkloadIdentities.php:25` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/ReadWorkloadIdentities/ReadWorkloadIdentities.php:25`; original review finding `DR-0356`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0355

### Finding: Constructor has 10 parameters.

- Finding ID: SCR-0571
- Review unit: `components/Identity/ExternalIdentity`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 10 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/SingleSignOn/SingleSignOn.php:31` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/SingleSignOn/SingleSignOn.php:31`; original review finding `DR-0572`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0571

### Finding: Constructor has 14 parameters.

- Finding ID: SCR-0572
- Review unit: `components/Identity/ExternalIdentity`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 14 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OAuth/OAuth.php:41` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/OAuth.php:41`; original review finding `DR-0573`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0572

### Finding: Constructor has 20 parameters.

- Finding ID: SCR-0573
- Review unit: `components/Identity/ExternalIdentity`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 20 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/OidcProviderMetadata.php:21` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/OidcProviderMetadata.php:21`; original review finding `DR-0574`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0573

### Finding: Constructor has 12 parameters.

- Finding ID: SCR-0574
- Review unit: `components/Identity/ExternalIdentity`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 12 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/OpenSslOidcProvider.php:26` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/OpenSslOidcProvider.php:26`; original review finding `DR-0575`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0574

### Finding: Class/file is 310 lines (>300).

- Finding ID: SCR-0575
- Review unit: `components/Identity/ExternalIdentity`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 310 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/OpenSslOidcProvider.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/OpenSslOidcProvider.php`; original review finding `DR-0576`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0575

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0576
- Review unit: `components/Identity/ExternalIdentity`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/OidcRequestObject.php:16` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/OidcRequestObject.php:16`; original review finding `DR-0577`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0576

### Finding: Constructor has 9 parameters.

- Finding ID: SCR-0577
- Review unit: `components/Identity/ExternalIdentity`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 9 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/BackChannelLogout/BackChannelLogout.php:23` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/BackChannelLogout/BackChannelLogout.php:23`; original review finding `DR-0578`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0577

### Finding: Constructor has 9 parameters.

- Finding ID: SCR-0578
- Review unit: `components/Identity/ExternalIdentity`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 9 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/ValidateRequestObject/ValidatedRequestObject.php:18` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/ValidateRequestObject/ValidatedRequestObject.php:18`; original review finding `DR-0579`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0578

### Finding: Class/file is 378 lines (>300).

- Finding ID: SCR-0579
- Review unit: `components/Identity/ExternalIdentity`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 378 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/PushAuthorizationRequest/PushAuthorizationRequest.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/PushAuthorizationRequest/PushAuthorizationRequest.php`; original review finding `DR-0580`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0579

### Finding: Constructor has 12 parameters.

- Finding ID: SCR-0580
- Review unit: `components/Identity/ExternalIdentity`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 12 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/PushAuthorizationRequest/PushAuthorizationRequestData.php:18` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/PushAuthorizationRequest/PushAuthorizationRequestData.php:18`; original review finding `DR-0581`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0580

### Finding: Constructor has 10 parameters.

- Finding ID: SCR-0581
- Review unit: `components/Identity/ExternalIdentity`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 10 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/PushAuthorizationRequest/PushedAuthorizationRequest.php:19` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/PushAuthorizationRequest/PushedAuthorizationRequest.php:19`; original review finding `DR-0582`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0581

### Finding: Constructor has 9 parameters.

- Finding ID: SCR-0582
- Review unit: `components/Identity/ExternalIdentity`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 9 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/FrontChannelLogout/FrontChannelLogout.php:23` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/FrontChannelLogout/FrontChannelLogout.php:23`; original review finding `DR-0583`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0582

### Finding: Constructor has 13 parameters.

- Finding ID: SCR-0583
- Review unit: `components/Identity/ExternalIdentity`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 13 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/OAuthTokenGrant.php:24` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/OAuthTokenGrant.php:24`; original review finding `DR-0584`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0583

### Finding: Constructor has 23 parameters.

- Finding ID: SCR-0584
- Review unit: `components/Identity/ExternalIdentity`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 23 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Elements/OAuthClient.php:48` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Elements/OAuthClient.php:48`; original review finding `DR-0585`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0584

### Finding: Constructor has 13 parameters.

- Finding ID: SCR-0585
- Review unit: `components/Identity/ExternalIdentity`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 13 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Elements/AuthorizationCodeRecord.php:19` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Elements/AuthorizationCodeRecord.php:19`; original review finding `DR-0586`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0585

### Finding: Class/file is 405 lines (>300).

- Finding ID: SCR-0586
- Review unit: `components/Identity/ExternalIdentity`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 405 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Elements/InMemoryOAuthClientRegistry.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Elements/InMemoryOAuthClientRegistry.php`; original review finding `DR-0587`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0586

### Finding: Constructor has 10 parameters.

- Finding ID: SCR-0587
- Review unit: `components/Identity/ExternalIdentity`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 10 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/ExchangeAuthorizationCode/ExchangeAuthorizationCode.php:31` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/ExchangeAuthorizationCode/ExchangeAuthorizationCode.php:31`; original review finding `DR-0588`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0587

### Finding: Constructor has 9 parameters.

- Finding ID: SCR-0588
- Review unit: `components/Identity/ExternalIdentity`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 9 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/ExchangeAuthorizationCode/ExchangeAuthorizationCodeData.php:12` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/ExchangeAuthorizationCode/ExchangeAuthorizationCodeData.php:12`; original review finding `DR-0589`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0588

### Finding: Constructor has 13 parameters.

- Finding ID: SCR-0589
- Review unit: `components/Identity/ExternalIdentity`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 13 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/IntrospectToken/TokenIntrospection.php:20` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/IntrospectToken/TokenIntrospection.php:20`; original review finding `DR-0590`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0589

### Finding: Constructor has 19 parameters.

- Finding ID: SCR-0590
- Review unit: `components/Identity/ExternalIdentity`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 19 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/UpdateClient/UpdateClientData.php:46` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/UpdateClient/UpdateClientData.php:46`; original review finding `DR-0591`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0590

### Finding: Constructor has 9 parameters.

- Finding ID: SCR-0591
- Review unit: `components/Identity/ExternalIdentity`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 9 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/AuthorizeCode/AuthorizeCode.php:30` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/AuthorizeCode/AuthorizeCode.php:30`; original review finding `DR-0592`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0591

### Finding: Constructor has 11 parameters.

- Finding ID: SCR-0592
- Review unit: `components/Identity/ExternalIdentity`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 11 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/AuthorizeCode/AuthorizeCodeData.php:18` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/AuthorizeCode/AuthorizeCodeData.php:18`; original review finding `DR-0593`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0592

### Finding: Constructor has 18 parameters.

- Finding ID: SCR-0593
- Review unit: `components/Identity/ExternalIdentity`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 18 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/RegisterClient/RegisterClientData.php:46` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/RegisterClient/RegisterClientData.php:46`; original review finding `DR-0594`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0593

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0594
- Review unit: `components/Identity/ExternalIdentity`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/ExchangeClientCredentials/ExchangeClientCredentialsData.php:18` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/ExchangeClientCredentials/ExchangeClientCredentialsData.php:18`; original review finding `DR-0595`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0594

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0595
- Review unit: `components/Identity/ExternalIdentity`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/ExchangeRefreshToken/ExchangeRefreshToken.php:28` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/ExchangeRefreshToken/ExchangeRefreshToken.php:28`; original review finding `DR-0596`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0595

### Finding: Constructor has 17 parameters.

- Finding ID: SCR-0596
- Review unit: `components/Identity/ExternalIdentity`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 17 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/SingleSignOn/Federation/FederationConnection.php:22` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/SingleSignOn/Federation/FederationConnection.php:22`; original review finding `DR-0597`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0596

### Finding: Constructor has 14 parameters.

- Finding ID: SCR-0597
- Review unit: `components/Identity/ExternalIdentity`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor has 14 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/SingleSignOn/FederationRuntime/CompleteFederatedLogin/CompleteFederatedLogin.php:36` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/SingleSignOn/FederationRuntime/CompleteFederatedLogin/CompleteFederatedLogin.php:36`; original review finding `DR-0598`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0597

### Finding: Constructor has 9 parameters.

- Finding ID: SCR-0598
- Review unit: `components/Identity/ExternalIdentity`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 9 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Identity/ExternalIdentity/System/Capabilities/SingleSignOn/FederationRuntime/RegisterConnection/RegisterFederationConnectionData.php:19` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Identity/ExternalIdentity/System/Capabilities/SingleSignOn/FederationRuntime/RegisterConnection/RegisterFederationConnectionData.php:19`; original review finding `DR-0599`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0598

## 12. Strict Code Review Decision

Decision: **TARGETED_REDESIGN**.

Reason: highest severity `HIGH`, strict finding count `30`, tests `yes (1)`, confidence `HIGH`. KEEP_AND_IMPROVE is not used when BLOCKER/HIGH remains.
