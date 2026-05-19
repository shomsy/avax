# Component Review: components/DataStack/Data

Generated: 2026-05-19T22:16:56+02:00

## 1. Component Identity

- Component path: `components/DataStack/Data`
- Parent group: `components/DataStack`
- Purpose inferred from code: Owns EncodeArray, EncodeJson, EncodeXml, Arrhae, Collection, CollectionInterface, Json, DotPath and related behavior.
- Public API surface: `components/DataStack/Data/System/PublicSurface/Arrhae.php`, `components/DataStack/Data/System/PublicSurface/BloomFilter.php`, `components/DataStack/Data/System/PublicSurface/Collection.php`, `components/DataStack/Data/System/PublicSurface/Data.php`, `components/DataStack/Data/System/PublicSurface/DataInterface.php`, `components/DataStack/Data/System/PublicSurface/Deque.php`, `components/DataStack/Data/System/PublicSurface/Graph.php`, `components/DataStack/Data/System/PublicSurface/Heap.php`, `components/DataStack/Data/System/PublicSurface/Json.php`, `components/DataStack/Data/System/PublicSurface/Map.php`, `components/DataStack/Data/System/PublicSurface/Matrix.php`, `components/DataStack/Data/System/PublicSurface/MultiMap.php`
- Internal ownership model: System folders present are PublicSurface, Flows, Capabilities, Configuration, Foundation.
- Main flows: Aggregate, Aggregate, Aggregate, Aggregate, Batch, CreateCollection, LazySequence, Normalize, Pipeline, Pipeline, Read, ReadDataValue, SerializeStructure, Transform, Transform, Transform, Transform, TransformData, Window, Write, WriteDataValue
- Main capabilities: ArrayCodec, JsonCodec, XmlCodec, ArrayForm, CollectionForm, CollectionForm, JsonForm, DataPath, Aggregate, Aggregate, Aggregate, Aggregate, Aggregate, Arrays, Arrays, ObjectReading, ObjectReading, Ordering, Ordering, Ordering, Ordering, Ordering, Search, Search, Search, Search, Selection, Selection, Transform, Transform, Transform, Transform, Transform, Transform, Transform, Transform, Transform, 
- Configuration owners: `components/DataStack/Data/System/Configuration/Builders/RegisterDataDependencies.php`
- Dependencies on other components: DataStack/Data (172), framework (1)

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
| `how-to-modern-php-attributes-di.md` | constructor bloat and DI discipline | Fail | constructor scan | see finding table | MEDIUM |
| `how-to-system-security.md` | path/I/O/security-sensitive boundaries | Pass | raw file/security scan | keep | - |
| `how-to-system-performance.md` | hot-path/memory/large object graph risks | Pass | performance scan | keep | - |
| `how-to-unit-test.md` | behavior proof exists | Pass | test discovery | keep | - |
| `how-to-document.md` | semantic PHPDoc/evidence truthfulness | Pass | PHPDoc/evidence checks | keep | - |
| `how-to-dogfooding.md` | first-party capabilities reused at boundaries | Fail | raw I/O + DI scans | see finding table | MEDIUM |

## 14. Finding Table

| ID | Severity | Rule source | Exact file/path | Exact issue | Risk type | Recommended fix | Safe remediation batch | Validation command | Cross-component | fix-this candidate |
|---|---|---|---|---|---|---|---|---|---:|---:|
| DR-0022 | MEDIUM | `how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5` | `components/DataStack/Data` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | CONFIGURATION_DI | Add a precise System/Configuration/*ServiceProvider that registers existing dependencies without creating new feature behavior. | ServiceProvider coverage batch | `php tooling/refactor/check-service-provider-coverage.php` | NO | YES |
| DR-0240 | MEDIUM | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/DataStack/Data/System/PublicSurface/Json.php:35` | PublicSurface directly instantiates collaborators (1 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0241 | MEDIUM | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/DataStack/Data/System/PublicSurface/Queue.php:18` | PublicSurface directly instantiates collaborators (1 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0242 | MEDIUM | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/DataStack/Data/System/PublicSurface/Sequence.php:23` | PublicSurface directly instantiates collaborators (1 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0243 | MEDIUM | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/DataStack/Data/System/PublicSurface/Set.php:23` | PublicSurface directly instantiates collaborators (1 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0244 | MEDIUM | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/DataStack/Data/System/PublicSurface/Arrhae.php:24` | PublicSurface directly instantiates collaborators (1 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0245 | MEDIUM | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/DataStack/Data/System/PublicSurface/MultiMap.php:23` | PublicSurface directly instantiates collaborators (1 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0246 | MEDIUM | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/DataStack/Data/System/PublicSurface/Matrix.php:19,29` | PublicSurface directly instantiates collaborators (2 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0247 | MEDIUM | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/DataStack/Data/System/PublicSurface/OrderedSet.php:23` | PublicSurface directly instantiates collaborators (1 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0248 | MEDIUM | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/DataStack/Data/System/PublicSurface/DataInterface.php:32` | PublicSurface directly instantiates collaborators (1 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0249 | MEDIUM | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/DataStack/Data/System/PublicSurface/Deque.php:18` | PublicSurface directly instantiates collaborators (1 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0250 | MEDIUM | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/DataStack/Data/System/PublicSurface/Map.php:23` | PublicSurface directly instantiates collaborators (1 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0251 | MEDIUM | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/DataStack/Data/System/PublicSurface/Stack.php:18` | PublicSurface directly instantiates collaborators (1 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0252 | MEDIUM | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/DataStack/Data/System/PublicSurface/OrderedMap.php:23` | PublicSurface directly instantiates collaborators (1 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0253 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Data/System/Capabilities/Operators/Selection/ReadValueByPath.php:27` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0254 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Data/System/Capabilities/Forms/JsonForm/Json.php:28` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0255 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Data/System/Capabilities/Forms/CollectionForm/Collection.php:32` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0256 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Data/System/Capabilities/Forms/ArrayForm/Arrhae.php:60` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0257 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Data/System/Capabilities/Values/Temporal/Moment.php:28` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0258 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Data/System/Capabilities/Codecs/XmlCodec/EncodeXml.php:30` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0259 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Data/System/Capabilities/Structures/Priority/MinHeap.php:32` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0260 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Data/System/Capabilities/Structures/Priority/MaxHeap.php:32` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0261 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Data/System/Capabilities/Structures/Linear/LinkedList.php:40` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0262 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/DataStack/Data/System/Capabilities/Structures/Functional/Option/None.php:25` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0263 | MEDIUM | `how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule` | `components/DataStack/Data/System/Capabilities/Structures/Functional/Option/None.php:25` | Null-coalescing fallback instantiates a dependency. | CONFIGURATION_DI | Make dependency explicit and fail during boot/verification, or move fallback to approved composition context. | Hidden fallback construction cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0510 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/DataStack/Data/System/Capabilities/Forms/CollectionForm/Collection.php` | Class/file is 454 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0511 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/DataStack/Data/System/Capabilities/Forms/ArrayForm/Arrhae.php` | Class/file is 592 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |

## 15. Component Decision

Decision: **NEEDS_MEDIUM_REMEDIATION**

Reason: highest severity is MEDIUM with 27 finding(s); tests detected: yes. This is review evidence only and is not a GREEN production-readiness claim.
