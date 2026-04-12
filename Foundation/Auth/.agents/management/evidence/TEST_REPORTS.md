# Test Reports

Concrete records of executed verification.

## Entry Template

- `executed_at`:
- `scope`:
- `environment`:
- `checks`:
- `result`: pass | fail | partial
- `notes`:

## Reports

- `executed_at`: 2026-04-12 23:10 CEST
- `scope`: full PHPUnit suite after assurance policy tiers, sender-constrained OAuth posture, and key-versioned JWT
  artifacts
- `environment`: local CLI PHP 8.4.11 with repo-local `composer.phar` and vendor dependencies installed
- `checks`: `php composer.phar test`
- `result`: pass
- `notes`: PHPUnit passes after adding `IdentityPolicyCatalog`, OAuth sender-constraint enforcement, phishing-resistant
  OAuth client policy, JWT `kid` handling, and new regression coverage; result `OK (138 tests, 355 assertions)`

- `executed_at`: 2026-04-12 23:10 CEST
- `scope`: baseline static analysis after enterprise-to-ideal auth policy expansion
- `environment`: local CLI PHP 8.4.11 with repo-local `composer.phar` and vendor dependencies installed
- `checks`: `php composer.phar analyse`
- `result`: pass
- `notes`: baseline PHPStan passes across the new `Capability/Access/Policy` types, OAuth sender-constraint contracts,
  and JWT token-version support

- `executed_at`: 2026-04-12 23:10 CEST
- `scope`: strict static analysis after enterprise-to-ideal auth policy expansion
- `environment`: local CLI PHP 8.4.11 with repo-local `composer.phar` and vendor dependencies installed
- `checks`: `php composer.phar analyse:strict`
- `result`: pass
- `notes`: strict PHPStan passes after tightening grant-type normalization, sender-constraint iterable contracts, and
  explicit assurance-policy seams

- `executed_at`: 2026-04-12 22:39 CEST
- `scope`: targeted regression suite for phishing-resistant access naming, admin elevation policy, and ChangeEmail flow
- `environment`: local CLI PHP 8.4.11 with repo-local `composer.phar` and vendor dependencies installed
- `checks`: `php composer.phar test` (suite covered `tests/Capabilities/Access/RequirePhishingResistantAuthentication/RequirePhishingResistantAuthenticationTest.php`, `tests/Flows/ChangeEmail/ChangeEmailTest.php`, `tests/Flows/AdminRealm/AdminElevationTest.php`, `tests/Capabilities/Access/AccessPolicyTest.php`, `tests/Capabilities/Access/AccessTest.php`, `tests/Capabilities/Identity/IdentityTest.php`)
- `result`: pass
- `notes`: targeted regression coverage passes for the new phishing-resistant capability guard, admin elevation policy,
  and email-change begin/confirm lifecycle; result `OK (129 tests, 327 assertions)`

- `executed_at`: 2026-04-12 22:39 CEST
- `scope`: baseline static analysis after phishing-resistant naming and ChangeEmail refactor
- `environment`: local CLI PHP 8.4.11 with repo-local `composer.phar` and vendor dependencies installed
- `checks`: `php composer.phar analyse`
- `result`: pass
- `notes`: baseline PHPStan passes after the `RequirePhishingResistantAuthentication` namespace move and email-change
  builder wiring

- `executed_at`: 2026-04-12 22:39 CEST
- `scope`: strict static analysis after phishing-resistant naming and ChangeEmail refactor
- `environment`: local CLI PHP 8.4.11 with repo-local `composer.phar` and vendor dependencies installed
- `checks`: `php composer.phar analyse:strict`
- `result`: pass
- `notes`: strict PHPStan passes after the new `ChangeEmail` flow and admin policy naming cleanup

