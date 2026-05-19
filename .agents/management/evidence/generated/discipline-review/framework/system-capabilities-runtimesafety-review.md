# Framework Review Unit: framework/System/Capabilities/RuntimeSafety

Generated: 2026-05-19T22:16:56+02:00

## 1. Framework Unit Identity

- Framework unit path: `framework/System/Capabilities/RuntimeSafety`
- Role in framework: Owns ComponentHealthScanner, ResetVerifier, RuntimeSafety, RuntimeSafetyFinding, StateLeakDetector, StatefulDependencyDetector, StatelessGuard, BoundaryAudit and related behavior.
- Public API entrypoints: `framework/System/Capabilities/RuntimeSafety/StatelessBoundary/PublicSurface/BoundaryAudit.php`, `framework/System/Capabilities/RuntimeSafety/StatelessBoundary/PublicSurface/StatelessBoundary.php`, `framework/System/Capabilities/RuntimeSafety/StatelessBoundary/System/PublicSurface/BoundaryAudit.php`, `framework/System/Capabilities/RuntimeSafety/StatelessBoundary/System/PublicSurface/StatelessBoundary.php`
- Runtime lifecycle ownership: yes
- Boot/container/configuration ownership: not primary
- Request/console/runtime boundary ownership: yes
- Dependencies on components: framework (9), Application/Cache (1), Application/Container (1), Application/Filesystem (1)

## 2. Framework Architecture Review

Checks framework-level capability/flow expression, dumping grounds, public entrypoint stability, lifecycle ownership, boot/run separation, and component-internal leakage.

Review result: No finding in this slice from the current scan.

## 3. Runtime and Lifecycle Review

Checks runtime-agnostic design, long-lived state, reset safety, boot/run separation, freeze/verify discipline, and hot-path assembly.

Review result: No finding in this slice from the current scan.

## 4. Public DSL and PublicSurface Review

Checks small predictable public DSL/facades, delegation, runtime machinery leakage, breaking-change risk, and fluent intent.

Review result: Finding(s) recorded in the table below.

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
| `how-to-design-components.md` | canonical System shape, PublicSurface delegates, Configuration assembles | Fail | structure + DI scans | see finding table | MEDIUM |
| `how-to-modern-php-attributes-di.md` | constructor bloat and DI discipline | Pass | constructor scan | keep | - |
| `how-to-system-security.md` | path/I/O/security-sensitive boundaries | Pass | raw file/security scan | keep | - |
| `how-to-system-performance.md` | hot-path/memory/large object graph risks | Pass | performance scan | keep | - |
| `how-to-unit-test.md` | behavior proof exists | Pass | test discovery | keep | - |
| `how-to-document.md` | semantic PHPDoc/evidence truthfulness | Pass | PHPDoc/evidence checks | keep | - |
| `how-to-dogfooding.md` | first-party capabilities reused at boundaries | Pass | raw I/O + DI scans | keep | - |

## 8. Finding Table

| ID | Severity | Rule source | Exact file/path | Exact issue | Risk type | Recommended fix | Safe remediation batch | Validation command | Cross-component | fix-this candidate |
|---|---|---|---|---|---|---|---|---|---:|---:|
| DR-0416 | MEDIUM | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `framework/System/Capabilities/RuntimeSafety/StatelessBoundary/System/PublicSurface/StatelessBoundary.php:45` | PublicSurface directly instantiates collaborators (1 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |

## 9. Framework Unit Decision

Decision: **NEEDS_MEDIUM_REMEDIATION**

Reason: highest severity is MEDIUM with 1 finding(s); tests detected: yes. This is review evidence only and is not a GREEN production-readiness claim.
