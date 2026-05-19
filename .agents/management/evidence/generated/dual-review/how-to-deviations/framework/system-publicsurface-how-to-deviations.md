# How-To Deviations: framework/System/PublicSurface

Generated: 2026-05-19T23:31:00+02:00

## 1. Review Unit Identity

- Review unit ID: RUF-080
- Root type: FRAMEWORK
- Path: `framework/System/PublicSurface`
- Purpose inferred from code: `framework/System/PublicSurface` owns the discovered PublicSurface delegation boundary.
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
| `.agents/how-to/how-to-clean-code.md` | correctness, readability, simplicity, cohesive units, meaningful names | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-code-review.md` | strict review process; hard gates; symptom/root-cause/impact/evidence/risk findings | YES | FAIL | DR-0638, DR-0639, DR-0640 | PublicSurface file is 366 lines (>150).; PublicSurface file exceeds review threshold. | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0637, HTD-0638, HTD-0639 |
| `.agents/how-to/how-to-code-style.md` | constructor promotion, nullable type format, return types, named argument discipline | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-coding-standards.md` | modern PHP, strict types, architecture and naming standards | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-dependency-injection.md` | constructor injection, service provider ownership, no service locator, fail-fast dependencies | YES | FAIL | DR-0389, DR-0390, DR-0391, DR-0392, DR-0393, DR-0394... | PublicSurface directly instantiates collaborators.; Constructor default parameter instantiates a dependency. | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0388, HTD-0389, HTD-0390, HTD-0391, HTD-0392, HTD-0393... |
| `.agents/how-to/how-to-design-components.md` | canonical component shape; PublicSurface/Flows/Capabilities/Configuration/Foundation roles | YES | FAIL | DR-0389, DR-0390, DR-0391, DR-0392, DR-0393, DR-0394... | PublicSurface directly instantiates collaborators.; Constructor default parameter instantiates a dependency. | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0388, HTD-0389, HTD-0390, HTD-0391, HTD-0392, HTD-0393... |
| `.agents/how-to/how-to-document.md` | canonical documentation location, HOW_THIS_WORKS standard, evidence truthfulness | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-dogfooding.md` | first-party capability reuse, public boundary dogfooding, storage/queue/messaging/internal reuse | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-events-listeners-event-sourcing-cqrs-realtime.md` | events/listeners/CQRS/realtime vocabulary and placement | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-git.md` | commit discipline, no unrelated staging, security must scream, gate self-tests | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-modern-php-attributes-di.md` | modern PHP feature use, attribute validation, DI/autowiring discipline, constructor pressure | YES | FAIL | DR-0390, DR-0391, DR-0393, DR-0395 | Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency. | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0389, HTD-0390, HTD-0392, HTD-0394 |
| `.agents/how-to/how-to-production-readiness.md` | readiness color rules, validation agreement, no optimistic readiness claims | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-runtime-composition.md` | no runtime composition leaks; no hidden fallback construction; boot/run separation | YES | FAIL | DR-0390, DR-0391, DR-0393, DR-0395 | Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency. | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0389, HTD-0390, HTD-0392, HTD-0394 |
| `.agents/how-to/how-to-system-performance.md` | no hidden I/O, bounded work, hot path proof, memory discipline | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-system-security.md` | deny by default, no secret exposure, secure defaults, negative tests for security-sensitive behavior | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-unit-test.md` | behavior proof, failure/edge tests, public behavior tests, no smoke-only tests | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-use-advanced-architecture-patterns.md` | no pattern-name dumping grounds; explicit decision rules for advanced patterns | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-write-avax.md` | local AvaX writing, naming, runtime, evidence, and testing rules | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |

## 4. Exact Deviation Findings

### How-To Deviation: PublicSurface directly instantiates collaborators.

- Deviation ID: HTD-0388
- Review unit: `framework/System/PublicSurface`
- Governance source: how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4
- Required rule: public API boundary must satisfy the cited how-to contract.
- Observed gap: PublicSurface directly instantiates collaborators.
- Where it fails: `framework/System/PublicSurface/App.php:207,211,254,271,272,274,275,285...`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: remove
- Suggested fix direction: Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only.
- Severity: HIGH
- Evidence: original finding `DR-0389`; gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0388

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0389
- Review unit: `framework/System/PublicSurface`
- Governance source: how-to-dependency-injection.md §3.4, §6.9
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `framework/System/PublicSurface/Avax.php:72`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Move default creation to approved configuration/boot context.
- Severity: HIGH
- Evidence: original finding `DR-0390`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0389

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0390
- Review unit: `framework/System/PublicSurface`
- Governance source: how-to-dependency-injection.md §3.4, §6.9
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `framework/System/PublicSurface/Avax.php:74`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Move default creation to approved configuration/boot context.
- Severity: HIGH
- Evidence: original finding `DR-0391`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0390

