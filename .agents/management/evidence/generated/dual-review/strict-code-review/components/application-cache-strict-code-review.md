# Strict Code Review: components/Application/Cache

Generated: 2026-05-19T23:31:00+02:00

## 1. Scope Gate

- Review unit ID: RUC-006
- Root type: COMPONENT
- Path: `components/Application/Cache`
- Purpose inferred from code: `components/Application/Cache` owns a runtime or user action flow inside the AvaX component tree.
- Primary consumers: public API callers and/or adjacent AvaX framework/component units inferred from `PublicSurface`, `Flows`, `Capabilities`, and test ownership.
- Runtime context: long-lived/runtime-sensitive.
- Lifecycle classification: runtime or user action flow.
- Public API stability requirement: public API stable boundary expected.
- Non-goals for the review: no code changes, no test changes, no folder moves, no API changes, no remediation backlog rewrite.

## 2. As-Built Execution Flow

```text
consumer/test -> components/Application/Cache/System/PublicSurface (if present) -> Flows (if present) -> Capabilities -> Configuration/Foundation support
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
| Behavior is backed by tests, not smoke-only claims | tests tree | test discovery: yes (29) | PARTIAL |
| Ownership follows flow/capability/component shape | System folder taxonomy | component suite and canonical shape gates | ENFORCED |

## 7. Correctness and Design Review

- Highest strict severity: HIGH.
- Finding count: 64.
- Review lens applied: correctness, cohesion, coupling, naming, complexity, null/error handling, type safety, immutability, side effects, public API stability, hidden behavior, extensibility cost, and maintainability.

## 8. Security Review

- Security-sensitive checks covered unsafe deserialization, file/path handling, command execution, SSRF, injection, XSS/CSRF/session/cookie risk, auth/authz, secrets/logging, crypto/hashing, unsafe defaults, and negative proof where applicable.
- Security-sensitive unresolved concrete findings are HIGH/BLOCKER unless explicitly proven otherwise.

## 9. Performance and Memory Review

- Checked hot-path work, object graph creation, reflection, filesystem/network I/O, unbounded arrays/caches, expensive construction, and worker memory leak patterns through available gates and finding evidence.

## 10. Test Quality Review

- Tests detected: yes (29).
- Test quality is considered proven only where behavior/failure/boundary tests are visible in current evidence; scanner-only absence is not proof.

## 11. Strict Code Review Findings

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0046
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Configuration/CacheConfiguration.php:21` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Configuration/CacheConfiguration.php:21`; original review finding `DR-0047`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0046

### Finding: PublicSurface directly instantiates collaborators (1 `new` expressions detected).

- Finding ID: SCR-0047
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/Application/Cache/System/PublicSurface/AvaxCache.php:153` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/PublicSurface/AvaxCache.php:153`; original review finding `DR-0048`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0047

### Finding: PublicSurface directly instantiates collaborators (4 `new` expressions detected).

- Finding ID: SCR-0048
- Review unit: `components/Application/Cache`
- Severity: HIGH
- Risk level: High
- Symptom: PublicSurface directly instantiates collaborators (4 `new` expressions detected).
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/Application/Cache/System/PublicSurface/CompiledCache.php:31,59,60,61` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/PublicSurface/CompiledCache.php:31,59,60,61`; original review finding `DR-0049`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0048

### Finding: PublicSurface directly instantiates collaborators (4 `new` expressions detected).

- Finding ID: SCR-0049
- Review unit: `components/Application/Cache`
- Severity: HIGH
- Risk level: High
- Symptom: PublicSurface directly instantiates collaborators (4 `new` expressions detected).
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/Application/Cache/System/PublicSurface/Cache.php:45,89,100,108` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/PublicSurface/Cache.php:45,89,100,108`; original review finding `DR-0050`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0049

### Finding: PublicSurface directly instantiates collaborators (2 `new` expressions detected).

- Finding ID: SCR-0050
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: PublicSurface directly instantiates collaborators (2 `new` expressions detected).
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/Application/Cache/System/PublicSurface/Read/ReadFromCache.php:32,50` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/PublicSurface/Read/ReadFromCache.php:32,50`; original review finding `DR-0051`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0050

### Finding: PublicSurface directly instantiates collaborators (1 `new` expressions detected).

- Finding ID: SCR-0051
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/Application/Cache/System/PublicSurface/Read/RuntimeCacheTarget.php:21` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/PublicSurface/Read/RuntimeCacheTarget.php:21`; original review finding `DR-0052`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0051

### Finding: PublicSurface directly instantiates collaborators (1 `new` expressions detected).

- Finding ID: SCR-0052
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/Application/Cache/System/PublicSurface/Read/CompiledCacheTarget.php:29` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/PublicSurface/Read/CompiledCacheTarget.php:29`; original review finding `DR-0053`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0052

### Finding: PublicSurface directly instantiates collaborators (2 `new` expressions detected).

- Finding ID: SCR-0053
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: PublicSurface directly instantiates collaborators (2 `new` expressions detected).
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/Application/Cache/System/PublicSurface/Facade/CacheRegistry.php:33,47` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/PublicSurface/Facade/CacheRegistry.php:33,47`; original review finding `DR-0054`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0053

### Finding: PublicSurface directly instantiates collaborators (1 `new` expressions detected).

- Finding ID: SCR-0054
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/Application/Cache/System/PublicSurface/Facade/Cache.php:71` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/PublicSurface/Facade/Cache.php:71`; original review finding `DR-0055`; validation gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0054

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0055
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Foundation/Serialization/SerializedCachePayload.php:23` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Foundation/Serialization/SerializedCachePayload.php:23`; original review finding `DR-0056`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0055

### Finding: Null-coalescing fallback instantiates a dependency.

- Finding ID: SCR-0056
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Null-coalescing fallback instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Foundation/Serialization/SerializedCachePayload.php:23` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Foundation/Serialization/SerializedCachePayload.php:23`; original review finding `DR-0057`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0056

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0057
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Foundation/Randomness/GenerateJitteredTtl.php:12` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Foundation/Randomness/GenerateJitteredTtl.php:12`; original review finding `DR-0058`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0057

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0058
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Configuration/Builders/BuildCache.php:35` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Configuration/Builders/BuildCache.php:35`; original review finding `DR-0059`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0058

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0059
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Configuration/Builders/BuildCache.php:45` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Configuration/Builders/BuildCache.php:45`; original review finding `DR-0060`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0059

### Finding: Null-coalescing fallback instantiates a dependency.

- Finding ID: SCR-0060
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Null-coalescing fallback instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Configuration/Builders/BuildCache.php:45` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Configuration/Builders/BuildCache.php:45`; original review finding `DR-0061`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0060

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0061
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Configuration/CompiledCacheConfiguration/Builders/BuildCompiledCache.php:48` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Configuration/CompiledCacheConfiguration/Builders/BuildCompiledCache.php:48`; original review finding `DR-0062`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0061

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0062
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Configuration/CompiledCacheConfiguration/Builders/BuildCompiledCache.php:51` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Configuration/CompiledCacheConfiguration/Builders/BuildCompiledCache.php:51`; original review finding `DR-0063`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0062

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0063
- Review unit: `components/Application/Cache`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Flows/Compiled/ClearCompiledCache/ClearCompiledCache.php:31` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Flows/Compiled/ClearCompiledCache/ClearCompiledCache.php:31`; original review finding `DR-0064`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0063

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0064
- Review unit: `components/Application/Cache`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Flows/Compiled/ClearCompiledCache/ClearCompiledCache.php:33` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Flows/Compiled/ClearCompiledCache/ClearCompiledCache.php:33`; original review finding `DR-0065`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0064

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0065
- Review unit: `components/Application/Cache`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Flows/Compiled/ClearCompiledCache/ClearCompiledCache.php:38` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Flows/Compiled/ClearCompiledCache/ClearCompiledCache.php:38`; original review finding `DR-0066`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0065

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0066
- Review unit: `components/Application/Cache`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Flows/Compiled/ReadCompiledCache/ReadCompiledCache.php:27` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Flows/Compiled/ReadCompiledCache/ReadCompiledCache.php:27`; original review finding `DR-0067`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0066

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0067
- Review unit: `components/Application/Cache`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Flows/Compiled/ReadCompiledCache/ReadCompiledCache.php:37` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Flows/Compiled/ReadCompiledCache/ReadCompiledCache.php:37`; original review finding `DR-0068`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0067

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0068
- Review unit: `components/Application/Cache`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Flows/Compiled/ReadCompiledCache/ReadCompiledCache.php:39` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Flows/Compiled/ReadCompiledCache/ReadCompiledCache.php:39`; original review finding `DR-0069`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0068

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0069
- Review unit: `components/Application/Cache`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Flows/Compiled/CompileCache/CompileCache.php:27` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Flows/Compiled/CompileCache/CompileCache.php:27`; original review finding `DR-0070`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0069

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0070
- Review unit: `components/Application/Cache`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Flows/Compiled/CompileCache/CompileCache.php:36` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Flows/Compiled/CompileCache/CompileCache.php:36`; original review finding `DR-0071`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0070

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0071
- Review unit: `components/Application/Cache`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Flows/Compiled/CompileCache/CompileCache.php:40` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Flows/Compiled/CompileCache/CompileCache.php:40`; original review finding `DR-0072`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0071

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0072
- Review unit: `components/Application/Cache`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Flows/Operations/StoreCachedValue/StoreCachedValue.php:24` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Flows/Operations/StoreCachedValue/StoreCachedValue.php:24`; original review finding `DR-0073`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0072

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0073
- Review unit: `components/Application/Cache`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Flows/Protection/ProtectCacheSource/AcquireCacheStampedeLock.php:23` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Flows/Protection/ProtectCacheSource/AcquireCacheStampedeLock.php:23`; original review finding `DR-0074`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0073

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0074
- Review unit: `components/Application/Cache`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Flows/Protection/ProtectCacheSource/AcquireCacheStampedeLock.php:30` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Flows/Protection/ProtectCacheSource/AcquireCacheStampedeLock.php:30`; original review finding `DR-0075`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0074

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0075
- Review unit: `components/Application/Cache`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Flows/Lifecycle/EvictCachedValue/EvictCachedValue.php:18` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Flows/Lifecycle/EvictCachedValue/EvictCachedValue.php:18`; original review finding `DR-0076`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0075

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0076
- Review unit: `components/Application/Cache`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Flows/Lifecycle/WarmCache/WarmCache.php:21` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Flows/Lifecycle/WarmCache/WarmCache.php:21`; original review finding `DR-0077`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0076

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0077
- Review unit: `components/Application/Cache`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Flows/Lifecycle/RefreshCachedValue/RefreshCachedValue.php:26` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Flows/Lifecycle/RefreshCachedValue/RefreshCachedValue.php:26`; original review finding `DR-0078`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0077

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0078
- Review unit: `components/Application/Cache`
- Severity: HIGH
- Risk level: High
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Flows/Lifecycle/RefreshCachedValue/RefreshCachedValue.php:29` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Flows/Lifecycle/RefreshCachedValue/RefreshCachedValue.php:29`; original review finding `DR-0079`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0078

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0079
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Capabilities/Stores/RedisCacheStore.php:23` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/Stores/RedisCacheStore.php:23`; original review finding `DR-0080`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0079

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0080
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Capabilities/Stores/MemcachedCacheStore.php:18` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/Stores/MemcachedCacheStore.php:18`; original review finding `DR-0081`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0080

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0081
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Capabilities/Distribution/CacheNodeHealth.php:24` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/Distribution/CacheNodeHealth.php:24`; original review finding `DR-0082`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0081

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0082
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Capabilities/Distribution/CacheNodeHealth.php:41` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/Distribution/CacheNodeHealth.php:41`; original review finding `DR-0083`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0082

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0083
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Capabilities/Storage/SizeCachedValues/CheckCacheCapacity.php:13` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/Storage/SizeCachedValues/CheckCacheCapacity.php:13`; original review finding `DR-0084`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0083

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0084
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Capabilities/Storage/StoreCachedValues/RedisCacheStore.php:47` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/Storage/StoreCachedValues/RedisCacheStore.php:47`; original review finding `DR-0085`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0084

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0085
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Capabilities/Distribution/ReplicateCachedValues/CacheReplication.php:32` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/Distribution/ReplicateCachedValues/CacheReplication.php:32`; original review finding `DR-0086`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0085

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0086
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Capabilities/Distribution/DistributeCachedValues/CacheCluster.php:22` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/Distribution/DistributeCachedValues/CacheCluster.php:22`; original review finding `DR-0087`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0086

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0087
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Capabilities/Distribution/DistributeCachedValues/CacheCluster.php:23` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/Distribution/DistributeCachedValues/CacheCluster.php:23`; original review finding `DR-0088`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0087

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0088
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Capabilities/Lifecycle/ExpireCachedValues/CacheTtl.php:17` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/Lifecycle/ExpireCachedValues/CacheTtl.php:17`; original review finding `DR-0089`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0088

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0089
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Capabilities/Source/ProtectCacheSource/InMemoryLockStore.php:14` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/Source/ProtectCacheSource/InMemoryLockStore.php:14`; original review finding `DR-0090`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0089

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0090
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Capabilities/Source/ProtectCacheSource/RequestCoalescing.php:21` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/Source/ProtectCacheSource/RequestCoalescing.php:21`; original review finding `DR-0091`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0090

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0091
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Capabilities/Source/SyncWithSource/SourceSyncPolicies/SourceSyncCoordinator.php:34` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/Source/SyncWithSource/SourceSyncPolicies/SourceSyncCoordinator.php:34`; original review finding `DR-0092`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0091

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0092
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Capabilities/CompiledCache/ManageCompiledCache/AtomicCompiledCacheWrite.php:16` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/CompiledCache/ManageCompiledCache/AtomicCompiledCacheWrite.php:16`; original review finding `DR-0093`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0092

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0093
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Capabilities/CompiledCache/ManageCompiledCache/AtomicCompiledCacheWrite.php:28` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/CompiledCache/ManageCompiledCache/AtomicCompiledCacheWrite.php:28`; original review finding `DR-0094`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0093

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0094
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Capabilities/CompiledCache/ManageCompiledCache/CheckCompiledCacheIsFresh.php:25` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/CompiledCache/ManageCompiledCache/CheckCompiledCacheIsFresh.php:25`; original review finding `DR-0095`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0094

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0095
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Capabilities/CompiledCache/ManageCompiledCache/DeleteCompiledCacheFile.php:19` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/CompiledCache/ManageCompiledCache/DeleteCompiledCacheFile.php:19`; original review finding `DR-0096`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0095

### Finding: Constructor default parameter instantiates a dependency.

- Finding ID: SCR-0096
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor default parameter instantiates a dependency.
- Root cause: The unit boundary allows dependency and runtime composition pressure to appear in `components/Application/Cache/System/Capabilities/CompiledCache/ManageCompiledCache/DeleteCompiledCacheFile.php:29` instead of keeping the responsibility isolated.
- Impact: Dependency and runtime composition risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/CompiledCache/ManageCompiledCache/DeleteCompiledCacheFile.php:29`; original review finding `DR-0097`; validation gate `php tooling/refactor/check-direct-instantiation.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Test/gate proof required: php tooling/refactor/check-direct-instantiation.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0096

### Finding: Constructor has 9 parameters.

- Finding ID: SCR-0421
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 9 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Cache/System/Configuration/CacheConfiguration.php:16` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Configuration/CacheConfiguration.php:16`; original review finding `DR-0422`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0421

### Finding: Constructor has 9 parameters.

- Finding ID: SCR-0422
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 9 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Cache/System/PublicSurface/AvaxCache.php:34` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/PublicSurface/AvaxCache.php:34`; original review finding `DR-0423`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0422

### Finding: PublicSurface file is 334 lines (>150).

- Finding ID: SCR-0423
- Review unit: `components/Application/Cache`
- Severity: HIGH
- Risk level: High
- Symptom: PublicSurface file is 334 lines (>150).
- Root cause: The unit boundary allows public API boundary pressure to appear in `components/Application/Cache/System/PublicSurface/AvaxCache.php` instead of keeping the responsibility isolated.
- Impact: Public api boundary risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/PublicSurface/AvaxCache.php`; original review finding `DR-0424`; validation gate `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify methods as delegate vs behavior; move internal behavior behind flows/capabilities without API churn.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0423

### Finding: Class/file is 434 lines (>300).

- Finding ID: SCR-0424
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 434 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Cache/System/Capabilities/Health/CacheHealthDetector.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/Health/CacheHealthDetector.php`; original review finding `DR-0425`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0424

