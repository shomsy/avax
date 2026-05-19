# Component Review: components/DataStack/Database

Generated: 2026-05-19T22:16:56+02:00

## 1. Component Identity

- Component path: `components/DataStack/Database`
- Parent group: `components/DataStack`
- Purpose inferred from code: Owns BloomFilter, DatabaseBloomFilter, TwoPhaseCommit, DatabaseConnection, ConnectionPool, ConnectionConfig, DatabaseDriver, Dsn and related behavior.
- Public API surface: `components/DataStack/Database/System/PublicSurface/Database.php`, `components/DataStack/Database/System/PublicSurface/DatabaseInterface.php`, `components/DataStack/Database/System/PublicSurface/Entities.php`, `components/DataStack/Database/System/PublicSurface/ManageEntityPersistence.php`, `components/DataStack/Database/System/PublicSurface/Migrations.php`, `components/DataStack/Database/System/PublicSurface/Query.php`, `components/DataStack/Database/System/PublicSurface/Schema.php`, `components/DataStack/Database/System/PublicSurface/Telemetry.php`, `components/DataStack/Database/System/PublicSurface/Transactions.php`, `components/DataStack/Database/System/PublicSurface/shortcuts.php`
- Internal ownership model: System folders present are PublicSurface, Flows, Capabilities, Configuration, Foundation.
- Main flows: BuildDatabaseSchema, BuildDatabaseSchema, ConnectToDatabase, ConnectToDatabase, ConnectToDatabase, ExecuteQuery, RunDatabaseMigration, RunDatabaseQuery, RunDatabaseQuery, RunDatabaseQuery, RunDatabaseQuery, RunDatabaseTransaction, RunDatabaseTransaction
- Main capabilities: Advanced, Advanced, Advanced, ConnectionContracts, Connections, ConnectionTypes, ConnectionTypes, ConnectionTypes, Connections, Connections, Exceptions, Exceptions, Exceptions, Connections, Connections, OpenConnection, OpenConnection, OpenConnection, Pools, Pools, Pools, Pools, Pools, Pools, Pools, Pools, DTO, Pools, Pools, Pools, Pools, Pools, Pools, Pools, PdoConnectionPool, PdoPooledConnection,
- Configuration owners: `components/DataStack/Database/System/Configuration/Builders/DatabaseBuilder.php`, `components/DataStack/Database/System/Configuration/Builders/RegisterDatabaseDependencies.php`, `components/DataStack/Database/System/Configuration/DatabaseRegistrar.php`, `components/DataStack/Database/System/Configuration/DatabaseServiceProvider.php`
- Dependencies on other components: DataStack/Database (440), Application/Container (9), Application/Filesystem (5), DataStack/DataTransfer (4), framework (3), Operations/Filesystem (2), psr (2), Application/Validation (1)

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
| DR-0267 | HIGH | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/DataStack/Database/System/PublicSurface/shortcuts.php:48,67,86` | PublicSurface directly instantiates collaborators (3 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0268 | HIGH | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/DataStack/Database/System/PublicSurface/Database.php:36,51,56,61,66,71,76` | PublicSurface directly instantiates collaborators (7 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0269 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Database/System/PublicSurface/ManageEntityPersistence.php:20` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0270 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Database/System/PublicSurface/ManageEntityPersistence.php:21` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0271 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Database/System/PublicSurface/ManageEntityPersistence.php:22` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0272 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Database/System/PublicSurface/ManageEntityPersistence.php:27` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0273 | HIGH | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/DataStack/Database/System/PublicSurface/ManageEntityPersistence.php:20,21,22,27` | PublicSurface directly instantiates collaborators (4 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0535 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/DataStack/Database/System/Capabilities/Query/State/QueryState.php:71` | Constructor has 16 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0274 | MEDIUM | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/DataStack/Database/System/PublicSurface/Query.php:24` | PublicSurface directly instantiates collaborators (1 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0275 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Database/System/Capabilities/Observability/QueryTimeline.php:44` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0276 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Database/System/Capabilities/Connections/MultiTenantPool.php:26` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0277 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Database/System/Capabilities/Connections/Pools/DatabaseConnectionPool.php:44` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0278 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Database/System/Capabilities/Connections/Pools/DatabaseConnectionPool.php:45` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0279 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Database/System/Capabilities/Query/IR/IRBuilder.php:19` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0280 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Database/System/Capabilities/Query/CreateBuilder/CreateBuilder.php:37` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0281 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Database/System/Capabilities/Query/State/QueryState.php:75` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0282 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Database/System/Capabilities/Query/Builder/QueryBuilder.php:68` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0283 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Database/System/Capabilities/Query/Builder/QueryBuilder.php:72` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0284 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Database/System/Capabilities/Query/Projections/ResultMapper.php:25` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0285 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Database/System/Capabilities/Query/Advanced/BulkOperations/BulkUpsert.php:30` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0286 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Database/System/Capabilities/Migrations/CLI/SeederCommand.php:28` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0522 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/DataStack/Database/System/PublicSurface/Database.php:23` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0523 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/DataStack/Database/System/Capabilities/Observability/QueryEntry.php:14` | Constructor has 9 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0524 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/DataStack/Database/System/Capabilities/Observability/SlowQueryStatistics.php:12` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0525 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/DataStack/Database/System/Capabilities/Observability/SlowQueryDetector.php:15` | Constructor has 9 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0526 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/DataStack/Database/System/Capabilities/Observability/SlowQueryDetector.php:94` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0527 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/DataStack/Database/System/Capabilities/Observability/SlowQueryDetector.php` | Class/file is 394 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0528 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/DataStack/Database/System/Capabilities/Observability/SlowQueryReport.php:15` | Constructor has 9 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0529 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/DataStack/Database/System/Capabilities/Transactions/DeadlockReport.php:14` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0530 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/DataStack/Database/System/Capabilities/Transactions/DeadlockDetector.php:17` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0531 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/DataStack/Database/System/Capabilities/Transactions/DeadlockDetector.php` | Class/file is 326 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0532 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/DataStack/Database/System/Capabilities/Connections/ConnectionTypes/ConnectionConfig.php:37` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0533 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/DataStack/Database/System/Capabilities/Connections/Pools/PdoConnectionPool/PdoConnectionPool.php:28` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0534 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/DataStack/Database/System/Capabilities/Transactions/RunTransaction/Transaction.php` | Class/file is 510 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0536 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/DataStack/Database/System/Capabilities/Query/State/QueryState.php` | Class/file is 368 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0537 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/DataStack/Database/System/Capabilities/Query/Builder/QueryBuilder.php` | Class/file is 589 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0538 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/DataStack/Database/System/Capabilities/Query/Execution/QueryOrchestrator.php` | Class/file is 345 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0539 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/DataStack/Database/System/Capabilities/Query/Grammar/Grammar.php` | Class/file is 500 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0540 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/DataStack/Database/System/Capabilities/Telemetry/OpenTelemetry/QuerySpan.php:9` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0541 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/DataStack/Database/System/Capabilities/ORM/Metadata/RelationMetadata.php:14` | Constructor has 10 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0542 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/DataStack/Database/System/Capabilities/ORM/Persisters/EntityPersister.php` | Class/file is 598 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0543 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/DataStack/Database/System/Capabilities/Migrations/Design/Table/Blueprint.php` | Class/file is 893 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |

## 15. Component Decision

Decision: **NEEDS_HIGH_REMEDIATION**

Reason: highest severity is HIGH with 42 finding(s); tests detected: yes. This is review evidence only and is not a GREEN production-readiness claim.