### How-To Deviation: PublicSurface directly instantiates collaborators.

- Deviation ID: HTD-0391
- Review unit: `framework/System/PublicSurface`
- Governance source: how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4
- Required rule: public API boundary must satisfy the cited how-to contract.
- Observed gap: PublicSurface directly instantiates collaborators.
- Where it fails: `framework/System/PublicSurface/Avax.php:72,74,75,76,77,78,79,80...`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: remove
- Suggested fix direction: Move collaborator creation behind framework configuration.
- Severity: HIGH
- Evidence: original finding `DR-0392`; gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0391

### How-To Deviation: Null-coalescing fallback instantiates a dependency.

- Deviation ID: HTD-0392
- Review unit: `framework/System/PublicSurface`
- Governance source: how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Null-coalescing fallback instantiates a dependency.
- Where it fails: `framework/System/PublicSurface/BootDsl.php:150`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Make the dependency explicit and fail during boot/verification.
- Severity: HIGH
- Evidence: original finding `DR-0393`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0392

### How-To Deviation: PublicSurface directly instantiates collaborators.

- Deviation ID: HTD-0393
- Review unit: `framework/System/PublicSurface`
- Governance source: how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4
- Required rule: public API boundary must satisfy the cited how-to contract.
- Observed gap: PublicSurface directly instantiates collaborators.
- Where it fails: `framework/System/PublicSurface/BootDsl.php:55,59,150,151,155,156,159,164`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: remove
- Suggested fix direction: Move Boot DSL assembly behind framework configuration without changing public API.
- Severity: HIGH
- Evidence: original finding `DR-0394`; gate `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0393

### How-To Deviation: Hidden fallback construction in public DSL.

- Deviation ID: HTD-0394
- Review unit: `framework/System/PublicSurface`
- Governance source: how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Hidden fallback construction in public DSL.
- Where it fails: `framework/System/PublicSurface/BootDsl.php:150`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Replace fallback with injected/default boot dependency.
- Severity: HIGH
- Evidence: original finding `DR-0395`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0394

### How-To Deviation: PublicSurface file is 366 lines (>150).

- Deviation ID: HTD-0637
- Review unit: `framework/System/PublicSurface`
- Governance source: how-to-code-review.md §21; how-to-design-components.md §6.2
- Required rule: public API boundary must satisfy the cited how-to contract.
- Observed gap: PublicSurface file is 366 lines (>150).
- Where it fails: `framework/System/PublicSurface/App.php`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: remove
- Suggested fix direction: Classify behavior leaks and move internal behavior behind flows/capabilities.
- Severity: HIGH
- Evidence: original finding `DR-0638`; gate `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0637

### How-To Deviation: PublicSurface file exceeds review threshold.

- Deviation ID: HTD-0638
- Review unit: `framework/System/PublicSurface`
- Governance source: how-to-code-review.md §21; how-to-design-components.md §6.2
- Required rule: public API boundary must satisfy the cited how-to contract.
- Observed gap: PublicSurface file exceeds review threshold.
- Where it fails: `framework/System/PublicSurface/Avax.php`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: remove
- Suggested fix direction: Classify delegate vs behavior methods.
- Severity: MEDIUM
- Evidence: original finding `DR-0639`; gate `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0638

### How-To Deviation: PublicSurface file exceeds review threshold.

- Deviation ID: HTD-0639
- Review unit: `framework/System/PublicSurface`
- Governance source: how-to-code-review.md §21; how-to-design-components.md §6.2
- Required rule: public API boundary must satisfy the cited how-to contract.
- Observed gap: PublicSurface file exceeds review threshold.
- Where it fails: `framework/System/PublicSurface/BootDsl.php`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: remove
- Suggested fix direction: Classify delegate vs behavior methods.
- Severity: MEDIUM
- Evidence: original finding `DR-0640`; gate `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0639

## 5. Deviations Summary

- Total deviations: 10
- BLOCKER count: 0
- HIGH count: 8
- MEDIUM count: 2
- LOW count: 0
- Blocked/unverifiable rules: none at file-read level; semantic proof is bounded by evidence above.
- Highest severity: HIGH
- Decision: **HAS_HIGH_DEVIATIONS**
