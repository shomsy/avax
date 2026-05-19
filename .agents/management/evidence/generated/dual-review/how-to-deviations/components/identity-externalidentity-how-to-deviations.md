# How-To Deviations: components/Identity/ExternalIdentity

Generated: 2026-05-19T23:31:00+02:00

## 1. Review Unit Identity

- Review unit ID: RUC-051
- Root type: COMPONENT
- Path: `components/Identity/ExternalIdentity`
- Purpose inferred from code: `components/Identity/ExternalIdentity` owns the discovered runtime or user action flow boundary.
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
| `.agents/how-to/how-to-clean-code.md` | correctness, readability, simplicity, cohesive units, meaningful names | YES | FAIL | DR-0576, DR-0580, DR-0587 | Class/file is 310 lines (>300).; Class/file is 378 lines (>300). | Address concrete deviations listed below and rerun named gates. | MEDIUM | HTD-0575, HTD-0579, HTD-0586 |
| `.agents/how-to/how-to-code-review.md` | strict review process; hard gates; symptom/root-cause/impact/evidence/risk findings | YES | FAIL | DR-0355, DR-0356, DR-0572, DR-0573, DR-0574, DR-0575... | Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency. | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0354, HTD-0355, HTD-0571, HTD-0572, HTD-0573, HTD-0574... |
| `.agents/how-to/how-to-code-style.md` | constructor promotion, nullable type format, return types, named argument discipline | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-coding-standards.md` | modern PHP, strict types, architecture and naming standards | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-dependency-injection.md` | constructor injection, service provider ownership, no service locator, fail-fast dependencies | YES | FAIL | DR-0355, DR-0356 | Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency. | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0354, HTD-0355 |
| `.agents/how-to/how-to-design-components.md` | canonical component shape; PublicSurface/Flows/Capabilities/Configuration/Foundation roles | YES | FAIL | DR-0355, DR-0356 | Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency. | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0354, HTD-0355 |
| `.agents/how-to/how-to-document.md` | canonical documentation location, HOW_THIS_WORKS standard, evidence truthfulness | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-dogfooding.md` | first-party capability reuse, public boundary dogfooding, storage/queue/messaging/internal reuse | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-events-listeners-event-sourcing-cqrs-realtime.md` | events/listeners/CQRS/realtime vocabulary and placement | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-git.md` | commit discipline, no unrelated staging, security must scream, gate self-tests | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-modern-php-attributes-di.md` | modern PHP feature use, attribute validation, DI/autowiring discipline, constructor pressure | YES | FAIL | DR-0355, DR-0356, DR-0572, DR-0573, DR-0574, DR-0575... | Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency. | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0354, HTD-0355, HTD-0571, HTD-0572, HTD-0573, HTD-0574... |
| `.agents/how-to/how-to-production-readiness.md` | readiness color rules, validation agreement, no optimistic readiness claims | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-runtime-composition.md` | no runtime composition leaks; no hidden fallback construction; boot/run separation | YES | FAIL | DR-0355, DR-0356 | Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency. | Address concrete deviations listed below and rerun named gates. | HIGH | HTD-0354, HTD-0355 |
| `.agents/how-to/how-to-system-performance.md` | no hidden I/O, bounded work, hot path proof, memory discipline | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-system-security.md` | deny by default, no secret exposure, secure defaults, negative tests for security-sensitive behavior | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-unit-test.md` | behavior proof, failure/edge tests, public behavior tests, no smoke-only tests | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-use-advanced-architecture-patterns.md` | no pattern-name dumping grounds; explicit decision rules for advanced patterns | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |
| `.agents/how-to/how-to-write-avax.md` | local AvaX writing, naming, runtime, evidence, and testing rules | YES | PASS | no matching concrete finding in this pass | No exact deviation recorded in current evidence; semantic proof remains bounded by available review artifacts. | Keep under ratchet; do not claim GREEN without fresh validation. | LOW | - |

