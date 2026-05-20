# TODO-006 Slice A Validation Output

## Environment Note

The TODO-006 worktree initially had `vendor/autoload.php` but no `vendor/bin`. The first PHPUnit attempt failed with:

```text
/bin/bash: line 1: vendor/bin/phpunit: No such file or directory
```

Action taken:

```bash
composer install --no-interaction
```

Result: dependencies installed from `composer.lock`; no composer files were changed.

Several validation commands also needed escalation because the sandbox blocked the Docker socket before command execution. Escalated reruns are the authoritative validation results below.

## Required Commands

### Composer Validate

```bash
composer validate --no-check-publish
```

Result:

```text
./composer.json is valid
```

Classification: GREEN.

### Composer Dump Autoload

```bash
composer dump-autoload -o
```

Result:

```text
Generating optimized autoload files
Class xhp_ located in ./framework/System/Foundation/compat.php does not comply with psr-4 autoloading standard (rule: Avax\Framework\ => ./framework). Skipping.
Generated optimized autoload files containing 9367 classes
```

Classification: GREEN_WITH_PRE_EXISTING_WARNING.

### Focused PHPUnit

```bash
vendor/bin/phpunit --filter "AvaxCreateTest|CreateApplicationTest|AppTest|BootDslTest|V4AppDoesNotDuplicateComponentsTest" --no-coverage
```

Result:

```text
OK (122 tests, 234 assertions)
```

Classification: GREEN.

### Broad PHPStan

```bash
vendor/bin/phpstan analyse framework tests --memory-limit=1G
```

Result:

```text
[ERROR] Found 64 errors
```

Representative files:

```text
tests/HTTP/MiddlewareFailureReferenceTest.php
tests/Operations/SagaReferenceSemanticsTest.php
tests/Unit/Components/DataStack/Persistence/SqlInjection/CompileDataQueryIdentifierSafetyTest.php
tests/Unit/Components/HTTP/Security/SecurityShortcutsTest.php
tests/Unit/Operations/Queue/QueueWorkerSecurityTest.php
```

Classification: PRE_EXISTING_YELLOW.

Reason: no errors were reported in Slice A changed files.

### Changed-File PHPStan Proof

```bash
vendor/bin/phpstan analyse framework/System/Configuration/Builders/BuildRunApplication.php framework/System/Configuration/BootDsl/BootDslEngine.php framework/System/Flows/CreateApplication/CreateApplication.php framework/System/Flows/RunApplication/RunApplication.php framework/System/PublicSurface/App.php tests/Contract/V4Architecture/V4AppDoesNotDuplicateComponentsTest.php --memory-limit=1G
```

Result:

```text
[OK] No errors
```

Classification: GREEN.

### Public Surface Gate

```bash
php tooling/refactor/check-public-surface.php
```

Result:

```text
PASS
```

Classification: GREEN.

### Direct Instantiation Gate

```bash
php tooling/refactor/check-direct-instantiation.php
```

Result:

```text
FAIL
```

Relevant changed-file/residual findings on this branch:

```text
framework/System/PublicSurface/App.php:199 - Constructor default parameter instantiation
framework/System/PublicSurface/App.php:203 - Constructor default parameter instantiation
framework/System/PublicSurface/App.php:282 - Constructor default parameter instantiation
framework/System/PublicSurface/App.php:316 - Constructor default parameter instantiation
framework/System/PublicSurface/BootDsl.php:150 - Null-coalescing fallback instantiation
framework/System/PublicSurface/BootDsl.php:151 - Constructor default parameter instantiation
framework/System/PublicSurface/BootDsl.php:155 - Constructor default parameter instantiation
framework/System/PublicSurface/BootDsl.php:156 - Constructor default parameter instantiation
framework/System/PublicSurface/BootDsl.php:159 - Constructor default parameter instantiation
framework/System/PublicSurface/BootDsl.php:164 - Constructor default parameter instantiation
```

Comparison against clean `main`:

```text
framework/System/PublicSurface/App.php:207
framework/System/PublicSurface/App.php:211
framework/System/PublicSurface/App.php:290
framework/System/PublicSurface/App.php:337
framework/System/PublicSurface/BootDsl.php:150
framework/System/PublicSurface/BootDsl.php:151
framework/System/PublicSurface/BootDsl.php:155
framework/System/PublicSurface/BootDsl.php:156
framework/System/PublicSurface/BootDsl.php:159
framework/System/PublicSurface/BootDsl.php:164
```

Classification: ACCEPTED_YELLOW / TODO-006_REMAINDER.

Reason: Slice A did not add these findings. The `App.php` line numbers shifted because Slice A removed lazy dispatcher assembly. `BootDsl.php` was not changed in this slice.

### Runtime Composition Leak Gate

```bash
php tooling/refactor/check-runtime-composition-leaks.php
```

Result:

```text
FAIL
[HIGH] components/DataStack/Database/System/Capabilities/Migrations/Migrations.php:174 - Runtime class discovery (class_exists)
[HIGH] components/DataStack/Database/System/Capabilities/Migrations/CLI/SeederCommand.php:31 - Runtime class discovery (class_exists)
[HIGH] components/DataStack/Database/System/Capabilities/Migrations/SeedDatabase/Seeder.php:25 - Runtime class discovery (class_exists)
[HIGH] components/Application/Container/System/Capabilities/Providers/ProviderRegistry.php:26 - Runtime class discovery (class_exists)
```

Comparison against clean `main`: same four findings.

Classification: PRE_EXISTING_YELLOW / OUT_OF_SCOPE_FOR_SLICE_A.

### Namespace Drift Gate

```bash
php tooling/refactor/check-namespace-drift.php
```

Result:

```text
PASS
```

Classification: GREEN.

### Governance Index Gate

```bash
php tooling/governance/check-governance-index-current.php
```

Result:

```text
GREEN: Governance index is current.
```

Classification: GREEN.

### Root Evidence Hygiene Gate

```bash
php tooling/governance/check-root-evidence-hygiene.php
```

Result:

```text
GREEN: Root evidence hygiene PASSED.
```

Classification: GREEN.

### Diff Hygiene

```bash
git diff --check
```

Result: no output, exit code `0`.

Classification: GREEN.

## Validation Decision

Status: TODO_PARTIAL_WITH_YELLOW.

Slice A is validated for merge review:

- no changed-file PHPStan issue
- no new public-surface issue
- no namespace/governance/evidence hygiene issue
- focused public/API behavior tests pass
- direct-instantiation and runtime-composition failures are classified as pre-existing or remaining TODO-006 work

Full canonical validation was not run.
