# How-To Deviations: components/DataStack/DataTransfer

Generated: 2026-05-19T23:31:00+02:00

## 1. Review Unit Identity

- Review unit ID: RUC-021
- Root type: COMPONENT
- Path: `components/DataStack/DataTransfer`
- Purpose inferred from code: `components/DataStack/DataTransfer` owns the discovered runtime or user action flow boundary.
- Applicable how-to documents: all current `.agents/how-to/how-to-*.md`; non-authoritative backups and fixtures are inventoried globally but not used as local rule sources.

## 2. Rule Applicability Matrix

| Document path | Applies | Reason | Rule families checked |
|---|---|---|---|
| `.agents/how-to/how-to-architecture-extension-with-ddd.md` | YES | Current AvaX how-to rule source; checked against unit evidence and generated finding table. | PublicSurface delegation; domain modeling placement; no runtime leakage |
| `.agents/how-to/how-to-architecture.md` | YES | Current AvaX how-to rule source; checked against unit evidence and generated finding table. | flow/capability ownership; folder says flow or capability; recursive ownership |
| `.agents/how-to/how-to-clean-code.md` | YES | Current AvaX how-to rule source; checked against unit evidence and generated finding table. | correctness, readability, simplicity, cohesive units, meaningful names |
| `.agents/how-to/how-to-code-review.md` | YES | Current AvaX how-to rule source; checked against unit evidence and generated finding table. | strict review process; hard gates; symptom/root-cause/impact/evidence/risk findings |
| `.agents/how-to/how-to-code-style.md` | YES | Current AvaX how-to rule source; checked against unit evidence and generated finding table. | constructor promotion, nullable type format, return types, named argument discipline |
| `.agents/how-to/how-to-coding-standards.md` | YES | Current AvaX how-to rule source; checked against unit evidence and generated finding table. | modern PHP, strict types, architecture and naming standards |
| `.agents/how-to/how-to-dependency-injection.md` | YES | Current AvaX how-to rule source; checked against unit evidence and generated finding table. | constructor injection, service provider ownership, no service locator, fail-fast dependencies |
| `.agents/how-to/how-to-design-components.md` | YES | Current AvaX how-to rule source; checked against unit evidence and generated finding table. | canonical component shape; PublicSurface/Flows/Capabilities/Configuration/Foundation roles |
| `.agents/how-to/how-to-document.md` | YES | Current AvaX how-to rule source; checked against unit evidence and generated finding table. | canonical documentation location, HOW_THIS_WORKS standard, evidence truthfulness |
| `.agents/how-to/how-to-dogfooding.md` | YES | Current AvaX how-to rule source; checked against unit evidence and generated finding table. | first-party capability reuse, public boundary dogfooding, storage/queue/messaging/internal reuse |
| `.agents/how-to/how-to-events-listeners-event-sourcing-cqrs-realtime.md` | PARTIAL | Event/messaging rules apply only if this unit owns or touches event/listener/realtime behavior. | events/listeners/CQRS/realtime vocabulary and placement |
| `.agents/how-to/how-to-git.md` | PARTIAL | Applies to evidence, readiness, staging, and no-fake-GREEN claims rather than every class body. | commit discipline, no unrelated staging, security must scream, gate self-tests |
| `.agents/how-to/how-to-modern-php-attributes-di.md` | YES | Current AvaX how-to rule source; checked against unit evidence and generated finding table. | modern PHP feature use, attribute validation, DI/autowiring discipline, constructor pressure |
| `.agents/how-to/how-to-production-readiness.md` | PARTIAL | Applies to evidence, readiness, staging, and no-fake-GREEN claims rather than every class body. | readiness color rules, validation agreement, no optimistic readiness claims |
| `.agents/how-to/how-to-runtime-composition.md` | YES | Current AvaX how-to rule source; checked against unit evidence and generated finding table. | no runtime composition leaks; no hidden fallback construction; boot/run separation |
| `.agents/how-to/how-to-system-performance.md` | YES | Current AvaX how-to rule source; checked against unit evidence and generated finding table. | no hidden I/O, bounded work, hot path proof, memory discipline |
| `.agents/how-to/how-to-system-security.md` | YES | Current AvaX how-to rule source; checked against unit evidence and generated finding table. | deny by default, no secret exposure, secure defaults, negative tests for security-sensitive behavior |
| `.agents/how-to/how-to-unit-test.md` | YES | Current AvaX how-to rule source; checked against unit evidence and generated finding table. | behavior proof, failure/edge tests, public behavior tests, no smoke-only tests |
| `.agents/how-to/how-to-use-advanced-architecture-patterns.md` | YES | Current AvaX how-to rule source; checked against unit evidence and generated finding table. | no pattern-name dumping grounds; explicit decision rules for advanced patterns |
| `.agents/how-to/how-to-write-avax.md` | YES | Current AvaX how-to rule source; checked against unit evidence and generated finding table. | local AvaX writing, naming, runtime, evidence, and testing rules |

