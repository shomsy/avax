# Component Review: components/SystemDesign

Generated: 2026-05-19T22:16:56+02:00

## 1. Component Identity

- Component path: `components/SystemDesign`
- Parent group: `components`
- Purpose inferred from code: Owns ArchitectureTest, FailureBudget, Slo, CacheHitRatio, CacheStampedeRisk, CapacityModel, LatencyBudget, ConsumerThroughput and related behavior.
- Public API surface: `components/SystemDesign/System/PublicSurface/SystemDesignKit.php`
- Internal ownership model: System folders present are PublicSurface, Flows, Capabilities, Foundation.
- Main flows: DetectConsistencyRisk, DetectMessagingRisk, EstimateCacheEffectiveness, EstimateFailureBudget, EstimateLatencyBudget, EstimateProjectionLag, EstimateQueuePressure, EstimateReplicationLag, EstimateStorageGrowth, EstimateTrafficLoad, ExplainConsistencyTradeoff, ResolveConflict, RunArchitectureTests, RunFailureSimulations, RunScenarios, ValidateArchitectureTestsSchema, ValidateCapacityModel, Validate
- Main capabilities: ArchitectureTesting, Availability, Availability, Cache, Cache, Capacity, Latency, Queue, Queue, Storage, Traffic, Traffic, Traffic, Conflicts, Conflicts, Consistency, Delivery, Delivery, Lag, Lag, Profiles, Profiles, Staleness, FailureSimulation, Acknowledgement, Broker, CommandQuery, CommandQuery, Consumers, DeadLetters, Envelope, Inbox, Messaging, Outbox, Retry, Types, Types, ScenarioRunner, Sce
- Configuration owners: 
- Dependencies on other components: SystemDesign/System (96), Application/Filesystem (1)

## 2. Structure Review

Checks canonical System shape, flow/capability language, forbidden buckets, duplicate ownership, and noisy subfolders.

Review result: No finding in this slice from the current scan.

## 3. PublicSurface Review

Checks that public API stays small, delegates, avoids mutable/request-scoped state, and does not leak internals.

Review result: Finding(s) recorded in the table below.

## 4. Configuration and DI Review

Checks assembly-only Configuration, direct runtime instantiation, service locator behavior, fail-fast dependencies, fallbacks, and constructor pressure.

Review result: No finding in this slice from the current scan.

## 5. Flow Review

Checks exact action names, end-to-end ownership, misplaced assembly, breadth, and testable outcomes.

Review result: No finding in this slice from the current scan.

## 6. Capability Review

Checks reusable ability naming, cohesion, generic service/manager hiding, and honest extracted reuse.

Review result: No finding in this slice from the current scan.

## 7. Foundation Review

Checks that Foundation remains tiny, neutral, and free from hidden domain behavior.

Review result: No finding in this slice from the current scan.

## 8. Code Review According to how-to-code-review.md

Checks correctness, responsibility, naming, cohesion, coupling, complexity, errors, nulls, types, PHPDoc, dead code, duplication, hidden effects, security, performance, and testability.

Review result: No finding in this slice from the current scan.

## 9. Runtime Safety Review

Checks static mutable state, request-scoped state, hidden caches, global state, reset safety, and long-lived worker leakage.

Review result: No finding in this slice from the current scan.

## 10. Security Review

Checks unsafe defaults, silent fallbacks, validation, auth boundaries, credentials, serialization, file/path risk, and negative proof.

Review result: No finding in this slice from the current scan.

## 11. Performance and Memory Review

Checks object graph size, repeated work, reflection, large arrays/mixed contracts, unbounded growth, I/O/scanning, and cache misuse.

Review result: No finding in this slice from the current scan.

## 12. Test Review

Checks behavior proof, public surface tests, negative/failure tests, stability, fixture smells, and suppressions.

Review result: No finding in this slice from the current scan.

## 13. Documentation and Evidence Review

Checks semantic PHPDoc, useful comments, current evidence, and honest YELLOW handling.

Review result: No finding in this slice from the current scan.

## Governance Compliance Report

| Governance document | Requirement checked | Status | Evidence | Required action | Severity |
|---|---|---|---|---|---|
| `how-to-code-review.md` | Every unit has concrete finding table | Pass | this review file | keep | - |
| `how-to-architecture.md` | ownership and flow/capability slicing | Pass | structure scan | keep | - |
| `how-to-design-components.md` | canonical System shape, PublicSurface delegates, Configuration assembles | Fail | structure + DI scans | see finding table | HIGH |
| `how-to-modern-php-attributes-di.md` | constructor bloat and DI discipline | Fail | constructor scan | see finding table | HIGH |
| `how-to-system-security.md` | path/I/O/security-sensitive boundaries | Pass | raw file/security scan | keep | - |
| `how-to-system-performance.md` | hot-path/memory/large object graph risks | Pass | performance scan | keep | - |
| `how-to-unit-test.md` | behavior proof exists | Pass | test discovery | keep | - |
| `how-to-document.md` | semantic PHPDoc/evidence truthfulness | Pass | PHPDoc/evidence checks | keep | - |
| `how-to-dogfooding.md` | first-party capabilities reused at boundaries | Pass | raw I/O + DI scans | keep | - |

## 14. Finding Table

| ID | Severity | Rule source | Exact file/path | Exact issue | Risk type | Recommended fix | Safe remediation batch | Validation command | Cross-component | fix-this candidate |
|---|---|---|---|---|---|---|---|---|---:|---:|
| DR-0239 | HIGH | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/SystemDesign/System/PublicSurface/SystemDesignKit.php:56,65,70,99,116,134,152,169...` | PublicSurface directly instantiates collaborators (18 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0501 | HIGH | `how-to-code-review.md §21; how-to-design-components.md §6.2` | `components/SystemDesign/System/PublicSurface/SystemDesignKit.php` | PublicSurface file is 491 lines (>150). | PUBLIC_API | Classify methods as delegate vs behavior; move internal behavior behind flows/capabilities without API churn. | PublicSurface size classification batch | `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0503 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/SystemDesign/System/Capabilities/Capacity/CapacityModel.php:29` | Constructor has 13 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0508 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/SystemDesign/System/Capabilities/Messaging/MessagingModel.php:41` | Constructor has 12 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0502 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/SystemDesign/System/Capabilities/ScenarioRunner/Scenario.php` | Class/file is 683 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0504 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/SystemDesign/System/Capabilities/ArchitectureTesting/ArchitectureTest.php` | Class/file is 328 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0505 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/SystemDesign/System/Capabilities/Consistency/ConsistencyModel.php:36` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0506 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/SystemDesign/System/Capabilities/SchemaValidation/NativeYamlParser.php` | Class/file is 345 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0507 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/SystemDesign/System/Capabilities/SchemaValidation/SchemaValidator.php` | Class/file is 436 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0509 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/SystemDesign/System/Capabilities/Messaging/MessagingModel.php` | Class/file is 302 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |

## 15. Component Decision

Decision: **NEEDS_HIGH_REMEDIATION**

Reason: highest severity is HIGH with 10 finding(s); tests detected: yes. This is review evidence only and is not a GREEN production-readiness claim.
