# How-To Deviations: components/Identity/Auth

Generated: 2026-05-19T23:31:00+02:00

## 1. Review Unit Identity

- Review unit ID: RUC-049
- Root type: COMPONENT
- Path: `components/Identity/Auth`
- Purpose inferred from code: `components/Identity/Auth` owns the discovered runtime or user action flow boundary.
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
| `.agents/how-to/how-to-clean-code.md` | correctness, readability, simplicity, cohesive units, meaningful names | YES | FAIL | DR-0602, DR-0612, DR-0624, DR-0626 | Class/file is 678 lines (>300).; Class/file is 871 lines (>300). | Address concrete deviations listed below and rerun named gates. | MEDIUM | HTD-0601, HTD-0611, HTD-0623, HTD-0625 |
| `.agents/how-to/how-to-code-review.md` | strict review process; hard gates; symptom/root-cause/impact/evidence/risk findings | YES | FAIL | DR-0359, DR-0361, DR-0370, DR-0371, DR-0372, DR-0373... | Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency. | Address concrete deviations listed below and rerun named gates. | BLOCKER | HTD-0358, HTD-0360, HTD-0369, HTD-0370, HTD-0371, HTD-0372... |
| `.agents/how-to/how-to-code-style.md` | constructor promotion, nullable type format, return types, named argument discipline | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-coding-standards.md` | modern PHP, strict types, architecture and naming standards | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-dependency-injection.md` | constructor injection, service provider ownership, no service locator, fail-fast dependencies | YES | FAIL | DR-0357, DR-0358, DR-0359, DR-0360, DR-0361, DR-0362... | PublicSurface directly instantiates collaborators (1 `new` expressions detected).; PublicSurface directly instantiates collaborators (1 `new` expressions detected). | Address concrete deviations listed below and rerun named gates. | BLOCKER | HTD-0356, HTD-0357, HTD-0358, HTD-0359, HTD-0360, HTD-0361... |
| `.agents/how-to/how-to-design-components.md` | canonical component shape; PublicSurface/Flows/Capabilities/Configuration/Foundation roles | YES | FAIL | DR-0357, DR-0358, DR-0359, DR-0360, DR-0361, DR-0362... | PublicSurface directly instantiates collaborators (1 `new` expressions detected).; PublicSurface directly instantiates collaborators (1 `new` expressions detected). | Address concrete deviations listed below and rerun named gates. | BLOCKER | HTD-0356, HTD-0357, HTD-0358, HTD-0359, HTD-0360, HTD-0361... |
| `.agents/how-to/how-to-document.md` | canonical documentation location, HOW_THIS_WORKS standard, evidence truthfulness | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-dogfooding.md` | first-party capability reuse, public boundary dogfooding, storage/queue/messaging/internal reuse | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-events-listeners-event-sourcing-cqrs-realtime.md` | events/listeners/CQRS/realtime vocabulary and placement | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-git.md` | commit discipline, no unrelated staging, security must scream, gate self-tests | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-modern-php-attributes-di.md` | modern PHP feature use, attribute validation, DI/autowiring discipline, constructor pressure | YES | FAIL | DR-0359, DR-0360, DR-0361, DR-0362, DR-0363, DR-0364... | Constructor default parameter instantiates a dependency.; Null-coalescing fallback instantiates a dependency. | Address concrete deviations listed below and rerun named gates. | BLOCKER | HTD-0358, HTD-0359, HTD-0360, HTD-0361, HTD-0362, HTD-0363... |
| `.agents/how-to/how-to-production-readiness.md` | readiness color rules, validation agreement, no optimistic readiness claims | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-runtime-composition.md` | no runtime composition leaks; no hidden fallback construction; boot/run separation | YES | FAIL | DR-0359, DR-0360, DR-0361, DR-0362, DR-0363, DR-0364... | Constructor default parameter instantiates a dependency.; Null-coalescing fallback instantiates a dependency. | Address concrete deviations listed below and rerun named gates. | BLOCKER | HTD-0358, HTD-0359, HTD-0360, HTD-0361, HTD-0362, HTD-0363... |
| `.agents/how-to/how-to-system-performance.md` | no hidden I/O, bounded work, hot path proof, memory discipline | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-system-security.md` | deny by default, no secret exposure, secure defaults, negative tests for security-sensitive behavior | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-unit-test.md` | behavior proof, failure/edge tests, public behavior tests, no smoke-only tests | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-use-advanced-architecture-patterns.md` | no pattern-name dumping grounds; explicit decision rules for advanced patterns | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-write-avax.md` | local AvaX writing, naming, runtime, evidence, and testing rules | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |

## 4. Exact Deviation Findings

### How-To Deviation: PublicSurface directly instantiates collaborators (1 `new` expressions detected).

- Deviation ID: HTD-0356
- Review unit: `components/Identity/Auth`
- Governance source: how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4
- Required rule: public API boundary must satisfy the cited how-to contract.
- Observed gap: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Where it fails: `components/Identity/Auth/System/PublicSurface/UserRecord.php:19`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: remove
- Suggested fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Severity: MEDIUM
- Evidence: original finding `DR-0357`; gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0356

### How-To Deviation: PublicSurface directly instantiates collaborators (1 `new` expressions detected).

- Deviation ID: HTD-0357
- Review unit: `components/Identity/Auth`
- Governance source: how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4
- Required rule: public API boundary must satisfy the cited how-to contract.
- Observed gap: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Where it fails: `components/Identity/Auth/System/PublicSurface/User.php:19`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: remove
- Suggested fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Severity: MEDIUM
- Evidence: original finding `DR-0358`; gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0357

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0358
- Review unit: `components/Identity/Auth`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/Identity/Auth/System/Foundation/Time/Expiry.php:16`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: MEDIUM
- Evidence: original finding `DR-0359`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0358

