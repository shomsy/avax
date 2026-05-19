# Component Review: components/Identity/ExternalIdentity

Generated: 2026-05-19T22:16:56+02:00

## 1. Component Identity

- Component path: `components/Identity/ExternalIdentity`
- Parent group: `components/Identity`
- Purpose inferred from code: Owns ExternalIdentity, ExternalIdentityCapabilityUnavailable, AuthorizationRequest, AuthorizationCodeRecord, AuthorizationCodeStoreInterface, InMemoryAuthorizationCodeStore, InMemoryOAuthClientRegistry, IssuedAuthorizationCode and related behavior.
- Public API surface: `components/Identity/ExternalIdentity/System/PublicSurface/ExternalIdentity.php`
- Internal ownership model: System folders present are PublicSurface, Flows, Capabilities, Configuration, Foundation.
- Main flows: LinkExternalIdentity, ResolveExternalIdentity
- Main capabilities: Capabilities, Capabilities, Authorization, Elements, Elements, Elements, Elements, Elements, Elements, Elements, Elements, Elements, Elements, Elements, Elements, Elements, Elements, Elements, Elements, SenderConstraint, SenderConstraint, SenderConstraint, SenderConstraint, OAuth, ApproveClientRegistration, ApproveClientRegistration, AuthorizeCode, AuthorizeCode, CleanupExpiredAuthorizationCodes, 
- Configuration owners: `components/Identity/ExternalIdentity/System/Configuration/ExternalIdentityConfiguration.php`, `components/Identity/ExternalIdentity/System/Configuration/ExternalIdentityServiceProvider.php`
- Dependencies on other components: Identity/ExternalIdentity (215), Identity/Auth (111), Identity/Tokens (13), Security/Hashing (2), Identity/Access (2), Application/Container (2)

## 2. Structure Review

Checks canonical System shape, flow/capability language, forbidden buckets, duplicate ownership, and noisy subfolders.

Review result: No finding in this slice from the current scan.

## 3. PublicSurface Review

Checks that public API stays small, delegates, avoids mutable/request-scoped state, and does not leak internals.

Review result: No finding in this slice from the current scan.

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
| DR-0355 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/UpdateClient/UpdateClient.php:31` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0356 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/ReadWorkloadIdentities/ReadWorkloadIdentities.php:25` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0573 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/OAuth.php:41` | Constructor has 14 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0574 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/OidcProviderMetadata.php:21` | Constructor has 20 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0575 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/OpenSslOidcProvider.php:26` | Constructor has 12 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0581 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/PushAuthorizationRequest/PushAuthorizationRequestData.php:18` | Constructor has 12 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0584 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/OAuthTokenGrant.php:24` | Constructor has 13 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0585 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Elements/OAuthClient.php:48` | Constructor has 23 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0586 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Elements/AuthorizationCodeRecord.php:19` | Constructor has 13 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0590 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/IntrospectToken/TokenIntrospection.php:20` | Constructor has 13 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0591 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/UpdateClient/UpdateClientData.php:46` | Constructor has 19 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0594 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/RegisterClient/RegisterClientData.php:46` | Constructor has 18 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0597 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/SingleSignOn/Federation/FederationConnection.php:22` | Constructor has 17 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0598 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/SingleSignOn/FederationRuntime/CompleteFederatedLogin/CompleteFederatedLogin.php:36` | Constructor has 14 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0572 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/SingleSignOn/SingleSignOn.php:31` | Constructor has 10 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0576 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/OpenSslOidcProvider.php` | Class/file is 310 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0577 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/OidcRequestObject.php:16` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0578 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/BackChannelLogout/BackChannelLogout.php:23` | Constructor has 9 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0579 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/ValidateRequestObject/ValidatedRequestObject.php:18` | Constructor has 9 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0580 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/PushAuthorizationRequest/PushAuthorizationRequest.php` | Class/file is 378 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0582 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/PushAuthorizationRequest/PushedAuthorizationRequest.php:19` | Constructor has 10 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0583 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/FrontChannelLogout/FrontChannelLogout.php:23` | Constructor has 9 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0587 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Elements/InMemoryOAuthClientRegistry.php` | Class/file is 405 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0588 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/ExchangeAuthorizationCode/ExchangeAuthorizationCode.php:31` | Constructor has 10 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0589 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/ExchangeAuthorizationCode/ExchangeAuthorizationCodeData.php:12` | Constructor has 9 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0592 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/AuthorizeCode/AuthorizeCode.php:30` | Constructor has 9 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0593 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/AuthorizeCode/AuthorizeCodeData.php:18` | Constructor has 11 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0595 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/ExchangeClientCredentials/ExchangeClientCredentialsData.php:18` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0596 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/ExchangeRefreshToken/ExchangeRefreshToken.php:28` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0599 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/ExternalIdentity/System/Capabilities/SingleSignOn/FederationRuntime/RegisterConnection/RegisterFederationConnectionData.php:19` | Constructor has 9 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |

## 15. Component Decision

Decision: **NEEDS_HIGH_REMEDIATION**

Reason: highest severity is HIGH with 30 finding(s); tests detected: yes. This is review evidence only and is not a GREEN production-readiness claim.
