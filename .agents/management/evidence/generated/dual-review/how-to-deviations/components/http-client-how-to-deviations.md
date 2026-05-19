# How-To Deviations: components/HTTP/Client

Generated: 2026-05-19T23:31:00+02:00

## 1. Review Unit Identity

- Review unit ID: RUC-035
- Root type: COMPONENT
- Path: `components/HTTP/Client`
- Purpose inferred from code: `components/HTTP/Client` owns the discovered runtime or user action flow boundary.
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
| `.agents/how-to/how-to-clean-code.md` | correctness, readability, simplicity, cohesive units, meaningful names | YES | FAIL | DR-0465 | Class/file is 306 lines (>300). | Address concrete deviations listed below and rerun named gates. | MEDIUM | HTD-0464 |
| `.agents/how-to/how-to-code-review.md` | strict review process; hard gates; symptom/root-cause/impact/evidence/risk findings | YES | FAIL | DR-0125, DR-0463, DR-0464, DR-0465, DR-0466 | Constructor default parameter instantiates a dependency.; Constructor has 15 parameters. | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0124, HTD-0462, HTD-0463, HTD-0464, HTD-0465 |
| `.agents/how-to/how-to-code-style.md` | constructor promotion, nullable type format, return types, named argument discipline | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-coding-standards.md` | modern PHP, strict types, architecture and naming standards | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-dependency-injection.md` | constructor injection, service provider ownership, no service locator, fail-fast dependencies | YES | FAIL | DR-0125, DR-0126 | Constructor default parameter instantiates a dependency.; PublicSurface directly instantiates collaborators (3 `new` expressions detected). | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0124, HTD-0125 |
| `.agents/how-to/how-to-design-components.md` | canonical component shape; PublicSurface/Flows/Capabilities/Configuration/Foundation roles | YES | FAIL | DR-0004, DR-0125, DR-0126 | No component-specific tests detected under tests/.; Constructor default parameter instantiates a dependency. | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0004, HTD-0124, HTD-0125 |
| `.agents/how-to/how-to-document.md` | canonical documentation location, HOW_THIS_WORKS standard, evidence truthfulness | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-dogfooding.md` | first-party capability reuse, public boundary dogfooding, storage/queue/messaging/internal reuse | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-events-listeners-event-sourcing-cqrs-realtime.md` | events/listeners/CQRS/realtime vocabulary and placement | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-git.md` | commit discipline, no unrelated staging, security must scream, gate self-tests | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-modern-php-attributes-di.md` | modern PHP feature use, attribute validation, DI/autowiring discipline, constructor pressure | YES | FAIL | DR-0125, DR-0463, DR-0464, DR-0466 | Constructor default parameter instantiates a dependency.; Constructor has 15 parameters. | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0124, HTD-0462, HTD-0463, HTD-0465 |
| `.agents/how-to/how-to-production-readiness.md` | readiness color rules, validation agreement, no optimistic readiness claims | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-runtime-composition.md` | no runtime composition leaks; no hidden fallback construction; boot/run separation | YES | FAIL | DR-0125 | Constructor default parameter instantiates a dependency. | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0124 |
| `.agents/how-to/how-to-system-performance.md` | no hidden I/O, bounded work, hot path proof, memory discipline | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-system-security.md` | deny by default, no secret exposure, secure defaults, negative tests for security-sensitive behavior | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-unit-test.md` | behavior proof, failure/edge tests, public behavior tests, no smoke-only tests | YES | FAIL | DR-0004 | No component-specific tests detected under tests/. | Address concrete deviations listed below and rerun named gates. | MEDIUM | HTD-0004 |
| `.agents/how-to/how-to-use-advanced-architecture-patterns.md` | no pattern-name dumping grounds; explicit decision rules for advanced patterns | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-write-avax.md` | local AvaX writing, naming, runtime, evidence, and testing rules | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |

## 4. Exact Deviation Findings

### How-To Deviation: No component-specific tests detected under tests/.

- Deviation ID: HTD-0004
- Review unit: `components/HTTP/Client`
- Governance source: how-to-unit-test.md; how-to-design-components.md §4
- Required rule: test proof must satisfy the cited how-to contract.
- Observed gap: No component-specific tests detected under tests/.
- Where it fails: `components/HTTP/Client`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: test
- Suggested fix direction: Add behavior-first unit or integration tests before risky remediation in this component.
- Severity: MEDIUM
- Evidence: original finding `DR-0004`; gate `vendor/bin/phpunit --no-coverage --filter <component>`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0004

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0124
- Review unit: `components/HTTP/Client`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/HTTP/Client/System/PublicSurface/HttpClient.php:14`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: HIGH
- Evidence: original finding `DR-0125`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0124

### How-To Deviation: PublicSurface directly instantiates collaborators (3 `new` expressions detected).

- Deviation ID: HTD-0125
- Review unit: `components/HTTP/Client`
- Governance source: how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4
- Required rule: public API boundary must satisfy the cited how-to contract.
- Observed gap: PublicSurface directly instantiates collaborators (3 `new` expressions detected).
- Where it fails: `components/HTTP/Client/System/PublicSurface/HttpClient.php:14,19,34`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: remove
- Suggested fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Severity: HIGH
- Evidence: original finding `DR-0126`; gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0125

### How-To Deviation: Constructor has 15 parameters.

- Deviation ID: HTD-0462
- Review unit: `components/HTTP/Client`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 15 parameters.
- Where it fails: `components/HTTP/Client/System/Capabilities/Requests/RequestOptions.php:40`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: HIGH
- Evidence: original finding `DR-0463`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0462

### How-To Deviation: Constructor has 12 parameters.

- Deviation ID: HTD-0463
- Review unit: `components/HTTP/Client`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 12 parameters.
- Where it fails: `components/HTTP/Client/System/Capabilities/Responses/ClientResponse.php:30`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: HIGH
- Evidence: original finding `DR-0464`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0463

### How-To Deviation: Class/file is 306 lines (>300).

- Deviation ID: HTD-0464
- Review unit: `components/HTTP/Client`
- Governance source: how-to-code-review.md §21; how-to-clean-code.md module design
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Class/file is 306 lines (>300).
- Where it fails: `components/HTTP/Client/System/Capabilities/Transports/CurlTransport.php`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Severity: MEDIUM
- Evidence: original finding `DR-0465`; gate `php tooling/governance/check-large-unit-thresholds.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0464

### How-To Deviation: Constructor has 9 parameters.

- Deviation ID: HTD-0465
- Review unit: `components/HTTP/Client`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 9 parameters.
- Where it fails: `components/HTTP/Client/System/Capabilities/Resilience/RetryPolicy.php:37`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0466`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0465

## 5. Deviations Summary

- Total deviations: 7
- BLOCKER count: 0
- HIGH count: 4
- MEDIUM count: 3
- LOW count: 0
- Blocked/unverifiable rules: none at file-read level; semantic proof is bounded by evidence above.
- Highest severity: HIGH
- Decision: **HAS_HIGH_DEVIATIONS**
