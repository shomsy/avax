# Agents Backup Cleanup Validation Summary

Generated at: 2026-05-26 14:27:00 CEST

## Commands And Results

```bash
git diff --check
```

Result: PASS, no whitespace errors.

```bash
php tooling/governance/check-governance-canonical-truth.php
```

Result: PASS.

```text
GREEN: Governance canonical truth is coherent.
```

```bash
php tooling/governance/check-governance-leakage.php
```

Result: PASS.

```text
Files checked: 26
Files with leaks: 0
Total findings: 0
GREEN: No unapproved project leakage found in generic governance.
```

```bash
php tooling/governance/check-governance-index-current.php
```

Result: PASS.

```text
GREEN: Governance index is current.
```

```bash
php tooling/governance/check-root-evidence-hygiene.php
```

Result: PASS.

```text
Files: 3
Directories: 0
GREEN: Root evidence hygiene PASSED.
```

```bash
php tooling/governance/check-self-explaining-architecture.php --mode=changed
```

Result: PASS.

```text
Changed files: 801
Findings in changed scope: 14
Blocking findings: 0
GREEN — changed scope has no blocking findings for this gate.
```

```bash
php tooling/testing/check-shallow-tests.php --mode=changed
```

Result: PASS.

```text
Changed files: 801
Findings in changed scope: 0
Blocking findings: 0
GREEN — changed scope has no blocking findings for this gate.
```

```bash
composer dump-autoload -o
```

Result: PASS.

```text
Generated optimized autoload files containing 9497 classes
```

Known Composer warnings:

```text
Class Avax\Tests\Unit\Components\Identity\Auth\Login\RateLimitExceptionTest located in ./tests/Unit/Components/Identity/Auth/Login/RateLimit/RateLimitExceptionTest.php does not comply with psr-4 autoloading standard (rule: Avax\Tests\ => ./tests). Skipping.
Class xhp_ located in ./framework/System/Foundation/compat.php does not comply with psr-4 autoloading standard (rule: Avax\Framework\ => ./framework). Skipping.
```

These warnings are legacy autoload hygiene findings and were not introduced by the `.agents.backup*` cleanup.

```bash
php -l tooling/governance/check-root-evidence-hygiene.php
php -l tooling/governance/check-governance-canonical-truth.php
php -l tooling/governance/check-governance-leakage.php
php -l tooling/governance/check-self-explaining-architecture.php
php -l tooling/testing/check-shallow-tests.php
```

Result: PASS, no syntax errors detected.

```bash
vendor/bin/phpunit --no-coverage
```

Result: PASS.

```text
OK (9551 tests, 27380 assertions)
```

## Final Decision

Backup-folder cleanup is validated for changed scope and full PHPUnit.

