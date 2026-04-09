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