### How-To Deviation: Null-coalescing fallback instantiates a dependency.

- Deviation ID: HTD-0359
- Review unit: `components/Identity/Auth`
- Governance source: how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Null-coalescing fallback instantiates a dependency.
- Where it fails: `components/Identity/Auth/System/Foundation/Time/Expiry.php:16`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Severity: MEDIUM
- Evidence: original finding `DR-0360`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0359

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0360
- Review unit: `components/Identity/Auth`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/Identity/Auth/System/Foundation/Time/Expiry.php:23`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: MEDIUM
- Evidence: original finding `DR-0361`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0360

### How-To Deviation: Null-coalescing fallback instantiates a dependency.

- Deviation ID: HTD-0361
- Review unit: `components/Identity/Auth`
- Governance source: how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Null-coalescing fallback instantiates a dependency.
- Where it fails: `components/Identity/Auth/System/Foundation/Time/Expiry.php:23`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Severity: MEDIUM
- Evidence: original finding `DR-0362`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0361

### How-To Deviation: Null-coalescing fallback instantiates a dependency.

- Deviation ID: HTD-0362
- Review unit: `components/Identity/Auth`
- Governance source: how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Null-coalescing fallback instantiates a dependency.
- Where it fails: `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:203`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Severity: MEDIUM
- Evidence: original finding `DR-0363`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0362

### How-To Deviation: Null-coalescing fallback instantiates a dependency.

- Deviation ID: HTD-0363
- Review unit: `components/Identity/Auth`
- Governance source: how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Null-coalescing fallback instantiates a dependency.
- Where it fails: `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:204`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Severity: MEDIUM
- Evidence: original finding `DR-0364`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0363

### How-To Deviation: Null-coalescing fallback instantiates a dependency.

- Deviation ID: HTD-0364
- Review unit: `components/Identity/Auth`
- Governance source: how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Null-coalescing fallback instantiates a dependency.
- Where it fails: `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:208`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Severity: MEDIUM
- Evidence: original finding `DR-0365`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0364

### How-To Deviation: Null-coalescing fallback instantiates a dependency.

- Deviation ID: HTD-0365
- Review unit: `components/Identity/Auth`
- Governance source: how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Null-coalescing fallback instantiates a dependency.
- Where it fails: `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:209`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Severity: MEDIUM
- Evidence: original finding `DR-0366`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0365

### How-To Deviation: Null-coalescing fallback instantiates a dependency.

- Deviation ID: HTD-0366
- Review unit: `components/Identity/Auth`
- Governance source: how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Null-coalescing fallback instantiates a dependency.
- Where it fails: `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:210`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Severity: MEDIUM
- Evidence: original finding `DR-0367`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0366

### How-To Deviation: Null-coalescing fallback instantiates a dependency.

- Deviation ID: HTD-0367
- Review unit: `components/Identity/Auth`
- Governance source: how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Null-coalescing fallback instantiates a dependency.
- Where it fails: `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:211`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Severity: MEDIUM
- Evidence: original finding `DR-0368`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0367

### How-To Deviation: Null-coalescing fallback instantiates a dependency.

- Deviation ID: HTD-0368
- Review unit: `components/Identity/Auth`
- Governance source: how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Null-coalescing fallback instantiates a dependency.
- Where it fails: `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php:607`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Make dependency explicit and fail during boot/verification, or move fallback to approved composition context.
- Severity: MEDIUM
- Evidence: original finding `DR-0369`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0368

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0369
- Review unit: `components/Identity/Auth`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/Identity/Auth/System/Flows/Register/CreateRegisteredUser.php:26`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: HIGH
- Evidence: original finding `DR-0370`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0369

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0370
- Review unit: `components/Identity/Auth`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/Identity/Auth/System/Capabilities/SessionIdentity/SessionIdentity.php:27`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: MEDIUM
- Evidence: original finding `DR-0371`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0370

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0371
- Review unit: `components/Identity/Auth`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/Identity/Auth/System/Capabilities/Tokens/TokenCodec.php:26`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: MEDIUM
- Evidence: original finding `DR-0372`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0371

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0372
- Review unit: `components/Identity/Auth`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/DeprovisionUser/DeprovisionUser.php:38`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: HIGH
- Evidence: original finding `DR-0373`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0372

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0373
- Review unit: `components/Identity/Auth`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/SuspendUser/SuspendUser.php:38`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: HIGH
- Evidence: original finding `DR-0374`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0373

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0374
- Review unit: `components/Identity/Auth`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/ReactivateUser/ReactivateUser.php:23`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: HIGH
- Evidence: original finding `DR-0375`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0374

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0375
- Review unit: `components/Identity/Auth`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ReadGroups/ReadScimGroups.php:22`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: HIGH
- Evidence: original finding `DR-0376`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0375

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0376
- Review unit: `components/Identity/Auth`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ReadUsers/ReadScimUsers.php:30`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: HIGH
- Evidence: original finding `DR-0377`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0376

### How-To Deviation: Constructor has 27 parameters.

- Deviation ID: HTD-0599
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 27 parameters.
- Where it fails: `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthExternalIdentityGraph.php:92`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: HIGH
- Evidence: original finding `DR-0600`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0599

### How-To Deviation: Constructor has 43 parameters.

- Deviation ID: HTD-0600
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 43 parameters.
- Where it fails: `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:136`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: HIGH
- Evidence: original finding `DR-0601`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0600

### How-To Deviation: Class/file is 678 lines (>300).

- Deviation ID: HTD-0601
- Review unit: `components/Identity/Auth`
- Governance source: how-to-code-review.md §21; how-to-clean-code.md module design
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Class/file is 678 lines (>300).
- Where it fails: `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Severity: MEDIUM
- Evidence: original finding `DR-0602`; gate `php tooling/governance/check-large-unit-thresholds.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0601

### How-To Deviation: Configuration builder is 797 lines (>300).

- Deviation ID: HTD-0602
- Review unit: `components/Identity/Auth`
- Governance source: how-to-code-review.md §21; how-to-design-components.md §6.5.1
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Configuration builder is 797 lines (>300).
- Where it fails: `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split assembly by exact responsibility without changing public API or runtime behavior.
- Severity: BLOCKER
- Evidence: original finding `DR-0603`; gate `php tooling/governance/check-large-unit-thresholds.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0602

### How-To Deviation: Constructor has 12 parameters.

- Deviation ID: HTD-0603
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 12 parameters.
- Where it fails: `components/Identity/Auth/System/Flows/ChangePassword/ChangePassword.php:34`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: HIGH
- Evidence: original finding `DR-0604`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0603

### How-To Deviation: Constructor has 9 parameters.

- Deviation ID: HTD-0604
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 9 parameters.
- Where it fails: `components/Identity/Auth/System/Flows/ChangeEmail/BeginEmailChange.php:23`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0605`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0604

