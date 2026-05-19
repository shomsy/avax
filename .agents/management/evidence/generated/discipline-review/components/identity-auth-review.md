# Component Review: components/Identity/Auth

Generated: 2026-05-19T22:16:56+02:00

## 1. Component Identity

- Component path: `components/Identity/Auth`
- Parent group: `components/Identity`
- Purpose inferred from code: Owns AuditEvent, AuditExporterInterface, AuditLogInterface, CorrelatingAuditLog, DrainAuditLogInterface, ExportAuditEvents, InMemoryAuditLog, NullAuditLog and related behavior.
- Public API surface: `components/Identity/Auth/System/PublicSurface/Auth.php`, `components/Identity/Auth/System/PublicSurface/AuthInterface.php`, `components/Identity/Auth/System/PublicSurface/User.php`, `components/Identity/Auth/System/PublicSurface/UserRecord.php`, `components/Identity/Auth/System/PublicSurface/shortcuts.php`
- Internal ownership model: System folders present are PublicSurface, Flows, Capabilities, Configuration, Foundation.
- Main flows: ChangeEmail, ChangeEmail, ChangeEmail, ChangeEmail, ChangeEmail, ChangeEmail, ChangeEmail, ChangeEmail, ChangeEmail, ChangePassword, ChangePassword, ChangePassword, AuthenticateRequest, AuthenticateRequest, AuthenticateRequest, AuthenticateRequest, AuthenticateRequest, AuthenticateRequest, AuthenticateRequest, CheckAuthentication, ReadCurrentUser, Login, Login, Login, Login, Login, Login, RateLimi
- Main capabilities: Audit, Audit, Audit, Audit, Audit, ExportAuditEvents, Audit, Audit, AuthDiagnostics, AuthDiagnostics, Explainability, Explainability, Authentication, Identity, Identity, Identity, IdentityOwners, IdentityOwners, IdentityOwners, IdentityOwners, Identity, Jwt, Jwt, Session, Session, Session, Sessions, Registry, Registry, Registry, Registry, Registry, Registry, Registry, Runtime, CleanupExpiredSessio
- Configuration owners: `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthExternalIdentityGraph.php`, `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php`, `components/Identity/Auth/System/Configuration/AuthConfiguration.php`, `components/Identity/Auth/System/Configuration/AuthServiceProvider.php`, `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php`, `components/Identity/Auth/System/Configuration/Builders/RegisterAuthDefaults.php`, `components/Identity/Auth/System/Configuration/Builders/RegisterAuthDependencies.php`, `components/Identity/Auth/System/Configuration/Readiness/AuthBootstrapValidator.php`, `components/Identity/Auth/System/Configuration/Readiness/AuthCapabilityReadiness.php`, `components/Identity/Auth/System/Configuration/Readines
- Dependencies on other components: Identity/Auth (604), Identity/ExternalIdentity (106), Identity/Credentials (96), Identity/Tenancy (79), Identity/Access (41), Identity/Tokens (35), Security/Hashing (14), Application/Container (6)

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
| `how-to-design-components.md` | canonical System shape, PublicSurface delegates, Configuration assembles | Fail | structure + DI scans | see finding table | BLOCKER |
| `how-to-modern-php-attributes-di.md` | constructor bloat and DI discipline | Fail | constructor scan | see finding table | BLOCKER |
| `how-to-system-security.md` | path/I/O/security-sensitive boundaries | Pass | raw file/security scan | keep | - |
| `how-to-system-performance.md` | hot-path/memory/large object graph risks | Pass | performance scan | keep | - |
| `how-to-unit-test.md` | behavior proof exists | Pass | test discovery | keep | - |
| `how-to-document.md` | semantic PHPDoc/evidence truthfulness | Pass | PHPDoc/evidence checks | keep | - |
| `how-to-dogfooding.md` | first-party capabilities reused at boundaries | Fail | raw I/O + DI scans | see finding table | BLOCKER |

## 14. Finding Table

| ID | Severity | Rule source | Exact file/path | Exact issue | Risk type | Recommended fix | Safe remediation batch | Validation command | Cross-component | fix-this candidate |
|---|---|---|---|---|---|---|---|---|---:|---:|
| DR-0603 | BLOCKER | `how-to-code-review.md §21; how-to-design-components.md §6.5.1` | `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php` | Configuration builder is 797 lines (>300). | CONFIGURATION_DI | Split assembly by exact responsibility without changing public API or runtime behavior. | Large builder split/classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0370 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/Identity/Auth/System/Flows/Register/CreateRegisteredUser.php:26` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0373 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/DeprovisionUser/DeprovisionUser.php:38` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0374 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/SuspendUser/SuspendUser.php:38` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0375 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/ReactivateUser/ReactivateUser.php:23` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0376 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ReadGroups/ReadScimGroups.php:22` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0377 | HIGH | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ReadUsers/ReadScimUsers.php:30` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0600 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthExternalIdentityGraph.php:92` | Constructor has 27 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0601 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:136` | Constructor has 43 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0604 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Flows/ChangePassword/ChangePassword.php:34` | Constructor has 12 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0610 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Flows/CheckAuthentication/AuthenticateRequest/AuthenticationContext.php:16` | Constructor has 12 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0614 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/SCIM.php:38` | Constructor has 12 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0615 | HIGH | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Directories/ScimDirectory.php:17` | Constructor has 13 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0357 | MEDIUM | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/Identity/Auth/System/PublicSurface/UserRecord.php:19` | PublicSurface directly instantiates collaborators (1 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0358 | MEDIUM | `how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4` | `components/Identity/Auth/System/PublicSurface/User.php:19` | PublicSurface directly instantiates collaborators (1 `new` expressions detected). | PUBLIC_API | Move collaborator creation to Configuration/ServiceProvider/builder and keep PublicSurface delegation-only. | PublicSurface assembly cleanup batch | `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php` | NO | YES |
| DR-0359 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/Identity/Auth/System/Foundation/Time/Expiry.php:16` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0360 | MEDIUM | `how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule` | `components/Identity/Auth/System/Foundation/Time/Expiry.php:16` | Null-coalescing fallback instantiates a dependency. | CONFIGURATION_DI | Make dependency explicit and fail during boot/verification, or move fallback to approved composition context. | Hidden fallback construction cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0361 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/Identity/Auth/System/Foundation/Time/Expiry.php:23` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0362 | MEDIUM | `how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule` | `components/Identity/Auth/System/Foundation/Time/Expiry.php:23` | Null-coalescing fallback instantiates a dependency. | CONFIGURATION_DI | Make dependency explicit and fail during boot/verification, or move fallback to approved composition context. | Hidden fallback construction cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0363 | MEDIUM | `how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule` | `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:203` | Null-coalescing fallback instantiates a dependency. | CONFIGURATION_DI | Make dependency explicit and fail during boot/verification, or move fallback to approved composition context. | Hidden fallback construction cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0364 | MEDIUM | `how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule` | `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:204` | Null-coalescing fallback instantiates a dependency. | CONFIGURATION_DI | Make dependency explicit and fail during boot/verification, or move fallback to approved composition context. | Hidden fallback construction cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0365 | MEDIUM | `how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule` | `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:208` | Null-coalescing fallback instantiates a dependency. | CONFIGURATION_DI | Make dependency explicit and fail during boot/verification, or move fallback to approved composition context. | Hidden fallback construction cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0366 | MEDIUM | `how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule` | `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:209` | Null-coalescing fallback instantiates a dependency. | CONFIGURATION_DI | Make dependency explicit and fail during boot/verification, or move fallback to approved composition context. | Hidden fallback construction cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0367 | MEDIUM | `how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule` | `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:210` | Null-coalescing fallback instantiates a dependency. | CONFIGURATION_DI | Make dependency explicit and fail during boot/verification, or move fallback to approved composition context. | Hidden fallback construction cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0368 | MEDIUM | `how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule` | `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:211` | Null-coalescing fallback instantiates a dependency. | CONFIGURATION_DI | Make dependency explicit and fail during boot/verification, or move fallback to approved composition context. | Hidden fallback construction cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0369 | MEDIUM | `how-to-dependency-injection.md §3.4; how-to-runtime-composition.md fallback construction rule` | `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php:607` | Null-coalescing fallback instantiates a dependency. | CONFIGURATION_DI | Make dependency explicit and fail during boot/verification, or move fallback to approved composition context. | Hidden fallback construction cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0371 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/Identity/Auth/System/Capabilities/SessionIdentity/SessionIdentity.php:27` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0372 | MEDIUM | `how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule` | `components/Identity/Auth/System/Capabilities/Tokens/TokenCodec.php:26` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | Inject required dependencies or move default creation to Configuration/ServiceProvider/approved factory. | Direct default-instantiation cleanup batch | `php tooling/refactor/check-direct-instantiation.php` | NO | YES |
| DR-0602 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php` | Class/file is 678 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0605 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Flows/ChangeEmail/BeginEmailChange.php:23` | Constructor has 9 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0606 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Flows/ChangeEmail/ConfirmEmailChange.php:23` | Constructor has 11 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0607 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Flows/RecoverAccess/PasswordReset/ResetPassword.php:23` | Constructor has 9 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0608 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Flows/CheckAuthentication/AuthenticateRequest/AuthenticateRequest.php:24` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0609 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Flows/CheckAuthentication/AuthenticateRequest/AuthenticatedUser.php:29` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0611 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Capabilities/Identity/Identity.php:39` | Constructor has 10 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0612 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/Identity/Auth/System/Capabilities/Authentication/DefaultAuth.php` | Class/file is 871 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0613 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Capabilities/SessionIdentity/SessionIdentity.php:14` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0616 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/DeprovisionUser/DeprovisionUser.php:22` | Constructor has 9 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0617 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/SuspendUser/SuspendUser.php:22` | Constructor has 9 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0618 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ProvisionUser/ScimProvisioningResult.php:14` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0619 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ProvisionUser/ProvisionScimUserData.php:18` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0620 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ProvisionUser/ProvisionScimUser.php:34` | Constructor has 10 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0621 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ReadUsers/ScimUserProjection.php:16` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0622 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Capabilities/Identity/User/User.php:21` | Constructor has 8 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0623 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Capabilities/Identity/Session/SessionIdentity.php:32` | Constructor has 11 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0624 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/Identity/Auth/System/Capabilities/Identity/Session/SessionIdentity.php` | Class/file is 311 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0625 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Capabilities/Identity/Jwt/JwtIdentity.php:38` | Constructor has 10 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0626 | MEDIUM | `how-to-code-review.md §21; how-to-clean-code.md module design` | `components/Identity/Auth/System/Capabilities/Identity/Jwt/JwtIdentity.php` | Class/file is 390 lines (>300). | MAINTAINABILITY | Classify responsibility; split only where extraction clarifies ownership and tests can protect behavior. | Large unit classification batch | `php tooling/governance/check-large-unit-thresholds.php` | NO | YES |
| DR-0627 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Capabilities/Identity/Sessions/Runtime/ActiveSession.php:17` | Constructor has 11 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |
| DR-0628 | MEDIUM | `how-to-modern-php-attributes-di.md constructor bloat; how-to-code-review.md §21` | `components/Identity/Auth/System/Capabilities/Identity/Sessions/Registry/SessionRecord.php:17` | Constructor has 11 parameters. | MAINTAINABILITY | Split responsibility, introduce exact configuration/value objects only where meaningful, or document why a data carrier needs the arity. | Constructor responsibility review batch | `php tooling/refactor/check-constructor-bloat.php` | NO | YES |

## 15. Component Decision

Decision: **BLOCKED_BY_GOVERNANCE**

Reason: highest severity is BLOCKER with 50 finding(s); tests detected: yes. This is review evidence only and is not a GREEN production-readiness claim.