- `executed_at`: 2026-04-12 21:32 CEST
- `scope`: targeted authorization-policy, passkey rename, maintenance cleanup, and audit export regression suite
- `environment`: local CLI PHP 8.4.11 with vendor dependencies installed
- `checks`: `vendor/bin/phpunit tests/Capabilities/Access/AccessTest.php tests/Capabilities/Access/AccessPolicyTest.php tests/Flows/Passkey/PasskeyFlowTest.php tests/Flows/Session/CleanupExpiredSessionsTest.php tests/Flows/Recover/CleanupExpiredPasswordResetsTest.php tests/Flows/Mfa/CleanupExpiredMfaChallengesTest.php tests/Flows/Passkey/CleanupExpiredPasskeyChallengesTest.php tests/Flows/OAuth/CleanupExpiredAuthorizationCodesTest.php tests/Flows/Diagnostics/ExportAuditEventsTest.php`
- `result`: pass
- `notes`: targeted regression suite passes for composed access-policy enforcement, passkey rename ownership, cleanup
  jobs, and audit export drain behavior; result `OK (10 tests, 27 assertions)`

- `executed_at`: 2026-04-12 21:32 CEST
- `scope`: full PHPUnit suite after authorization-policy, maintenance, and passkey rename expansion
- `environment`: local CLI PHP 8.4.11 with repo-local `composer.phar` and vendor dependencies installed
- `checks`: `php composer.phar test`
- `result`: pass
- `notes`: full PHPUnit suite passes after access-policy wiring, maintenance cleanup/export flows, passkey rename, and
  docs alignment; result `OK (121 tests, 302 assertions)`

- `executed_at`: 2026-04-12 21:32 CEST
- `scope`: baseline static analysis after authorization-policy, maintenance, and passkey rename expansion
- `environment`: local CLI PHP 8.4.11 with repo-local `composer.phar` and vendor dependencies installed
- `checks`: `php composer.phar analyse`
- `result`: pass
- `notes`: baseline PHPStan passes across the new `Capability/Access/Policy`, maintenance flows, and passkey rename
  surface

- `executed_at`: 2026-04-12 21:32 CEST
- `scope`: strict static analysis after authorization-policy, maintenance, and passkey rename expansion
- `environment`: local CLI PHP 8.4.11 with repo-local `composer.phar` and vendor dependencies installed
- `checks`: `php composer.phar analyse:strict`
- `result`: pass
- `notes`: strict PHPStan passes after the new maintenance contracts, access-policy composition, and passkey label
  validation flow

- `executed_at`: 2026-04-12 15:36 CEST
- `scope`: full PHPUnit suite after OAuth/API auth subsystem v1 implementation
- `environment`: local CLI PHP 8.4.11 with repo-local `composer.phar` and vendor dependencies installed
- `checks`: `php composer.phar test`
- `result`: pass
- `notes`: PHPUnit passes after adding `Capability/OAuth`, `Flow/OAuth`, client-bound JWT claims, and new OAuth facade
  methods; result `OK (107 tests, 260 assertions)`

- `executed_at`: 2026-04-12 15:36 CEST
- `scope`: baseline static analysis after OAuth/API auth subsystem v1 implementation
- `environment`: local CLI PHP 8.4.11 with repo-local `composer.phar` and vendor dependencies installed
- `checks`: `php composer.phar analyse`
- `result`: pass
- `notes`: baseline PHPStan passes across the new OAuth capability and flow slices

- `executed_at`: 2026-04-12 15:36 CEST
- `scope`: strict static analysis after OAuth/API auth subsystem v1 implementation
- `environment`: local CLI PHP 8.4.11 with repo-local `composer.phar` and vendor dependencies installed
- `checks`: `php composer.phar analyse:strict`
- `result`: pass
- `notes`: strict PHPStan passes after tightening iterable contracts for client/scope-aware token issuance

- `executed_at`: 2026-04-12 15:36 CEST
- `scope`: targeted OAuth regression suite and JWT claim coverage
- `environment`: local CLI PHP 8.4.11 with vendor dependencies installed
- `checks`: `vendor/bin/phpunit tests/Capabilities/OAuth/InMemoryOAuthClientRegistryTest.php tests/Capabilities/Identity/Jwt/JwtIdentityTest.php tests/Flows/OAuth/OAuthFlowTest.php tests/System/AuthTest.php`
- `result`: pass
- `notes`: targeted OAuth regression suite passes for PKCE enforcement, authorization-code exchange, refresh reuse
  detection, introspection, revoke, and client-claim preservation; result `OK (12 tests, 47 assertions)`

