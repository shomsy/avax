# Framework Review Unit: framework/System/Capabilities/Runtime

Generated: 2026-05-19T22:16:56+02:00

## 1. Framework Unit Identity

- Framework unit path: `framework/System/Capabilities/Runtime`
- Role in framework: Owns RunApplicationOnFrankenPhp, RunApplicationOnPhpBuiltInServer, RunApplicationOnRoadRunner, RunApplicationOnSwoole, RunApplicationOnWorkerman, CliInputReader, CliOutputWriter, CliRuntime and related behavior.
- Public API entrypoints: `framework/System/Capabilities/Runtime/GracefulShutdown/PublicSurface/GracefulShutdown.php`, `framework/System/Capabilities/Runtime/GracefulShutdown/System/PublicSurface/GracefulShutdown.php`, `framework/System/Capabilities/Runtime/PublicSurface/Server.php`, `framework/System/Capabilities/Runtime/PublicSurface/ServerResult.php`
- Runtime lifecycle ownership: yes
- Boot/container/configuration ownership: not primary
- Request/console/runtime boundary ownership: yes
- Dependencies on components: framework (84), psr (4)

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

Review result: Finding(s) recorded in the table below.

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
| `how-to-design-components.md` | canonical System shape, PublicSurface delegates, Configuration assembles | Fail | structure + DI scans | see finding table | HIGH |
| `how-to-modern-php-attributes-di.md` | constructor bloat and DI discipline | Fail | constructor scan | see finding table | HIGH |
| `how-to-system-security.md` | path/I/O/security-sensitive boundaries | Pass | raw file/security scan | keep | - |
| `how-to-system-performance.md` | hot-path/memory/large object graph risks | Pass | performance scan | keep | - |
| `how-to-unit-test.md` | behavior proof exists | Pass | test discovery | keep | - |
| `how-to-document.md` | semantic PHPDoc/evidence truthfulness | Pass | PHPDoc/evidence checks | keep | - |
| `how-to-dogfooding.md` | first-party capabilities reused at boundaries | Fail | raw I/O + DI scans | see finding table | HIGH |

## 8. Finding Table

| ID | Severity | Rule source | Exact file/path | Exact issue | Risk type | Recommended fix | Safe remediation batch | Validation command | Cross-component | fix-this candidate |
|---|---|---|---|---|---|---|---|---|---:|---:|
| DR-0417 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `framework/System/Capabilities/Runtime/WarmApplication/HandleWarmRequest.php:48` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0418 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `framework/System/Capabilities/Runtime/WarmApplication/HandleWarmRequest.php:49` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0419 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `framework/System/Capabilities/Runtime/WarmApplication/HandleWarmRequest.php:50` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0420 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `framework/System/Capabilities/Runtime/Worker/WorkerLoop.php:21` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0646 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `framework/System/Capabilities/Runtime/Runtime.php:24` | Constructor has 13 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0421 | MEDIUM | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `framework/System/Capabilities/Runtime/GracefulShutdown/System/PublicSurface/GracefulShutdown.php:24` | PublicSurface directly instantiates collaborators (1 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |

## 9. Framework Unit Decision

Decision: **NEEDS_HIGH_REMEDIATION**

Reason: highest severity is HIGH with 6 finding(s); tests detected: yes. This is review evidence only and is not a GREEN production-readiness claim.