### Finding: Constructor has 10 parameters.

- Finding ID: SCR-0425
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 10 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Cache/System/Capabilities/Health/CacheHealthStatus.php:17` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/Health/CacheHealthStatus.php:17`; original review finding `DR-0426`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0425

### Finding: Constructor has 9 parameters.

- Finding ID: SCR-0426
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 9 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Cache/System/Capabilities/Storage/StoreCachedValues/RedisCacheStore.php:30` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/Storage/StoreCachedValues/RedisCacheStore.php:30`; original review finding `DR-0427`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0426

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0427
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Cache/System/Capabilities/Distribution/ReplicateCachedValues/PrimaryReplicaPolicy.php:24` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/Distribution/ReplicateCachedValues/PrimaryReplicaPolicy.php:24`; original review finding `DR-0428`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0427

### Finding: Class/file is 316 lines (>300).

- Finding ID: SCR-0428
- Review unit: `components/Application/Cache`
- Severity: LOW
- Risk level: Low
- Symptom: Class/file is 316 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Cache/System/Capabilities/Distribution/ReplicateCachedValues/CacheReplication.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/Distribution/ReplicateCachedValues/CacheReplication.php`; original review finding `DR-0429`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: NO
- Related how-to deviation IDs if known: HTD-0428

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0429
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Cache/System/Capabilities/Distribution/DistributeCachedValues/DetectUnhealthyCacheNode.php:9` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/Distribution/DistributeCachedValues/DetectUnhealthyCacheNode.php:9`; original review finding `DR-0430`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0429

### Finding: Constructor has 8 parameters.

- Finding ID: SCR-0430
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 8 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Cache/System/Capabilities/Observability/ObserveCache/CacheOperation.php:13` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/Observability/ObserveCache/CacheOperation.php:13`; original review finding `DR-0431`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0430

### Finding: Class/file is 312 lines (>300).

- Finding ID: SCR-0431
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 312 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Cache/System/Capabilities/CompiledCache/DistributedCompiledCache/CompiledCacheFreshness.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/CompiledCache/DistributedCompiledCache/CompiledCacheFreshness.php`; original review finding `DR-0432`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0431