## 3. Governance Compliance Matrix

| Governance document | Rule / requirement | Applies? | Status | Evidence | Missing / weak area | Required action | Severity | Deviation ID |
|---|---|---|---|---|---|---|---|---|
| `.agents/how-to/how-to-architecture-extension-with-ddd.md` | PublicSurface delegation; domain modeling placement; no runtime leakage | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-architecture.md` | flow/capability ownership; folder says flow or capability; recursive ownership | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-clean-code.md` | correctness, readability, simplicity, cohesive units, meaningful names | YES | FAIL | DR-0545, DR-0546 | Class/file is 418 lines (>300).; Class/file is 319 lines (>300). | Address concrete deviations listed below and rerun named gates. | MEDIUM | HTD-0544, HTD-0545 |
| `.agents/how-to/how-to-code-review.md` | strict review process; hard gates; symptom/root-cause/impact/evidence/risk findings | YES | FAIL | DR-0288, DR-0289, DR-0290, DR-0291, DR-0292, DR-0293... | Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency. | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0287, HTD-0288, HTD-0289, HTD-0290, HTD-0291, HTD-0292... |
| `.agents/how-to/how-to-code-style.md` | constructor promotion, nullable type format, return types, named argument discipline | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-coding-standards.md` | modern PHP, strict types, architecture and naming standards | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-dependency-injection.md` | constructor injection, service provider ownership, no service locator, fail-fast dependencies | YES | FAIL | DR-0023, DR-0287, DR-0288, DR-0289, DR-0290, DR-0291... | ServiceProvider coverage gate reports real code but no component ServiceProvider.; PublicSurface directly instantiates collaborators (10 `new` expressions detected). | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0023, HTD-0286, HTD-0287, HTD-0288, HTD-0289, HTD-0290... |
| `.agents/how-to/how-to-design-components.md` | canonical component shape; PublicSurface/Flows/Capabilities/Configuration/Foundation roles | YES | FAIL | DR-0023, DR-0287, DR-0288, DR-0289, DR-0290, DR-0291... | ServiceProvider coverage gate reports real code but no component ServiceProvider.; PublicSurface directly instantiates collaborators (10 `new` expressions detected). | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0023, HTD-0286, HTD-0287, HTD-0288, HTD-0289, HTD-0290... |
| `.agents/how-to/how-to-document.md` | canonical documentation location, HOW_THIS_WORKS standard, evidence truthfulness | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-dogfooding.md` | first-party capability reuse, public boundary dogfooding, storage/queue/messaging/internal reuse | YES | FAIL | DR-0046 | DataTransfer compiled metadata capabilities use raw file/directory operations classified NEEDS_DESIGN_DECISION. | Address concrete deviations listed below and rerun named gates. | MEDIUM | HTD-0045 |
| `.agents/how-to/how-to-events-listeners-event-sourcing-cqrs-realtime.md` | events/listeners/CQRS/realtime vocabulary and placement | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-git.md` | commit discipline, no unrelated staging, security must scream, gate self-tests | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-modern-php-attributes-di.md` | modern PHP feature use, attribute validation, DI/autowiring discipline, constructor pressure | YES | FAIL | DR-0023, DR-0288, DR-0289, DR-0290, DR-0291, DR-0292... | ServiceProvider coverage gate reports real code but no component ServiceProvider.; Constructor default parameter instantiates a dependency. | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0023, HTD-0287, HTD-0288, HTD-0289, HTD-0290, HTD-0291... |
| `.agents/how-to/how-to-production-readiness.md` | readiness color rules, validation agreement, no optimistic readiness claims | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-runtime-composition.md` | no runtime composition leaks; no hidden fallback construction; boot/run separation | YES | FAIL | DR-0023, DR-0288, DR-0289, DR-0290, DR-0291, DR-0292... | ServiceProvider coverage gate reports real code but no component ServiceProvider.; Constructor default parameter instantiates a dependency. | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0023, HTD-0287, HTD-0288, HTD-0289, HTD-0290, HTD-0291... |
| `.agents/how-to/how-to-system-performance.md` | no hidden I/O, bounded work, hot path proof, memory discipline | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-system-security.md` | deny by default, no secret exposure, secure defaults, negative tests for security-sensitive behavior | YES | FAIL | DR-0046 | DataTransfer compiled metadata capabilities use raw file/directory operations classified NEEDS_DESIGN_DECISION. | Address concrete deviations listed below and rerun named gates. | MEDIUM | HTD-0045 |
| `.agents/how-to/how-to-unit-test.md` | behavior proof, failure/edge tests, public behavior tests, no smoke-only tests | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-use-advanced-architecture-patterns.md` | no pattern-name dumping grounds; explicit decision rules for advanced patterns | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-write-avax.md` | local AvaX writing, naming, runtime, evidence, and testing rules | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |

## 4. Exact Deviation Findings

### How-To Deviation: ServiceProvider coverage gate reports real code but no component ServiceProvider.

- Deviation ID: HTD-0023
- Review unit: `components/DataStack/DataTransfer`
- Governance source: how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Where it fails: `components/DataStack/DataTransfer`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Add a precise System/Configuration/*ServiceProvider that registers existing dependencies without creating new feature behavior.
- Severity: HIGH
- Evidence: original finding `DR-0023`; gate `php tooling/refactor/check-service-provider-coverage.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0023