## 4. Exact Deviation Findings

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0354
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/UpdateClient/UpdateClient.php:31`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: HIGH
- Evidence: original finding `DR-0355`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0354

### How-To Deviation: Constructor default parameter instantiates a dependency.

- Deviation ID: HTD-0355
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule
- Required rule: dependency and runtime composition must satisfy the cited how-to contract.
- Observed gap: Constructor default parameter instantiates a dependency.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/ReadWorkloadIdentities/ReadWorkloadIdentities.php:25`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: simplify
- Suggested fix direction: Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory.
- Severity: HIGH
- Evidence: original finding `DR-0356`; gate `php tooling/refactor/check-direct-instantiation.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0355

### How-To Deviation: Constructor has 10 parameters.

- Deviation ID: HTD-0571
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 10 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/SingleSignOn/SingleSignOn.php:31`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0572`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0571

### How-To Deviation: Constructor has 14 parameters.

- Deviation ID: HTD-0572
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 14 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/OAuth.php:41`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: HIGH
- Evidence: original finding `DR-0573`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0572

### How-To Deviation: Constructor has 20 parameters.

- Deviation ID: HTD-0573
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 20 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/OidcProviderMetadata.php:21`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: HIGH
- Evidence: original finding `DR-0574`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0573

### How-To Deviation: Constructor has 12 parameters.

- Deviation ID: HTD-0574
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 12 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/OpenSslOidcProvider.php:26`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: HIGH
- Evidence: original finding `DR-0575`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0574

### How-To Deviation: Class/file is 310 lines (>300).

- Deviation ID: HTD-0575
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-code-review.md §21; how-to-clean-code.md module design
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Class/file is 310 lines (>300).
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/OpenSslOidcProvider.php`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Severity: MEDIUM
- Evidence: original finding `DR-0576`; gate `php tooling/governance/check-large-unit-thresholds.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0575

### How-To Deviation: Constructor has 8 parameters.

- Deviation ID: HTD-0576
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 8 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/OidcRequestObject.php:16`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0577`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0576

### How-To Deviation: Constructor has 9 parameters.

- Deviation ID: HTD-0577
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 9 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/BackChannelLogout/BackChannelLogout.php:23`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0578`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0577

### How-To Deviation: Constructor has 9 parameters.

- Deviation ID: HTD-0578
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 9 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/ValidateRequestObject/ValidatedRequestObject.php:18`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0579`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0578

### How-To Deviation: Class/file is 378 lines (>300).

- Deviation ID: HTD-0579
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-code-review.md §21; how-to-clean-code.md module design
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Class/file is 378 lines (>300).
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/PushAuthorizationRequest/PushAuthorizationRequest.php`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Severity: MEDIUM
- Evidence: original finding `DR-0580`; gate `php tooling/governance/check-large-unit-thresholds.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0579

### How-To Deviation: Constructor has 12 parameters.

- Deviation ID: HTD-0580
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 12 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/PushAuthorizationRequest/PushAuthorizationRequestData.php:18`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: HIGH
- Evidence: original finding `DR-0581`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0580

### How-To Deviation: Constructor has 10 parameters.

- Deviation ID: HTD-0581
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 10 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/PushAuthorizationRequest/PushedAuthorizationRequest.php:19`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0582`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0581

### How-To Deviation: Constructor has 9 parameters.

- Deviation ID: HTD-0582
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 9 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/FrontChannelLogout/FrontChannelLogout.php:23`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0583`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0582

### How-To Deviation: Constructor has 13 parameters.

- Deviation ID: HTD-0583
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 13 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/OAuthTokenGrant.php:24`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: HIGH
- Evidence: original finding `DR-0584`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0583

### How-To Deviation: Constructor has 23 parameters.

- Deviation ID: HTD-0584
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 23 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Elements/OAuthClient.php:48`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: HIGH
- Evidence: original finding `DR-0585`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0584

### How-To Deviation: Constructor has 13 parameters.

- Deviation ID: HTD-0585
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 13 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Elements/AuthorizationCodeRecord.php:19`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: HIGH
- Evidence: original finding `DR-0586`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0585