### How-To Deviation: Constructor has 11 parameters.

- Deviation ID: HTD-0605
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 11 parameters.
- Where it fails: `components/Identity/Auth/System/Flows/ChangeEmail/ConfirmEmailChange.php:23`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0606`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0605

### How-To Deviation: Constructor has 9 parameters.

- Deviation ID: HTD-0606
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 9 parameters.
- Where it fails: `components/Identity/Auth/System/Flows/RecoverAccess/PasswordReset/ResetPassword.php:23`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0607`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0606

### How-To Deviation: Constructor has 8 parameters.

- Deviation ID: HTD-0607
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 8 parameters.
- Where it fails: `components/Identity/Auth/System/Flows/CheckAuthentication/AuthenticateRequest/AuthenticateRequest.php:24`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0608`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0607

### How-To Deviation: Constructor has 8 parameters.

- Deviation ID: HTD-0608
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 8 parameters.
- Where it fails: `components/Identity/Auth/System/Flows/CheckAuthentication/AuthenticateRequest/AuthenticatedUser.php:29`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0609`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0608

### How-To Deviation: Constructor has 12 parameters.

- Deviation ID: HTD-0609
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 12 parameters.
- Where it fails: `components/Identity/Auth/System/Flows/CheckAuthentication/AuthenticateRequest/AuthenticationContext.php:16`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: HIGH
- Evidence: original finding `DR-0610`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0609

