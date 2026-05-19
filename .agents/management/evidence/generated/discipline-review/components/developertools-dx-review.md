# Component Review: components/DeveloperTools/Dx

Generated: 2026-05-19T22:16:56+02:00

## 1. Component Identity

- Component path: `components/DeveloperTools/Dx`
- Parent group: `components/DeveloperTools`
- Purpose inferred from code: Owns DependencyGraph, DependencyNode, DxCommand, DependencyMap, ProjectTemplate, DxConfiguration, DxConfigurationInterface, DxException and related behavior.
- Public API surface: `components/DeveloperTools/Dx/System/Capabilities/PublicSurface/DependencyMap.php`, `components/DeveloperTools/Dx/System/PublicSurface/Dx.php`
- Internal ownership model: System folders present are PublicSurface, Capabilities, Configuration, Foundation.
- Main flows: 
- Main capabilities: Graph, Graph, DxCommands, PublicSurface, Templates
- Configuration owners: `components/DeveloperTools/Dx/System/Configuration/DxConfiguration.php`, `components/DeveloperTools/Dx/System/Configuration/DxConfigurationInterface.php`
- Dependencies on other components: DeveloperTools/Dx (3)

## 2. Structure Review

Checks canonical System shape, flow/capability language, forbidden buckets, duplicate ownership, and noisy subfolders.

Review result: No finding in this slice from the current scan.

## 3. PublicSurface Review

Checks that public API stays small, delegates, avoids mutable/request-scoped state, and does not leak internals.

Review result: Finding(s) recorded in the table below.

## 4. Configuration and DI Review

Checks assembly-only Configuration, direct runtime instantiation, service locator behavior, fail-fast dependencies, fallbacks, and constructor pressure.

Review result: Finding(s) recorded in the table below.

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
| `how-to-design-components.md` | canonical System shape, PublicSurface delegates, Configuration assembles | Fail | structure + DI scans | see finding table | MEDIUM |
| `how-to-modern-php-attributes-di.md` | constructor bloat and DI discipline | Pass | constructor scan | keep | - |
| `how-to-system-security.md` | path/I/O/security-sensitive boundaries | Pass | raw file/security scan | keep | - |
| `how-to-system-performance.md` | hot-path/memory/large object graph risks | Pass | performance scan | keep | - |
| `how-to-unit-test.md` | behavior proof exists | Pass | test discovery | keep | - |
| `how-to-document.md` | semantic PHPDoc/evidence truthfulness | Pass | PHPDoc/evidence checks | keep | - |
| `how-to-dogfooding.md` | first-party capabilities reused at boundaries | Fail | raw I/O + DI scans | see finding table | MEDIUM |

## 14. Finding Table

| ID | Severity | Rule source | Exact file/path | Exact issue | Risk type | Recommended fix | Safe remediation batch | Validation command | Cross-component | fix-this candidate |
|---|---|---|---|---|---|---|---|---|---:|---:|
| DR-0028 | MEDIUM | `how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5` | `components/DeveloperTools/Dx` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | CONFIGURATION_DI | Add a precise System/Configuration/*ServiceProvider that registers existing dependencies without creating new feature behavior. | ServiceProvider coverage batch | `php tooling/refactor/check-service-provider-coverage.php` | NO | YES |
| DR-0232 | MEDIUM | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/DeveloperTools/Dx/System/PublicSurface/Dx.php:14,19` | PublicSurface directly instantiates collaborators (2 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |

## 15. Component Decision

Decision: **NEEDS_MEDIUM_REMEDIATION**

Reason: highest severity is MEDIUM with 2 finding(s); tests detected: yes. This is review evidence only and is not a GREEN production-readiness claim.