- `executed_at`: 2026-04-12 15:04 CEST
- `scope`: full PHPUnit suite after `REFAKTOR.md` scope artifacts and session subsystem v1 implementation
- `environment`: local CLI PHP 8.4.11 with repo-local `composer.phar` and vendor dependencies installed
- `checks`: `php composer.phar test`
- `result`: pass
- `notes`: full PHPUnit suite passes after adding tracked session registry contracts, session facade flows, and session
  revocation wiring; result `OK (102 tests, 239 assertions)`

- `executed_at`: 2026-04-12 15:04 CEST
- `scope`: baseline static analysis after `REFAKTOR.md` scope artifacts and session subsystem v1 implementation
- `environment`: local CLI PHP 8.4.11 with repo-local `composer.phar` and vendor dependencies installed
- `checks`: `php composer.phar analyse`
- `result`: pass
- `notes`: baseline PHPStan passes across the expanded session capability and new auth facade/session flow surface

- `executed_at`: 2026-04-12 15:04 CEST
- `scope`: strict static analysis after `REFAKTOR.md` scope artifacts and session subsystem v1 implementation
- `environment`: local CLI PHP 8.4.11 with repo-local `composer.phar` and vendor dependencies installed
- `checks`: `php composer.phar analyse:strict`
- `result`: pass
- `notes`: strict PHPStan passes after the session registry, logout-all, and session revoke changes

- `executed_at`: 2026-04-12 15:04 CEST
- `scope`: targeted regression suite for session subsystem v1, password-reset revocation, and updated auth facade flows
- `environment`: local CLI PHP 8.4.11 with vendor dependencies installed
- `checks`: `vendor/bin/phpunit tests/Capabilities/Identity/Session/SessionIdentityTest.php tests/Flows/Login/LoginTest.php tests/Flows/Mfa/Challenge/VerifyMfaChallengeTest.php tests/Flows/Logout/LogoutTest.php tests/Flows/ChangePassword/ChangePasswordTest.php tests/Flows/Recover/BeginPasswordResetTest.php tests/Flows/Recover/ResetPasswordTest.php tests/Flows/Mfa/Recover/MfaRecoveryTest.php tests/Flows/Session/ReadActiveSessionsTest.php tests/Flows/Session/LogoutAllSessionsTest.php tests/Flows/Session/RevokeSessionTest.php tests/System/AuthTest.php`
- `result`: pass
- `notes`: targeted regression suite passes after adding tracked session registry contracts, logout-all, targeted
  session revoke, and reset-driven session/challenge revocation; result `OK (31 tests, 86 assertions)`

- `executed_at`: 2026-04-12 14:25 CEST
- `scope`: full PHPUnit suite after auth-kernel hardening for session lifetime, recovery throttling, and password policy
- `environment`: local CLI PHP 8.4.11 with repo-local `composer.phar` and vendor dependencies installed
- `checks`: `php composer.phar test`
- `result`: pass
- `notes`: PHPUnit passes after the new session lifetime, recovery throttling, and password rehash coverage changes;
  result `OK (97 tests, 219 assertions)`

- `executed_at`: 2026-04-12 14:25 CEST
- `scope`: baseline static analysis after auth-kernel hardening for session lifetime, recovery throttling, and password
  policy
- `environment`: local CLI PHP 8.4.11 with repo-local `composer.phar` and vendor dependencies installed
- `checks`: `php composer.phar analyse`
- `result`: pass
- `notes`: baseline PHPStan passes with the new `Throttle` capability, `SessionLifetime` enforcement, and hasher policy
  changes

- `executed_at`: 2026-04-12 14:25 CEST
- `scope`: strict static analysis after auth-kernel hardening for session lifetime, recovery throttling, and password
  policy
- `environment`: local CLI PHP 8.4.11 with repo-local `composer.phar` and vendor dependencies installed
- `checks`: `php composer.phar analyse:strict`
- `result`: pass
- `notes`: strict PHPStan passes after tightening iterable types and default password-hashing behavior