### How-To Deviation: DataTransfer compiled metadata capabilities use raw file/directory operations classified NEEDS_D

- Deviation ID: HTD-0045
- Review unit: `components/DataStack/DataTransfer`
- Governance source: how-to-system-security.md §22; how-to-dogfooding.md Filesystem rule; check-raw-file-operations.php
- Required rule: security boundary must satisfy the cited how-to contract.
- Observed gap: DataTransfer compiled metadata capabilities use raw file/directory operations classified NEEDS_DESIGN_DECISION.
- Where it fails: `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/CompileClassAttributes.php; components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/CompileDataShapeSchema.php`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: harden
- Suggested fix direction: Decide compiled metadata ownership; route through Filesystem/Storage or document a compile-time exception with tests.
- Severity: MEDIUM
- Evidence: original finding `DR-0046`; gate `php tooling/refactor/check-raw-file-operations.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0045

### How-To Deviation: PublicSurface directly instantiates collaborators (10 `new` expressions detected).

- Deviation ID: HTD-0286
- Review unit: `components/DataStack/DataTransfer`
- Governance source: how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4
- Required rule: public API boundary must satisfy the cited how-to contract.
- Observed gap: PublicSurface directly instantiates collaborators (10 `new` expressions detected).
- Where it fails: `components/DataStack/DataTransfer/System/PublicSurface/DataTransfer.php:67,84,92,97,102,110,118,141...`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: remove
- Suggested fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Severity: HIGH
- Evidence: original finding `DR-0287`; gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0286

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0287
- Review unit: `components/DataStack/DataTransfer`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/DataStack/DataTransfer/System/Flows/SerializeDataObject/ConvertDataObjectToJsonApi.php:18`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: HIGH
- Evidence: original finding `DR-0288`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0287

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0288
- Review unit: `components/DataStack/DataTransfer`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/DataStack/DataTransfer/System/Flows/SerializeDataObject/ConvertDataObjectToArray.php:23`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: HIGH
- Evidence: original finding `DR-0289`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0288

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0289
- Review unit: `components/DataStack/DataTransfer`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/DataStack/DataTransfer/System/Flows/ReadDataObject/ReadDataObject.php:20`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: HIGH
- Evidence: original finding `DR-0290`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0289

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0290
- Review unit: `components/DataStack/DataTransfer`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/DataStack/DataTransfer/System/Flows/ReadDataObject/ReadDataObject.php:21`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: HIGH
- Evidence: original finding `DR-0291`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0290

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0291
- Review unit: `components/DataStack/DataTransfer`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/AttributeCompiler.php:35`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: MEDIUM
- Evidence: original finding `DR-0292`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0291

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0292
- Review unit: `components/DataStack/DataTransfer`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/InspectDataShape.php:13`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: MEDIUM
- Evidence: original finding `DR-0293`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0292

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0293
- Review unit: `components/DataStack/DataTransfer`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/DataStack/DataTransfer/System/Capabilities/LegacyTransfer/SerializeLegacyDTO.php:19`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: MEDIUM
- Evidence: original finding `DR-0294`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0293

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0294
- Review unit: `components/DataStack/DataTransfer`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/DataStack/DataTransfer/System/Capabilities/LegacyTransfer/AbstractDTO.php:27`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: MEDIUM
- Evidence: original finding `DR-0295`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0294

### How-To Deviation: Constructor has 9 parameters.

- Deviation ID: HTD-0543
- Review unit: `components/DataStack/DataTransfer`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 9 parameters.
- Where it fails: `components/DataStack/DataTransfer/System/Configuration/DataTransferConfig.php:15`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0544`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0543