### How-To Deviation: Constructor has 10 parameters.

- Deviation ID: HTD-0610
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 10 parameters.
- Where it fails: `components/Identity/Auth/System/Capabilities/Identity/Identity.php:39`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0611`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0610

### How-To Deviation: Class/file is 871 lines (>300).

- Deviation ID: HTD-0611
- Review unit: `components/Identity/Auth`
- Governance source: how-to-code-review.md §21; how-to-clean-code.md module design
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Class/file is 871 lines (>300).
- Where it fails: `components/Identity/Auth/System/Capabilities/Authentication/DefaultAuth.php`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Severity: MEDIUM
- Evidence: original finding `DR-0612`; gate `php tooling/governance/check-large-unit-thresholds.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0611

### How-To Deviation: Constructor has 8 parameters.

- Deviation ID: HTD-0612
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 8 parameters.
- Where it fails: `components/Identity/Auth/System/Capabilities/SessionIdentity/SessionIdentity.php:14`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0613`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0612

### How-To Deviation: Constructor has 12 parameters.

- Deviation ID: HTD-0613
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 12 parameters.
- Where it fails: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/SCIM.php:38`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: HIGH
- Evidence: original finding `DR-0614`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0613

### How-To Deviation: Constructor has 13 parameters.

- Deviation ID: HTD-0614
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 13 parameters.
- Where it fails: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Directories/ScimDirectory.php:17`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: HIGH
- Evidence: original finding `DR-0615`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0614

### How-To Deviation: Constructor has 9 parameters.

- Deviation ID: HTD-0615
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 9 parameters.
- Where it fails: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/DeprovisionUser/DeprovisionUser.php:22`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0616`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0615

### How-To Deviation: Constructor has 9 parameters.

- Deviation ID: HTD-0616
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 9 parameters.
- Where it fails: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/SuspendUser/SuspendUser.php:22`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0617`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0616

### How-To Deviation: Constructor has 8 parameters.

- Deviation ID: HTD-0617
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 8 parameters.
- Where it fails: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ProvisionUser/ScimProvisioningResult.php:14`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0618`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0617

### How-To Deviation: Constructor has 8 parameters.

- Deviation ID: HTD-0618
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 8 parameters.
- Where it fails: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ProvisionUser/ProvisionScimUserData.php:18`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0619`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0618

### How-To Deviation: Constructor has 10 parameters.

- Deviation ID: HTD-0619
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 10 parameters.
- Where it fails: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ProvisionUser/ProvisionScimUser.php:34`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0620`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0619

### How-To Deviation: Constructor has 8 parameters.

- Deviation ID: HTD-0620
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 8 parameters.
- Where it fails: `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ReadUsers/ScimUserProjection.php:16`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0621`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0620