- `executed_at`: 2026-04-09 21:44 CEST
- `scope`: mutation execution after installing a repo-local coverage-driver fallback
- `environment`: local CLI PHP 8.4.11 with repo-local `pcov` loaded via `tooling/run-with-coverage-driver`
- `checks`: `php composer.phar mutation`
- `result`: fail
- `notes`: tooling succeeds and Infection completes; result is a real quality failure, not an environment failure:
  `312 mutations`, `MSI 30%`, `Mutation Code Coverage 50%`, `Covered Code MSI 59%`, `64 escaped`, `153 uncovered`,
  `1 error`, `1 timeout`

- `executed_at`: 2026-04-09 21:44 CEST
- `scope`: covered-only mutation diagnostic pass for critical auth slices
- `environment`: local CLI PHP 8.4.11 with repo-local `pcov` loaded via `tooling/run-with-coverage-driver`
- `checks`: `tooling/run-with-coverage-driver vendor/bin/infection --configuration=infection.json.dist --only-covered --show-mutations --threads=16`
- `result`: fail
- `notes`: coverage lane is clean and actionable; `613 mutations`, `Mutation Code Coverage 100%`, `MSI 61%`,
  `Covered Code MSI 61%`, `236 escaped`, `70 timeouts`; strongest surviving signals cluster in `Identity`,
  `JwtIdentity`, `ChangePassword`, `RefreshAuthentication`, `HmacTokenCodec`, and in-memory token stores

- `executed_at`: 2026-04-09 21:12 CEST
- `scope`: full PHPUnit suite after restoring the Composer toolchain and strict-review fixes
- `environment`: local CLI PHP 8.4.11 with local `composer.phar` and vendor dependencies installed
- `checks`: `php composer.phar test`
- `result`: pass
- `notes`: PHPUnit now runs end to end; result `OK (89 tests, 200 assertions)`

- `executed_at`: 2026-04-09 21:12 CEST
- `scope`: baseline static analysis after kernel, adapter, and test hardening
- `environment`: local CLI PHP 8.4.11 with vendor dependencies installed
- `checks`: `php composer.phar analyse`
- `result`: pass
- `notes`: baseline PHPStan passes on the package with restored autoload and optional adapter seams

- `executed_at`: 2026-04-09 21:12 CEST
- `scope`: strict static analysis after strict-review fixes
- `environment`: local CLI PHP 8.4.11 with vendor dependencies installed
- `checks`: `php composer.phar analyse:strict`
- `result`: pass
- `notes`: strict PHPStan passes after tightening header parsing, session cookie normalization, TOTP internals, and
  iterable type declarations

- `executed_at`: 2026-04-09 21:12 CEST
- `scope`: PHPUnit hygiene check for deprecations
- `environment`: local CLI PHP 8.4.11 with vendor dependencies installed
- `checks`: `vendor/bin/phpunit --display-phpunit-deprecations --display-deprecations`
- `result`: pass
- `notes`: no PHPUnit runtime or configuration deprecations were emitted after migrating `phpunit.xml.dist`

- `executed_at`: 2026-04-09 21:12 CEST
- `scope`: PHPUnit hygiene check for skipped tests and hidden errors
- `environment`: local CLI PHP 8.4.11 with vendor dependencies installed
- `checks`: `vendor/bin/phpunit --display-skipped --display-errors`
- `result`: pass
- `notes`: no tests were skipped; the optional Avax container adapter now executes through a test seam instead of
  being silently omitted

- `executed_at`: 2026-04-09 21:12 CEST
- `scope`: mutation verification for security-sensitive auth paths
- `environment`: local CLI PHP 8.4.11 with vendor dependencies installed
- `checks`: `php composer.phar mutation`
- `result`: partial
- `notes`: superseded by the 2026-04-09 21:44 CEST mutation runs after the repo-local `pcov` fallback was installed

- `executed_at`: 2026-04-09 19:21 CEST
- `scope`: syntax validation after kernel and integration boundary extraction
- `environment`: local CLI PHP 8.4.11 without Composer installed
- `checks`: `find System integrations tests -name '*.php' -print0 | xargs -0 -n1 php -l`
- `result`: pass
- `notes`: kernel, optional adapters, and moved tests all parsed successfully after extracting `integrations/`

