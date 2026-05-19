# Framework Review Unit: framework/System/Flows/HandleIncomingHttp

Generated: 2026-05-19T22:16:56+02:00

## 1. Framework Unit Identity

- Framework unit path: `framework/System/Flows/HandleIncomingHttp`
- Role in framework: Owns CloseHttpRequestScope, DispatchConfiguredRoute, FrameworkRouteRegistrar, HandleIncomingHttp, MatchHttpRoute, MatchedHttpRoute, OpenHttpRequestScope, ReadIncomingHttpRequest and related behavior.
- Public API entrypoints: none detected
- Runtime lifecycle ownership: not primary
- Boot/container/configuration ownership: yes
- Request/console/runtime boundary ownership: yes
- Dependencies on components: HTTP/Router (14), framework (12), HTTP/Request (5), psr (4), HTTP/Response (3), Operations/Observability (1), HTTP/Dispatcher (1)

## 2. Framework Architecture Review

Checks framework-level capability/flow expression, dumping grounds, public entrypoint stability, lifecycle ownership, boot/run separation, and component-internal leakage.

Review result: No finding in this slice from the current scan.

## 3. Runtime and Lifecycle Review

Checks runtime-agnostic design, long-lived state, reset safety, boot/run separation, freeze/verify discipline, and hot-path assembly.

Review result: Finding(s) recorded in the table below.

## 4. Public DSL and PublicSurface Review

Checks small predictable public DSL/facades, delegation, runtime machinery leakage, breaking-change risk, and fluent intent.

Review result: No finding in this slice from the current scan.

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
| DR-0401 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `framework/System/Flows/HandleIncomingHttp/MatchHttpRoute.php:29` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0402 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `framework/System/Flows/HandleIncomingHttp/HandleIncomingHttp.php:34` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0403 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `framework/System/Flows/HandleIncomingHttp/HandleIncomingHttp.php:38` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0661 | MEDIUM | `AGENTS.md §14 runtime-agnostic-framework; how-to-system-performance.md long-lived runtime rules` | `framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:[16]` | Framework core references specific runtimes outside narrow runtime adapter/boundary slices. | RUNTIME_SAFETY | Confirm references are documentation/metadata only or move runtime-specific behavior behind RuntimeBoundary/Capabilities/Runtime. | Runtime-specific dependency classification batch | `php tooling/refactor/check-runtime-leaks.php` | NO | YES |

## 9. Framework Unit Decision

Decision: **NEEDS_HIGH_REMEDIATION**

Reason: highest severity is HIGH with 4 finding(s); tests detected: yes. This is review evidence only and is not a GREEN production-readiness claim.
