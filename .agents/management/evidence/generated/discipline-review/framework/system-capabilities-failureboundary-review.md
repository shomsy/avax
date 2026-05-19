# Framework Review Unit: framework/System/Capabilities/FailureBoundary

Generated: 2026-05-19T22:16:56+02:00

## 1. Framework Unit Identity

- Framework unit path: `framework/System/Capabilities/FailureBoundary`
- Role in framework: Owns ClassifyApplicationException, ClassifyFailure, CleanupAfterFailure, FailureCleanupRegistry, CompileFailurePolicies, EnforceTimeout, CheckFailureBoundaryHealth, InvalidateCompiledPolicy and related behavior.
- Public API entrypoints: `framework/System/Capabilities/FailureBoundary/PublicSurface/FailureBoundary.php`
- Runtime lifecycle ownership: not primary
- Boot/container/configuration ownership: yes
- Request/console/runtime boundary ownership: not primary
- Dependencies on components: framework (95), HTTP/Response (6), Operations/Resilience (3), psr (3), HTTP/SecureRequest (2), HTTP/Router (2), Operations/Observability (2), Operations/Queue (2)

## 2. Framework Architecture Review

Checks framework-level capability/flow expression, dumping grounds, public entrypoint stability, lifecycle ownership, boot/run separation, and component-internal leakage.

Review result: No finding in this slice from the current scan.

## 3. Runtime and Lifecycle Review

Checks runtime-agnostic design, long-lived state, reset safety, boot/run separation, freeze/verify discipline, and hot-path assembly.

Review result: No finding in this slice from the current scan.

## 4. Public DSL and PublicSurface Review

Checks small predictable public DSL/facades, delegation, runtime machinery leakage, breaking-change risk, and fluent intent.

Review result: No finding in this slice from the current scan.

## 5. Configuration and DI Review

Checks root container discipline, service locator avoidance, fail-fast dependencies, provider registration, and runtime execution vs assembly.

Review result: No finding in this slice from the current scan.

## 6. Code Review According to how-to-code-review.md

Checks correctness, responsibility, naming, cohesion, coupling, complexity, errors, types, PHPDoc, duplication, hidden effects, security, performance, and testability.

Review result: No finding in this slice from the current scan.

## 7. Tests and Evidence Review

Checks public entrypoint/lifecycle tests, long-lived worker proof, explicit boot errors, evidence honesty, and accepted YELLOW tracking.

Review result: No finding in this slice from the current scan.

## Governance Compliance Report

| Governance document | Requirement checked | Status | Evidence | Required action | Severity |
|---|---|---|---|---|---|
| `how-to-code-review.md` | Every unit has concrete finding table | Pass | this review file | keep | - |
| `how-to-architecture.md` | ownership and flow/capability slicing | Pass | structure scan | keep | - |
| `how-to-design-components.md` | canonical System shape, PublicSurface delegates, Configuration assembles | Pass | structure + DI scans | keep | - |
| `how-to-modern-php-attributes-di.md` | constructor bloat and DI discipline | Fail | constructor scan | see finding table | HIGH |
| `how-to-system-security.md` | path/I/O/security-sensitive boundaries | Fail | raw file/security scan | see finding table | HIGH |
| `how-to-system-performance.md` | hot-path/memory/large object graph risks | Pass | performance scan | keep | - |
| `how-to-unit-test.md` | behavior proof exists | Pass | test discovery | keep | - |
| `how-to-document.md` | semantic PHPDoc/evidence truthfulness | Pass | PHPDoc/evidence checks | keep | - |
| `how-to-dogfooding.md` | first-party capabilities reused at boundaries | Fail | raw I/O + DI scans | see finding table | HIGH |

## 8. Finding Table

| ID | Severity | Rule source | Exact file/path | Exact issue | Risk type | Recommended fix | Safe remediation batch | Validation command | Cross-component | fix-this candidate |
|---|---|---|---|---|---|---|---|---|---:|---:|
| DR-0654 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `framework/System/Capabilities/FailureBoundary/Foundation/FailurePolicy.php:17` | Constructor has 12 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0045 | MEDIUM | `how-to-system-security.md §22; how-to-dogfooding.md Filesystem rule; check-raw-file-operations.php` | `framework/System/Capabilities/FailureBoundary/Capabilities/WriteCompiledFailurePolicies/WriteCompiledFailurePolicies.php:30-34; CompileFailurePolicies.php:39; ReadCompiledFailurePolicies.php:25-29; CompiledMethodPolicy.php:116` | FailureBoundary compiled policy files use raw file operations classified NEEDS_DESIGN_DECISION. | SECURITY | Classify as approved compiled-artifact I/O or migrate to Filesystem/Storage boundary with path safety tests. | FailureBoundary raw I/O design decision batch | `php tooling/refactor/check-raw-file-operations.php` | NO | YES |
| DR-0651 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `framework/System/Capabilities/FailureBoundary/Configuration/FailureBoundaryConfiguration.php:12` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0652 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `framework/System/Capabilities/FailureBoundary/Foundation/CompiledMethodPolicy.php:12` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0653 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `framework/System/Capabilities/FailureBoundary/Foundation/FailureAction.php:15` | Constructor has 9 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0655 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `framework/System/Capabilities/FailureBoundary/Capabilities/RunFailurePipeline/RunFailurePipeline.php:27` | Constructor has 9 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |

## 9. Framework Unit Decision

Decision: **NEEDS_HIGH_REMEDIATION**

Reason: highest severity is HIGH with 6 finding(s); tests detected: yes. This is review evidence only and is not a GREEN production-readiness claim.
