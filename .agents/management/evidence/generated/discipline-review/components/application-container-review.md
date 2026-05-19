# Component Review: components/Application/Container

Generated: 2026-05-19T22:16:56+02:00

## 1. Component Identity

- Component path: `components/Application/Container`
- Parent group: `components/Application`
- Purpose inferred from code: Owns BindingRegistry, AssembleObservability, AssembleRuntime, ObservabilityAssembly, RuntimeAssembly, SeedSystemDependencies, SeedSystemServices, ArtifactMetadata and related behavior.
- Public API surface: `components/Application/Container/System/PublicSurface/Container.php`, `components/Application/Container/System/PublicSurface/ContainerInterface.php`, `components/Application/Container/System/PublicSurface/shortcuts.php`
- Internal ownership model: System folders present are PublicSurface, Flows, Capabilities, Configuration, Foundation.
- Main flows: BootProviders, CallFunction, CloseScope, CreateContainer, DescribeDependency, DescribeDependency, ExportGraph, OpenScope, RegisterBinding, RegisterDependencies, RegisterServices, RegisterServices, ResolveDependency, ResolveDependency, ValidateComposition
- Main capabilities: Bindings, Assembly, Assembly, Assembly, Assembly, Assembly, Assembly, Compilation, Compilation, Compilation, Compilation, Compilation, Compilation, Compilation, Composition, Composition, Errors, Errors, Errors, Observability, Observability, Observability, Observability, Observability, Policy, Policy, ContextualContainer, Bindings, Bindings, Bindings, Bindings, Bindings, Bindings, Bindings, Binding
- Configuration owners: `components/Application/Container/System/Configuration/Builders/ContainerBuilder.php`, `components/Application/Container/System/Configuration/ContainerServiceProvider.php`
- Dependencies on other components: Application/Container (323), psr (13), Application/Filesystem (5), framework (5)

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
| `how-to-design-components.md` | canonical System shape, PublicSurface delegates, Configuration assembles | Fail | structure + DI scans | see finding table | HIGH |
| `how-to-modern-php-attributes-di.md` | constructor bloat and DI discipline | Fail | constructor scan | see finding table | HIGH |
| `how-to-system-security.md` | path/I/O/security-sensitive boundaries | Pass | raw file/security scan | keep | - |
| `how-to-system-performance.md` | hot-path/memory/large object graph risks | Pass | performance scan | keep | - |
| `how-to-unit-test.md` | behavior proof exists | Pass | test discovery | keep | - |
| `how-to-document.md` | semantic PHPDoc/evidence truthfulness | Pass | PHPDoc/evidence checks | keep | - |
| `how-to-dogfooding.md` | first-party capabilities reused at boundaries | Fail | raw I/O + DI scans | see finding table | HIGH |

## 14. Finding Table

