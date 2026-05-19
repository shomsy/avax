# How-To Deviations: components/HTTP/Request

Generated: 2026-05-19T23:31:00+02:00

## 1. Review Unit Identity

- Review unit ID: RUC-040
- Root type: COMPONENT
- Path: `components/HTTP/Request`
- Purpose inferred from code: `components/HTTP/Request` owns the discovered runtime or user action flow boundary.
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
| `.agents/how-to/how-to-clean-code.md` | correctness, readability, simplicity, cohesive units, meaningful names | YES | FAIL | DR-0474 | Class/file is 446 lines (>300). | Address concrete deviations listed below and rerun named gates. | MEDIUM | HTD-0473 |
| `.agents/how-to/how-to-code-review.md` | strict review process; hard gates; symptom/root-cause/impact/evidence/risk findings | YES | FAIL | DR-0127, DR-0129, DR-0130, DR-0131, DR-0132, DR-0133... | Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency. | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0126, HTD-0128, HTD-0129, HTD-0130, HTD-0131, HTD-0132... |
| `.agents/how-to/how-to-code-style.md` | constructor promotion, nullable type format, return types, named argument discipline | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-coding-standards.md` | modern PHP, strict types, architecture and naming standards | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-dependency-injection.md` | constructor injection, service provider ownership, no service locator, fail-fast dependencies | YES | FAIL | DR-0127, DR-0128, DR-0129, DR-0130, DR-0131, DR-0132... | Constructor default parameter instantiates a dependency.; PublicSurface directly instantiates collaborators (1 `new` expressions detected). | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0126, HTD-0127, HTD-0128, HTD-0129, HTD-0130, HTD-0131... |
| `.agents/how-to/how-to-design-components.md` | canonical component shape; PublicSurface/Flows/Capabilities/Configuration/Foundation roles | YES | FAIL | DR-0127, DR-0128, DR-0129, DR-0130, DR-0131, DR-0132... | Constructor default parameter instantiates a dependency.; PublicSurface directly instantiates collaborators (1 `new` expressions detected). | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0126, HTD-0127, HTD-0128, HTD-0129, HTD-0130, HTD-0131... |
| `.agents/how-to/how-to-document.md` | canonical documentation location, HOW_THIS_WORKS standard, evidence truthfulness | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-dogfooding.md` | first-party capability reuse, public boundary dogfooding, storage/queue/messaging/internal reuse | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-events-listeners-event-sourcing-cqrs-realtime.md` | events/listeners/CQRS/realtime vocabulary and placement | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-git.md` | commit discipline, no unrelated staging, security must scream, gate self-tests | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-modern-php-attributes-di.md` | modern PHP feature use, attribute validation, DI/autowiring discipline, constructor pressure | YES | FAIL | DR-0127, DR-0129, DR-0130, DR-0131, DR-0132, DR-0133... | Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency. | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0126, HTD-0128, HTD-0129, HTD-0130, HTD-0131, HTD-0132... |
| `.agents/how-to/how-to-production-readiness.md` | readiness color rules, validation agreement, no optimistic readiness claims | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-runtime-composition.md` | no runtime composition leaks; no hidden fallback construction; boot/run separation | YES | FAIL | DR-0127, DR-0129, DR-0130, DR-0131, DR-0132, DR-0133... | Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency. | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0126, HTD-0128, HTD-0129, HTD-0130, HTD-0131, HTD-0132... |
| `.agents/how-to/how-to-system-performance.md` | no hidden I/O, bounded work, hot path proof, memory discipline | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-system-security.md` | deny by default, no secret exposure, secure defaults, negative tests for security-sensitive behavior | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-unit-test.md` | behavior proof, failure/edge tests, public behavior tests, no smoke-only tests | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-use-advanced-architecture-patterns.md` | no pattern-name dumping grounds; explicit decision rules for advanced patterns | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-write-avax.md` | local AvaX writing, naming, runtime, evidence, and testing rules | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |

## 4. Exact Deviation Findings

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0126
- Review unit: `components/HTTP/Request`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/HTTP/Request/System/PublicSurface/Request.php:33`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: HIGH
- Evidence: original finding `DR-0127`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0126

### How-To Deviation: PublicSurface directly instantiates collaborators (1 `new` expressions detected).