### How-To Deviation: Class/file is 405 lines (>300).

- Deviation ID: HTD-0586
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-code-review.md §21; how-to-clean-code.md module design
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Class/file is 405 lines (>300).
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Elements/InMemoryOAuthClientRegistry.php`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior.
- Severity: MEDIUM
- Evidence: original finding `DR-0587`; gate `php tooling/governance/check-large-unit-thresholds.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0586

### How-To Deviation: Constructor has 10 parameters.

- Deviation ID: HTD-0587
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 10 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/ExchangeAuthorizationCode/ExchangeAuthorizationCode.php:31`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0588`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0587

### How-To Deviation: Constructor has 9 parameters.

- Deviation ID: HTD-0588
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 9 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/ExchangeAuthorizationCode/ExchangeAuthorizationCodeData.php:12`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0589`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0588

### How-To Deviation: Constructor has 13 parameters.

- Deviation ID: HTD-0589
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 13 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/IntrospectToken/TokenIntrospection.php:20`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: HIGH
- Evidence: original finding `DR-0590`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0589

### How-To Deviation: Constructor has 19 parameters.

- Deviation ID: HTD-0590
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 19 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/UpdateClient/UpdateClientData.php:46`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: HIGH
- Evidence: original finding `DR-0591`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0590

### How-To Deviation: Constructor has 9 parameters.

- Deviation ID: HTD-0591
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 9 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/AuthorizeCode/AuthorizeCode.php:30`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0592`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0591

### How-To Deviation: Constructor has 11 parameters.

- Deviation ID: HTD-0592
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 11 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/AuthorizeCode/AuthorizeCodeData.php:18`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0593`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0592

### How-To Deviation: Constructor has 18 parameters.

- Deviation ID: HTD-0593
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 18 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/RegisterClient/RegisterClientData.php:46`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: HIGH
- Evidence: original finding `DR-0594`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0593

### How-To Deviation: Constructor has 8 parameters.

- Deviation ID: HTD-0594
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 8 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/ExchangeClientCredentials/ExchangeClientCredentialsData.php:18`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0595`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0594

### How-To Deviation: Constructor has 8 parameters.

- Deviation ID: HTD-0595
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 8 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/ExchangeRefreshToken/ExchangeRefreshToken.php:28`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0596`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0595

### How-To Deviation: Constructor has 17 parameters.

- Deviation ID: HTD-0596
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 17 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/SingleSignOn/Federation/FederationConnection.php:22`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: HIGH
- Evidence: original finding `DR-0597`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0596

### How-To Deviation: Constructor has 14 parameters.

- Deviation ID: HTD-0597
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 14 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/SingleSignOn/FederationRuntime/CompleteFederatedLogin/CompleteFederatedLogin.php:36`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: HIGH
- Evidence: original finding `DR-0598`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0597

### How-To Deviation: Constructor has 9 parameters.

- Deviation ID: HTD-0598
- Review unit: `components/Identity/ExternalIdentity`
- Governance source: how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21
- Required rule: maintainability and complexity must satisfy the cited how-to contract.
- Observed gap: Constructor has 9 parameters.
- Where it fails: `components/Identity/ExternalIdentity/System/Capabilities/SingleSignOn/FederationRuntime/RegisterConnection/RegisterFederationConnectionData.php:19`
- Why it matters: The unit cannot be treated as compliant with the cited how-to until the mandatory rule is satisfied and proven.
- Required action: split
- Suggested fix direction: Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity.
- Severity: MEDIUM
- Evidence: original finding `DR-0599`; gate `php tooling/refactor/check-constructor-bloat.php`.
- Blocks GREEN: YES
- Related strict code review finding IDs if known: SCR-0598

## 5. Deviations Summary

- Total deviations: 30
- BLOCKER count: 0
- HIGH count: 14
- MEDIUM count: 16
- LOW count: 0
- Blocked/unverifiable rules: none at file-read level; semantic proof is bounded by evidence above.
- Highest severity: HIGH
- Decision: **HAS_HIGH_DEVIATIONS**