### Finding: Constructor has 10 parameters.

- Finding ID: SCR-0432
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Constructor has 10 parameters.
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Cache/System/Capabilities/CompiledCache/DistributedCompiledCache/CompiledCacheManifestEntry.php:26` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/CompiledCache/DistributedCompiledCache/CompiledCacheManifestEntry.php:26`; original review finding `DR-0433`; validation gate `php tooling/refactor/check-constructor-bloat.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Test/gate proof required: php tooling/refactor/check-constructor-bloat.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0432

### Finding: Class/file is 303 lines (>300).

- Finding ID: SCR-0433
- Review unit: `components/Application/Cache`
- Severity: MEDIUM
- Risk level: Medium
- Symptom: Class/file is 303 lines (>300).
- Root cause: The unit boundary allows maintainability and complexity pressure to appear in `components/Application/Cache/System/Capabilities/CompiledCache/DistributedCompiledCache/CompiledCacheManifest.php` instead of keeping the responsibility isolated.
- Impact: Maintainability and complexity risk can compound across callers, weaken testability, or hide runtime/security/performance failure modes.
- Evidence: `components/Application/Cache/System/Capabilities/CompiledCache/DistributedCompiledCache/CompiledCacheManifest.php`; original review finding `DR-0434`; validation gate `php tooling/governance/check-large-unit-thresholds.php`.
- Why it matters: Strict review requires symptom, root cause, impact, evidence, and risk before the unit can be considered evolvable.
- Required fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Test/gate proof required: php tooling/governance/check-large-unit-thresholds.php
- Blocks clean approval: YES
- Related how-to deviation IDs if known: HTD-0433

## 12. Strict Code Review Decision

Decision: **TARGETED_REDESIGN**.

Reason: highest severity `HIGH`, strict finding count `64`, tests `yes (29)`, confidence `HIGH`. KEEP_AND_IMPROVE is not used when BLOCKER/HIGH remains.