| ID | Severity | Rule source | Exact file/path | Exact issue | Risk type | Recommended fix | Safe remediation batch | Validation command | Cross-component | fix-this candidate |
|---|---|---|---|---|---|---|---|---|---:|---:|
| DR-0443 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Application/Container/System/Capabilities/Composition/CreateContainerConfig.php:87` | Constructor has 16 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0446 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Application/Container/System/Capabilities/ContainerObservability/Observability/RuntimeReport.php:35` | Constructor has 19 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0449 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Application/Container/System/Capabilities/Composition/Compilation/ArtifactMetadata.php:35` | Constructor has 35 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0451 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Application/Container/System/Capabilities/Composition/Compilation/CompileReport.php:25` | Constructor has 33 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0452 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Application/Container/System/Capabilities/Composition/Compilation/CompileContainer.php:63` | Constructor has 20 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0454 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Application/Container/System/Capabilities/Declaration/Ownership/RegistrationMetadata.php:62` | Constructor has 20 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0103 | MEDIUM | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/Application/Container/System/PublicSurface/Container.php:59,137` | PublicSurface directly instantiates collaborators (2 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0104 | MEDIUM | `how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule` | `components/Application/Container/System/Capabilities/Resolution/ResolveDependencies.php:92` | Null-coalescing fallback instantiates a dependency. | CONFIGURATION_DI | Make dependency explicit and fail during boot/verification, or move fallback to approved composition context. | Hidden fallback construction cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0105 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/Application/Container/System/Capabilities/Providers/ProviderRegistry.php:26` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0106 | MEDIUM | `how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule` | `components/Application/Container/System/Capabilities/Execution/Injection/Invocation/FunctionCaller.php:62` | Null-coalescing fallback instantiates a dependency. | CONFIGURATION_DI | Make dependency explicit and fail during boot/verification, or move fallback to approved composition context. | Hidden fallback construction cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0107 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/Application/Container/System/Capabilities/Composition/Compilation/ServiceCompiler.php:29` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0108 | MEDIUM | `how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule` | `components/Application/Container/System/Capabilities/Declaration/Bindings/DependencyRegistry.php:122` | Null-coalescing fallback instantiates a dependency. | CONFIGURATION_DI | Make dependency explicit and fail during boot/verification, or move fallback to approved composition context. | Hidden fallback construction cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0109 | MEDIUM | `how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule` | `components/Application/Container/System/Capabilities/Declaration/Bindings/DependencyRegistry.php:274` | Null-coalescing fallback instantiates a dependency. | CONFIGURATION_DI | Make dependency explicit and fail during boot/verification, or move fallback to approved composition context. | Hidden fallback construction cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0110 | MEDIUM | `how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule` | `components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistry.php:122` | Null-coalescing fallback instantiates a dependency. | CONFIGURATION_DI | Make dependency explicit and fail during boot/verification, or move fallback to approved composition context. | Hidden fallback construction cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0111 | MEDIUM | `how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule` | `components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistry.php:273` | Null-coalescing fallback instantiates a dependency. | CONFIGURATION_DI | Make dependency explicit and fail during boot/verification, or move fallback to approved composition context. | Hidden fallback construction cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0112 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/Application/Container/System/Capabilities/Declaration/Bindings/DependencyRegistration.php:82` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0113 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistration.php:85` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0440 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/Application/Container/System/Capabilities/Runtime/ServicePool.php` | Class/file is 318 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0441 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/Application/Container/System/Capabilities/Runtime/DependencyPool.php` | Class/file is 318 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0442 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Application/Container/System/Capabilities/Resolution/LifetimePlan.php:33` | Constructor has 10 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0444 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/Application/Container/System/Capabilities/Composition/CreateContainerConfig.php` | Class/file is 516 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0445 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/Application/Container/System/Capabilities/ContextualContainer/ContextContainer.php` | Class/file is 653 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0447 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/Application/Container/System/Capabilities/ContainerObservability/Observability/GraphExporter.php` | Class/file is 341 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0448 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Application/Container/System/Capabilities/Composition/Assembly/RuntimeAssembly.php:21` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0450 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/Application/Container/System/Capabilities/Composition/Compilation/ArtifactMetadata.php` | Class/file is 436 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0453 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/Application/Container/System/Capabilities/Composition/Compilation/CompileContainer.php` | Class/file is 1310 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0455 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/Application/Container/System/Capabilities/Declaration/Ownership/RegistrationMetadata.php` | Class/file is 454 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0456 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Application/Container/System/Capabilities/Declaration/Blueprints/DependencyBlueprint.php:23` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0457 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/Application/Container/System/Capabilities/Declaration/Bindings/DependencyRegistry.php` | Class/file is 1150 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0458 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistry.php` | Class/file is 1148 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0459 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/Application/Container/System/Capabilities/Declaration/Bindings/DependencyRegistration.php` | Class/file is 423 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0460 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistration.php` | Class/file is 425 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0439 | LOW | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/Application/Container/System/Foundation/DIContainer.php` | Class/file is 538 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |

## 15. Component Decision

Decision: **NEEDS_HIGH_REMEDIATION**

Reason: highest severity is HIGH with 33 finding(s); tests detected: yes. This is review evidence only and is not a GREEN production-readiness claim.
