# Framework Review Unit: framework/System/PublicSurface

Generated: 2026-05-19T22:16:56+02:00

## 1. Framework Unit Identity

- Framework unit path: `framework/System/PublicSurface`
- Role in framework: Owns App, Avax, AvaxInterface, BootDsl, ConsoleKernel, ConsoleKernelInterface, Diagnostics, HttpKernel and related behavior.
- Public API entrypoints: `framework/System/PublicSurface/App.php`, `framework/System/PublicSurface/Avax.php`, `framework/System/PublicSurface/AvaxInterface.php`, `framework/System/PublicSurface/BootDsl.php`, `framework/System/PublicSurface/Console/ConsoleKernel.php`, `framework/System/PublicSurface/Console/ConsoleKernelInterface.php`, `framework/System/PublicSurface/Diagnostics.php`, `framework/System/PublicSurface/Http/HttpKernel.php`, `framework/System/PublicSurface/Http/HttpKernelInterface.php`, `framework/System/PublicSurface/Runtime/RuntimeKernel.php`, `framework/System/PublicSurface/Runtime/RuntimeKernelInterface.php`
- Runtime lifecycle ownership: yes
- Boot/container/configuration ownership: yes
- Request/console/runtime boundary ownership: not primary
- Dependencies on components: framework (78), HTTP/Request (10), HTTP/Response (3), HTTP/Router (2), Operations/Observability (1), psr (1), Application/Container (1)

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
| `how-to-modern-php-attributes-di.md` | constructor bloat and DI discipline | Pass | constructor scan | keep | - |
| `how-to-system-security.md` | path/I/O/security-sensitive boundaries | Pass | raw file/security scan | keep | - |
| `how-to-system-performance.md` | hot-path/memory/large object graph risks | Pass | performance scan | keep | - |
| `how-to-unit-test.md` | behavior proof exists | Pass | test discovery | keep | - |
| `how-to-document.md` | semantic PHPDoc/evidence truthfulness | Pass | PHPDoc/evidence checks | keep | - |
| `how-to-dogfooding.md` | first-party capabilities reused at boundaries | Pass | raw I/O + DI scans | keep | - |

## 8. Finding Table

| ID | Severity | Rule source | Path | Issue | Risk | Fix-this |
|---|---|---|---|---|---|---|
| none | - | - | - | No findings recorded for this review unit. | - | NO |

## 9. Framework Unit Decision

Decision: **CLEAN**

Reason: highest severity is NONE with 0 finding(s); tests detected: yes. This is review evidence only and is not a GREEN production-readiness claim.


## Addendum: Direct PublicSurface File Findings Folded Into This Unit

The generator initially discovered direct-file findings for `App.php`, `Avax.php`, and `BootDsl.php`; this addendum folds them into the canonical framework review unit `framework/System/PublicSurface`.

| ID | Severity | Rule source | Exact file/path | Exact issue | Risk type | Recommended fix | Safe remediation batch | Validation command | Cross-component | fix-this candidate |
|---|---|---|---|---|---|---|---|---|---:|---:|
| DR-0389 | HIGH | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `framework/System/PublicSurface/App.php:207,211,254,271,272,274,275,285...` | PublicSurface directly instantiates collaborators. | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0390 | HIGH | `how-to-dependency-injection.md §3.4, §6.9` | `framework/System/PublicSurface/Avax.php:72` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Move default creation to approved configuration/boot context. | Direct dependency construction cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0391 | HIGH | `how-to-dependency-injection.md §3.4, §6.9` | `framework/System/PublicSurface/Avax.php:74` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Move default creation to approved configuration/boot context. | Direct dependency construction cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0392 | HIGH | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `framework/System/PublicSurface/Avax.php:72,74,75,76,77,78,79,80...` | PublicSurface directly instantiates collaborators. | PUBLIC_API | Move collaborator creation behind framework configuration. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0393 | HIGH | `how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule` | `framework/System/PublicSurface/BootDsl.php:150` | Null-coalescing fallback instantiates a dependency. | CONFIGURATION_DI | Make the dependency explicit and fail during boot/verification. | Hidden fallback construction cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0394 | HIGH | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `framework/System/PublicSurface/BootDsl.php:55,59,150,151,155,156,159,164` | PublicSurface directly instantiates collaborators. | PUBLIC_API | Move Boot DSL assembly behind framework configuration without changing public API. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0395 | HIGH | `how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule` | `framework/System/PublicSurface/BootDsl.php:150` | Hidden fallback construction in public DSL. | CONFIGURATION_DI | Replace fallback with injected/default boot dependency. | Hidden fallback construction cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0638 | HIGH | `how-to-code-review.md §21; how-to-design-components.md §6.2` | `framework/System/PublicSurface/App.php` | PublicSurface file is 366 lines (>150). | PUBLIC_API | Classify behavior leaks and move internal behavior behind flows/capabilities. | PublicSurface size classification batch | `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0639 | MEDIUM | `how-to-code-review.md §21; how-to-design-components.md §6.2` | `framework/System/PublicSurface/Avax.php` | PublicSurface file exceeds review threshold. | PUBLIC_API | Classify delegate vs behavior methods. | PublicSurface size classification batch | `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0640 | MEDIUM | `how-to-code-review.md §21; how-to-design-components.md §6.2` | `framework/System/PublicSurface/BootDsl.php` | PublicSurface file exceeds review threshold. | PUBLIC_API | Classify delegate vs behavior methods. | PublicSurface size classification batch | `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php` | NO | YES |

Updated framework unit decision: **NEEDS_HIGH_REMEDIATION**. This is not a GREEN claim; it is a review-only classification.
