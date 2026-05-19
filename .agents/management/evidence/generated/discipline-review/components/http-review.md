# Component Review: components/HTTP

Generated: 2026-05-19T22:16:56+02:00

## 1. Component Identity

- Component path: `components/HTTP`
- Parent group: `components`
- Purpose inferred from code: Owns StreamBody, HttpMethod, HttpReasonPhrase, HttpStatusCode, RequestOption, ResponseHeaders, AppKernel, BootHttpKernel and related behavior.
- Public API surface: `components/HTTP/System/PublicSurface/Http.php`, `components/HTTP/System/PublicSurface/HttpInterface.php`, `components/HTTP/System/PublicSurface/Request.php`, `components/HTTP/System/PublicSurface/Response.php`, `components/HTTP/System/PublicSurface/shortcuts.php`
- Internal ownership model: System folders present are PublicSurface, Flows, Capabilities, Configuration, Foundation.
- Main flows: CreateRequestFromGlobals, CreateResponse, HandleRequest, HandleRequest, HandleRequest, HandleRequest, Routing, SendResponse
- Main capabilities: Body, Enums, Enums, Enums, Enums, Headers, Kernel, Kernel, Kernel, Kernel, Kernel, MiddlewarePipeline, MiddlewarePipeline, MiddlewarePipeline, MiddlewarePipeline, MiddlewarePipeline, MiddlewarePipeline, MiddlewarePipeline, MiddlewarePipeline, MiddlewarePipeline, Capabilities, SessionStorage, Uri, Uri, Uri, Capabilities
- Configuration owners: `components/HTTP/System/Configuration/Builders/HttpBuilder.php`, `components/HTTP/System/Configuration/Configuration.php`, `components/HTTP/System/Configuration/HttpConfiguration.php`, `components/HTTP/System/Configuration/HttpServiceProvider.php`, `components/HTTP/System/Configuration/RouterBootstrapper.php`
- Dependencies on other components: HTTP/Response (23), HTTP/System (21), HTTP/Request (17), HTTP/Router (16), psr (9), Application/Container (3), HTTP/SecureRequest (3), HTTP/Session (1)

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
| DR-0143 | HIGH | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/HTTP/System/PublicSurface/Response.php:38,46,51` | PublicSurface directly instantiates collaborators (3 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0478 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/HTTP/System/Configuration/RouterBootstrapper.php` | Class/file is 308 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0479 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/HTTP/System/Configuration/HttpConfiguration.php:25` | Constructor has 9 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0481 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/HTTP/System/Capabilities/Uri/Uri.php:11` | Constructor has 9 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0480 | LOW | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/HTTP/System/Foundation/Values/HeaderName.php` | Class/file is 335 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |

## 15. Component Decision

Decision: **NEEDS_HIGH_REMEDIATION**

Reason: highest severity is HIGH with 5 finding(s); tests detected: yes. This is review evidence only and is not a GREEN production-readiness claim.
