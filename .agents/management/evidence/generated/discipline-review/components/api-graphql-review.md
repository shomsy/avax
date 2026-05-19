# Component Review: components/API/GraphQL

Generated: 2026-05-19T22:16:56+02:00

## 1. Component Identity

- Component path: `components/API/GraphQL`
- Parent group: `components/API`
- Purpose inferred from code: Owns BatchLoadFields, DataLoader, AuthorizeGraphQLField, AuthorizeGraphQLOperation, GraphQLField, GraphQLObjectType, GraphQLSelection, ParseGraphQLOperation and related behavior.
- Public API surface: `components/API/GraphQL/System/PublicSurface/GraphQL.php`, `components/API/GraphQL/System/PublicSurface/GraphQLExecutionResult.php`, `components/API/GraphQL/System/PublicSurface/GraphQLExecutor.php`, `components/API/GraphQL/System/PublicSurface/GraphQLOperationReport.php`, `components/API/GraphQL/System/PublicSurface/GraphQLSchema.php`
- Internal ownership model: System folders present are PublicSurface, Flows, Capabilities, Configuration, Foundation.
- Main flows: BuildGraphQLSchema, ExecuteGraphQLMutation, ExecuteGraphQLQuery, ValidateGraphQLOperation
- Main capabilities: BatchFieldLoading, BatchFieldLoading, GraphQLAuthorization, GraphQLAuthorization, GraphQLSchemaModel, GraphQLSchemaModel, GraphQLSchemaModel, GraphQLSchemaModel, GraphQLSchemaModel, OperationComplexity, OperationComplexity, ResolverExecution, ResolverExecution, ResolverExecution, ResolverTiming, ResolverTiming, ResolverTiming, SchemaFieldAssembly, SchemaRouting, SchemaSerialization
- Configuration owners: `components/API/GraphQL/System/Configuration/GraphQLConfiguration.php`
- Dependencies on other components: API/GraphQL (73)

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
| DR-0016 | HIGH | `how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5` | `components/API/GraphQL` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | CONFIGURATION_DI | Add a precise System/Configuration/*ServiceProvider that registers existing dependencies without creating new feature behavior. | ServiceProvider coverage batch | `php tooling/refactor/check-service-provider-coverage.php` | NO | YES |
| DR-0322 | HIGH | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/API/GraphQL/System/PublicSurface/GraphQL.php:17,24,39,52` | PublicSurface directly instantiates collaborators (4 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0323 | HIGH | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/API/GraphQL/System/PublicSurface/GraphQLSchema.php:59,61,62,63,80,104,121` | PublicSurface directly instantiates collaborators (7 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0324 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/API/GraphQL/System/PublicSurface/GraphQLExecutor.php:27` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0325 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/API/GraphQL/System/PublicSurface/GraphQLExecutor.php:30` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0326 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/API/GraphQL/System/PublicSurface/GraphQLExecutor.php:31` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0327 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/API/GraphQL/System/PublicSurface/GraphQLExecutor.php:32` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0328 | HIGH | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/API/GraphQL/System/PublicSurface/GraphQLExecutor.php:27,30,31,32,54,68,86,103` | PublicSurface directly instantiates collaborators (8 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0329 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/API/GraphQL/System/Flows/ExecuteGraphQLMutation/ExecuteGraphQLMutation.php:18` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0330 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/API/GraphQL/System/Flows/ExecuteGraphQLQuery/ExecuteGraphQLQuery.php:18` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0331 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/API/GraphQL/System/Flows/ValidateGraphQLOperation/ValidateGraphQLOperation.php:21` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0332 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/API/GraphQL/System/Flows/ValidateGraphQLOperation/ValidateGraphQLOperation.php:22` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0333 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/API/GraphQL/System/Flows/ValidateGraphQLOperation/ValidateGraphQLOperation.php:23` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0334 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/API/GraphQL/System/Flows/ValidateGraphQLOperation/ValidateGraphQLOperation.php:24` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0335 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/API/GraphQL/System/Capabilities/ResolverExecution/ResolveGraphQLOperation.php:23` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0336 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/API/GraphQL/System/Capabilities/ResolverExecution/ResolveGraphQLOperation.php:24` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0337 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/API/GraphQL/System/Capabilities/ResolverExecution/ResolveGraphQLOperation.php:25` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0338 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/API/GraphQL/System/Capabilities/BatchFieldLoading/DataLoader.php:23` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0339 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/API/GraphQL/System/Capabilities/GraphQLAuthorization/AuthorizeGraphQLOperation.php:15` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0550 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/API/GraphQL/System/PublicSurface/GraphQLSchema.php:39` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0551 | MEDIUM | `how-to-code-review.md §21; how-to-design-components.md §6.2` | `components/API/GraphQL/System/PublicSurface/GraphQLSchema.php` | PublicSurface file is 165 lines (>150). | PUBLIC_API | Classify methods as delegate vs behavior; move internal behavior behind flows/capabilities without API churn. | PublicSurface size classification batch | `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php` | NO | YES |

## 15. Component Decision

Decision: **NEEDS_HIGH_REMEDIATION**

Reason: highest severity is HIGH with 21 finding(s); tests detected: yes. This is review evidence only and is not a GREEN production-readiness claim.
