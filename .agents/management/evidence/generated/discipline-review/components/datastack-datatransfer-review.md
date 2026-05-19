# Component Review: components/DataStack/DataTransfer

Generated: 2026-05-19T22:16:56+02:00

## 1. Component Identity

- Component path: `components/DataStack/DataTransfer`
- Parent group: `components/DataStack`
- Purpose inferred from code: Owns AlphaNum, AlphaNumOrEmail, ArrayType, AttributeCompiler, Between, BooleanType, CastWith, CompileClassAttributes and related behavior.
- Public API surface: `components/DataStack/DataTransfer/System/PublicSurface/DataObject.php`, `components/DataStack/DataTransfer/System/PublicSurface/DataTransfer.php`
- Internal ownership model: System folders present are PublicSurface, Flows, Capabilities, Configuration.
- Main flows: CreateDataObject, ReadDataObject, ReadDataObject, ReadDataObject, ReadDataObject, SerializeDataObject, SerializeDataObject, SerializeDataObject, SerializeDataObject, SerializeDataObject, SerializeDataObject
- Main capabilities: AttributeReading, AttributeReading, AttributeReading, AttributeReading, AttributeReading, AttributeReading, AttributeReading, AttributeReading, AttributeReading, AttributeReading, AttributeReading, AttributeReading, AttributeReading, AttributeReading, AttributeReading, AttributeReading, AttributeReading, AttributeReading, AttributeReading, AttributeReading, AttributeReading, AttributeReading, Data
- Configuration owners: `components/DataStack/DataTransfer/System/Configuration/DataTransferConfig.php`, `components/DataStack/DataTransfer/System/Configuration/UnknownFieldPolicy.php`
- Dependencies on other components: DataStack/DataTransfer (70), Application/Filesystem (4), DataStack/Data (2), framework (1)

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

Review result: Finding(s) recorded in the table below.

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
| `how-to-system-security.md` | path/I/O/security-sensitive boundaries | Fail | raw file/security scan | see finding table | HIGH |
| `how-to-system-performance.md` | hot-path/memory/large object graph risks | Pass | performance scan | keep | - |
| `how-to-unit-test.md` | behavior proof exists | Pass | test discovery | keep | - |
| `how-to-document.md` | semantic PHPDoc/evidence truthfulness | Pass | PHPDoc/evidence checks | keep | - |
| `how-to-dogfooding.md` | first-party capabilities reused at boundaries | Fail | raw I/O + DI scans | see finding table | HIGH |

## 14. Finding Table

| ID | Severity | Rule source | Exact file/path | Exact issue | Risk type | Recommended fix | Safe remediation batch | Validation command | Cross-component | fix-this candidate |
|---|---|---|---|---|---|---|---|---|---:|---:|
| DR-0023 | HIGH | `how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5` | `components/DataStack/DataTransfer` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | CONFIGURATION_DI | Add a precise System/Configuration/*ServiceProvider that registers existing dependencies without creating new feature behavior. | ServiceProvider coverage batch | `php tooling/refactor/check-service-provider-coverage.php` | NO | YES |
| DR-0287 | HIGH | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/DataStack/DataTransfer/System/PublicSurface/DataTransfer.php:67,84,92,97,102,110,118,141...` | PublicSurface directly instantiates collaborators (10 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0288 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/DataTransfer/System/Flows/SerializeDataObject/ConvertDataObjectToJsonApi.php:18` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0289 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/DataTransfer/System/Flows/SerializeDataObject/ConvertDataObjectToArray.php:23` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0290 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/DataTransfer/System/Flows/ReadDataObject/ReadDataObject.php:20` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0291 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/DataTransfer/System/Flows/ReadDataObject/ReadDataObject.php:21` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0548 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/DataField.php:21` | Constructor has 12 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0046 | MEDIUM | `how-to-system-security.md §22; how-to-dogfooding.md Filesystem rule; check-raw-file-operations.php` | `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/CompileClassAttributes.php; components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/CompileDataShapeSchema.php` | DataTransfer compiled metadata capabilities use raw file/directory operations classified NEEDS_DESIGN_DECISION. | SECURITY | Decide compiled metadata ownership; route through Filesystem/Storage or document a compile-time exception with tests. | DataTransfer compiled metadata I/O batch | `php tooling/refactor/check-raw-file-operations.php` | NO | YES |
| DR-0292 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/AttributeCompiler.php:35` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0293 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/InspectDataShape.php:13` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0294 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/DataTransfer/System/Capabilities/LegacyTransfer/SerializeLegacyDTO.php:19` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0295 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/DataTransfer/System/Capabilities/LegacyTransfer/AbstractDTO.php:27` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0544 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/DataStack/DataTransfer/System/Configuration/DataTransferConfig.php:15` | Constructor has 9 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0545 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/DataStack/DataTransfer/System/Flows/CreateDataObject/CreateDataObject.php` | Class/file is 418 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0546 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/CompileClassAttributes.php` | Class/file is 319 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0547 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/CompiledAttributeMetadata.php:37` | Constructor has 11 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0549 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/CompiledSchemaMetadata.php:29` | Constructor has 9 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |

## 15. Component Decision

Decision: **NEEDS_HIGH_REMEDIATION**

Reason: highest severity is HIGH with 17 finding(s); tests detected: yes. This is review evidence only and is not a GREEN production-readiness claim.