- Deviation ID: HTD-0127
- Review unit: `components/HTTP/Request`
- Governance source: how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4
- Required rule: public API boundary must satisfy the cited how-to contract.
- Observed gap: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Where it fails: `components/HTTP/Request/System/PublicSurface/Request.php:33`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: remove
- Suggested fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Severity: MEDIUM
- Evidence: original finding `DR-0128`; gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0127

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0128
- Review unit: `components/HTTP/Request`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/HTTP/Request/System/Configuration/Builders/RequestBuilder.php:43`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: MEDIUM
- Evidence: original finding `DR-0129`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0128

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0129
- Review unit: `components/HTTP/Request`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/HTTP/Request/System/Configuration/Builders/RequestBuilder.php:44`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: MEDIUM
- Evidence: original finding `DR-0130`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0129

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0130
- Review unit: `components/HTTP/Request`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/HTTP/Request/System/Configuration/Builders/RequestBuilder.php:45`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: MEDIUM
- Evidence: original finding `DR-0131`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0130

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0131
- Review unit: `components/HTTP/Request`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/HTTP/Request/System/Configuration/Builders/RequestBuilder.php:46`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: MEDIUM
- Evidence: original finding `DR-0132`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0131

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0132
- Review unit: `components/HTTP/Request`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/HTTP/Request/System/Flows/CreateRequestFromGlobals/CreateRequestFromGlobals.php:41`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: HIGH
- Evidence: original finding `DR-0133`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0132

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0133
- Review unit: `components/HTTP/Request`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/HTTP/Request/System/Capabilities/Headers/RequestHeaders.php:20`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: MEDIUM
- Evidence: original finding `DR-0134`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0133

### How-To Deviation: Constructor has 11 parameters.

- Deviation ID: HTD-0466
- Review unit: `components/HTTP/Request`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 11 parameters.
- Where it fails: `components/HTTP/Request/System/PublicSurface/Request.php:20`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0467`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0466

### How-To Deviation: PublicSurface file is 230 lines (>150).

- Deviation ID: HTD-0467
- Review unit: `components/HTTP/Request`
- Governance source: how-to-code-review.md §21; how-to-design-components.md §6.2
- Required rule: public API boundary must satisfy the cited how-to contract.
- Observed gap: PublicSurface file is 230 lines (>150).
- Where it fails: `components/HTTP/Request/System/PublicSurface/Request.php`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: remove
- Suggested fix direction: Classify methods as delegate vs behavior; move internal behavior behind flows/capabilities without API churn.
- Severity: MEDIUM
- Evidence: original finding `DR-0468`; gate `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0467

### How-To Deviation: Constructor has 9 parameters.

- Deviation ID: HTD-0468
- Review unit: `components/HTTP/Request`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 9 parameters.
- Where it fails: `components/HTTP/Request/System/Flows/CreateRequestFromGlobals/CreateRequestFromGlobals.php:22`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0469`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0468

### How-To Deviation: Constructor has 8 parameters.

- Deviation ID: HTD-0469
- Review unit: `components/HTTP/Request`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 8 parameters.
- Where it fails: `components/HTTP/Request/System/Capabilities/Uri/RequestUri.php:11`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0470`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0469

### How-To Deviation: Constructor has 11 parameters.

- Deviation ID: HTD-0470
- Review unit: `components/HTTP/Request`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 11 parameters.
- Where it fails: `components/HTTP/Request/System/Capabilities/RequestData/RequestData.php:14`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0471`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0470

### How-To Deviation: Constructor has 11 parameters.

- Deviation ID: HTD-0471
- Review unit: `components/HTTP/Request`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 11 parameters.
- Where it fails: `components/HTTP/Request/System/Capabilities/IncomingRequest/ServerRequest.php:35`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0472`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0471

### How-To Deviation: Constructor has 8 parameters.

- Deviation ID: HTD-0472
- Review unit: `components/HTTP/Request`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 8 parameters.
- Where it fails: `components/HTTP/Request/System/Capabilities/IncomingRequest/ServerRequest.php:74`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0473`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0472

### How-To Deviation: Class/file is 446 lines (>300).

- Deviation ID: HTD-0473
- Review unit: `components/HTTP/Request`
- Governance source: how-to-code-review.md §21; how-to-clean-code.md module design
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Class/file is 446 lines (>300).
- Where it fails: `components/HTTP/Request/System/Capabilities/IncomingRequest/ServerRequest.php`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Severity: MEDIUM
- Evidence: original finding `DR-0474`; gate `php tooling/governance/check-large-unit-thresholds.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0473

## 5. Deviations Summary

- Total deviations: 16
- BLOCKER count: 0
- HIGH count: 2
- MEDIUM count: 14
- LOW count: 0
- Blocked/unverifiable rules: none at file-read level; semantic proof is bounded by evidence above.
- Highest severity: HIGH
- Decision: **HAS_HIGH_DEVIATIONS**