### How-To Deviation: Class/file is 418 lines (>300).

- Deviation ID: HTD-0544
- Review unit: `components/DataStack/DataTransfer`
- Governance source: how-to-code-review.md §21; how-to-clean-code.md module design
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Class/file is 418 lines (>300).
- Where it fails: `components/DataStack/DataTransfer/System/Flows/CreateDataObject/CreateDataObject.php`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Severity: MEDIUM
- Evidence: original finding `DR-0545`; gate `php tooling/governance/check-large-unit-thresholds.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0544

### How-To Deviation: Class/file is 319 lines (>300).

- Deviation ID: HTD-0545
- Review unit: `components/DataStack/DataTransfer`
- Governance source: how-to-code-review.md §21; how-to-clean-code.md module design
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Class/file is 319 lines (>300).
- Where it fails: `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/CompileClassAttributes.php`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Severity: MEDIUM
- Evidence: original finding `DR-0546`; gate `php tooling/governance/check-large-unit-thresholds.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0545

### How-To Deviation: Constructor has 11 parameters.

- Deviation ID: HTD-0546
- Review unit: `components/DataStack/DataTransfer`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 11 parameters.
- Where it fails: `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/CompiledAttributeMetadata.php:37`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0547`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0546

### How-To Deviation: Constructor has 12 parameters.

- Deviation ID: HTD-0547
- Review unit: `components/DataStack/DataTransfer`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 12 parameters.
- Where it fails: `components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/DataField.php:21`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: HIGH
- Evidence: original finding `DR-0548`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0547

### How-To Deviation: Constructor has 9 parameters.

- Deviation ID: HTD-0548
- Review unit: `components/DataStack/DataTransfer`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 9 parameters.
- Where it fails: `components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/CompiledSchemaMetadata.php:29`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0549`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0548

## 5. Deviations Summary

- Total deviations: 17
- BLOCKER count: 0
- HIGH count: 7
- MEDIUM count: 10
- LOW count: 0
- Blocked/unverifiable rules: none at file-read level; semantic proof is bounded by evidence above.
- Highest severity: HIGH
- Decision: **HAS_HIGH_DEVIATIONS**