### How-To Deviation: Constructor has 8 parameters.

- Deviation ID: HTD-0621
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 8 parameters.
- Where it fails: `components/Identity/Auth/System/Capabilities/Identity/User/User.php:21`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0622`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0621

### How-To Deviation: Constructor has 11 parameters.

- Deviation ID: HTD-0622
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 11 parameters.
- Where it fails: `components/Identity/Auth/System/Capabilities/Identity/Session/SessionIdentity.php:32`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0623`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0622

### How-To Deviation: Class/file is 311 lines (>300).

- Deviation ID: HTD-0623
- Review unit: `components/Identity/Auth`
- Governance source: how-to-code-review.md §21; how-to-clean-code.md module design
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Class/file is 311 lines (>300).
- Where it fails: `components/Identity/Auth/System/Capabilities/Identity/Session/SessionIdentity.php`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Severity: MEDIUM
- Evidence: original finding `DR-0624`; gate `php tooling/governance/check-large-unit-thresholds.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0623

### How-To Deviation: Constructor has 10 parameters.

- Deviation ID: HTD-0624
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 10 parameters.
- Where it fails: `components/Identity/Auth/System/Capabilities/Identity/Jwt/JwtIdentity.php:38`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0625`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0624

### How-To Deviation: Class/file is 390 lines (>300).

- Deviation ID: HTD-0625
- Review unit: `components/Identity/Auth`
- Governance source: how-to-code-review.md §21; how-to-clean-code.md module design
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Class/file is 390 lines (>300).
- Where it fails: `components/Identity/Auth/System/Capabilities/Identity/Jwt/JwtIdentity.php`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Severity: MEDIUM
- Evidence: original finding `DR-0626`; gate `php tooling/governance/check-large-unit-thresholds.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0625

### How-To Deviation: Constructor has 11 parameters.

- Deviation ID: HTD-0626
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 11 parameters.
- Where it fails: `components/Identity/Auth/System/Capabilities/Identity/Sessions/Runtime/ActiveSession.php:17`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0627`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0626

### How-To Deviation: Constructor has 11 parameters.

- Deviation ID: HTD-0627
- Review unit: `components/Identity/Auth`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 11 parameters.
- Where it fails: `components/Identity/Auth/System/Capabilities/Identity/Sessions/Registry/SessionRecord.php:17`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0628`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0627

## 5. Deviations Summary

- Total deviations: 50
- BLOCKER count: 1
- HIGH count: 12
- MEDIUM count: 37
- LOW count: 0
- Blocked/unverifiable rules: none at file-read level; semantic proof is bounded by evidence above.
- Highest severity: BLOCKER
- Decision: **BLOCKED_BY_HOW_TO**