- `executed_at`: 2026-04-09 19:21 CEST
- `scope`: executable kernel and integration smoke path without Composer
- `environment`: local CLI PHP 8.4.11 with inline PSR-4 autoloader for `System/` and `integrations/`
- `checks`: inline PHP smoke covering kernel-only auth assembly, JWT login, HTTP ingress mapping, and safe HTTP failure
  mapping
- `result`: pass
- `notes`: emitted `kernel-integration-smoke-ok`

- `executed_at`: 2026-04-09 19:21 CEST
- `scope`: adapter isolation verification
- `environment`: local CLI shell
- `checks`: `rg -n -F 'Avax\\\\Auth\\\\Integrations\\\\' System`
- `result`: pass
- `notes`: emitted `kernel-does-not-import-integrations`; kernel source has no import dependency on the integration lane

- `executed_at`: 2026-04-09 18:10 CEST
- `scope`: syntax validation after MFA subsystem refactor
- `environment`: local CLI PHP 8.4.11
- `checks`: `find System tests -name '*.php' -print0 | xargs -0 -n1 php -l`
- `result`: pass
- `notes`: all source and test files parsed successfully after MFA slice implementation

- `executed_at`: 2026-04-09 18:10 CEST
- `scope`: static analysis of core auth kernel after MFA subsystem refactor
- `environment`: local CLI PHP 8.4.11 with vendor autoload
- `checks`: `vendor/bin/phpstan analyse System --memory-limit=1G -c phpstan.neon`
- `result`: pass
- `notes`: `System/` passes PHPStan with the new MFA slice, `mfaVerifiedAt` auth claims, and builder wiring

- `executed_at`: 2026-04-09 18:10 CEST
- `scope`: executable MFA lifecycle smoke path
- `environment`: local CLI PHP 8.4.11 with vendor autoload
- `checks`: inline PHP smoke covering MFA enrollment, mfa-required login, backup-code verification, change-password
  step-up, refresh preservation, backup-code replay failure, recovery reset, and post-recovery login
- `result`: pass
- `notes`: emitted `mfa-smoke-ok`

- `executed_at`: 2026-04-09 18:10 CEST
- `scope`: executable MFA security smoke path
- `environment`: local CLI PHP 8.4.11 with vendor autoload
- `checks`: inline PHP smoke covering repeated MFA failure lockout and stale fresh-MFA rejection
- `result`: pass
- `notes`: emitted `mfa-security-smoke-ok`

- `executed_at`: 2026-04-09 15:31 CEST
- `scope`: syntax validation for `System/` and `tests/`
- `environment`: local CLI PHP 8.4.11 without `dom/xml/xmlwriter/mbstring`
- `checks`: `find System tests -name '*.php' -print0 | xargs -0 -n1 php -l`
- `result`: pass
- `notes`: all PHP sources parsed successfully after auth-kernel refactor

- `executed_at`: 2026-04-09 15:31 CEST
- `scope`: static analysis of core auth kernel
- `environment`: local CLI PHP 8.4.11 with vendor installed via ignored XML platform requirements
- `checks`: `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`
- `result`: pass
- `notes`: `System/` passes PHPStan level 5; optional Avax container adapter is excluded from kernel analysis because
  the dependency is not installed locally

- `executed_at`: 2026-04-09 15:31 CEST
- `scope`: executable auth smoke path
- `environment`: local CLI PHP 8.4.11 with vendor autoload
- `checks`: inline PHP smoke covering register, login, authenticateRequest, changePassword, refresh rotation, MFA
  enable/challenge/verify, email verification, password reset, logout
- `result`: pass
- `notes`: emitted `smoke-ok`

- `executed_at`: 2026-04-09 15:31 CEST
- `scope`: executable security smoke path
- `environment`: local CLI PHP 8.4.11 with vendor autoload
- `checks`: inline PHP smoke covering refresh-token reuse detection and password-reset anti-enumeration behavior
- `result`: pass
- `notes`: emitted `security-smoke-ok`
