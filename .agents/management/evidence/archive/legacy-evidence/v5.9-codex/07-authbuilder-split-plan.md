# AuthBuilder Split Plan

Date: 2026-05-16
Stage: V5.9 Governance Baseline Classification
Status: PLAN_ONLY

## 1. Current Unit

| Field | Value |
|---|---|
| Path | `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php` |
| Line count | 1730 by `wc -l`; 1731 by large-unit gate newline count |
| Public methods | 46 |
| Private methods | 1 |
| Constructor dependencies | 0 |
| Mutable configuration properties | 40+ |
| Main risk method | `ready()` from line 706 to line 1710 |
| Current tests | `tests/Unit/Components/Identity/Auth/AuthCapabilitiesTest.php`, `tests/Unit/Components/Identity/Auth/AuthFoundationSmokeTest.php` |
| Missing tests | AuthBuilder ready graph, container defaults, capability gating, OAuth/OIDC, passkey, MFA, SCIM, tenancy, diagnostics, and failure messages |

## 2. Split Target

Target direction: reduce one 1730-line security-sensitive builder into 5 to 7 explicit configuration owners, each under
300 lines where practical, through phased extraction. No generic `Manager`, `Service`, `Helper`, or `Handler` names.

The builder should become a small fluent configuration surface that delegates assembly to focused configuration-time
builders.

## 3. Responsibility Table

| Responsibility | Current methods/lines | New owner candidate | Risk | First slice? |
|---|---|---|---|---:|
| Container default dependency resolution | `withContainer()` lines 665-704 | `ApplyAuthContainerDefaults` | Low/medium: defaults must preserve explicit user overrides and named throttle bindings | YES |
| Readiness/capability request calculation | `ready()` lines 706-724, `capabilityRequests()` lines 1713-1728 | `ResolveAuthReadiness` | Medium: failure timing and capability requirements must remain exact | YES, after defaults |
| Credential/password/account/recovery assembly | setters around lines 335-488, account/recovery/register assembly around lines 1085-1189 | `AssemblePasswordAuthentication` | Medium/high: password hashing, resets, sessions, and audit are security-sensitive | NO |
| Session/current-authentication assembly | session registry fields, current auth, sessions/login/logout/change-password lines 754-792 and 1062-1121 | `AssembleSessionAuthentication` | High: long-lived state and session revocation behavior must not regress | NO |
| MFA assembly | MFA setters lines 418-650, MFA graph lines 1191-1263 | `AssembleMfaAuthentication` | High: MFA recovery/challenge behavior is security-sensitive | NO |
| Passkey assembly | passkey setters lines 537-558, passkey graph lines 1265-1360 | `AssemblePasskeyAuthentication` | High: phishing-resistant auth and relying-party settings must stay exact | NO |
| OAuth/OIDC/federation assembly | external identity setters lines 502-608, OAuth/OIDC/federation graph lines 795-893 and 1374-1578 | `AssembleExternalIdentityAuthentication` | High: token, client, OIDC, and federation behavior is security-sensitive | NO |
| SCIM/provisioning/identity-sync assembly | SCIM/lifecycle setters lines 516-615, SCIM/provisioning graph lines 1016-1049 and 1581-1675 | `AssembleIdentitySyncAuthentication` | High: provisioning and lifecycle effects must be carefully tested | NO |
| Tenant/admin/security assembly | tenant/admin setters lines 523-636, tenant security graph lines 945-1009 and 1678-1702 | `AssembleTenantAuthentication` | High: tenant isolation and admin elevation are security-sensitive | NO |
| Final Auth aggregate creation | final graph lines 1362-1710 | `AssembleAuthRuntime` | High: must preserve exact public `Auth` capability graph | NO |

## 4. First Safe Extraction Slice

First slice: extract `withContainer()` default binding resolution into `ApplyAuthContainerDefaults`.

Why this slice first:

- It is configuration-time only.
- It is bounded to lines 665-704.
- It does not require changing public fluent method names.
- It can be tested by asserting explicit overrides win and missing required container bindings fail before `ready()`.
- It reduces hidden default wiring inside the large builder without splitting the full runtime graph yet.

Expected first-slice files:

| Path | Purpose |
|---|---|
| `components/Identity/Auth/System/Configuration/Builders/ApplyAuthContainerDefaults.php` | Applies default dependency bindings from the root container into a small mutable AuthBuilder state object or directly through a narrow callback contract. |
| `tests/Unit/Components/Identity/Auth/AuthBuilderContainerDefaultsTest.php` | Proves default resolution, explicit override precedence, named throttle binding behavior, and clear failure when a required default is missing. |

Important design constraint: the first slice must not introduce a generic state bag. If shared state is needed, name it
for the exact responsibility, such as `AuthBuilderOptions`, and keep it internal to `Configuration/Builders`.

## 5. Test Coverage Required Before First Slice Commit

| Behavior | Required proof |
|---|---|
| `withContainer()` keeps explicit fluent overrides | Unit test with custom dependency set before container application. |
| Missing default binding fails clearly | Unit test expecting `ConfigurationException` or container failure at configuration/build time. |
| Named throttle bindings are preserved | Unit test for `auth.throttle.password_reset`, `auth.throttle.mfa_recovery`, `auth.throttle.scim`. |
| `DefaultAuth::configuration($container)` still returns configured builder | Unit test around existing public path. |
| `RegisterAuthDependencies` still resolves `AuthInterface` | Focused provider/container test. |
| Existing auth smoke tests still pass | Existing unit tests. |

## 6. Rollback Strategy

If the first slice regresses behavior:

1. Revert only the new `ApplyAuthContainerDefaults` extraction and related tests.
2. Restore `withContainer()` body exactly from current `AuthBuilder.php`.
3. Rerun focused auth tests, semantic PHPDoc gate, large-unit gate, PHPStan, and full PHPUnit if public behavior changed.
4. Do not proceed to deeper assembly extraction until first-slice behavior is proven.

## 7. Validation Commands For First Slice

```bash
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit tests/Unit/Components/Identity/Auth --no-coverage
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse components/Identity/Auth tests/Unit/Components/Identity/Auth --memory-limit=1G
php tooling/governance/check-semantic-phpdoc.php
php tooling/governance/check-large-unit-thresholds.php
php tooling/refactor/check-runtime-composition-leaks.php
php tooling/components/check-component-runtime-assembly.php
php tooling/governance/check-security-commit-block-readiness.php
```

## 8. Stop Conditions

- Any auth/security behavior changes without focused tests.
- Any direct runtime fallback or hidden `?? new` reintroduced.
- Any public API rename.
- Any generic owner names such as `AuthBuilderManager`, `AuthBuilderService`, `AuthBuilderHelper`, or `AuthBuilderHandler`.
- Any partial split leaving `ready()` broken or less testable.
