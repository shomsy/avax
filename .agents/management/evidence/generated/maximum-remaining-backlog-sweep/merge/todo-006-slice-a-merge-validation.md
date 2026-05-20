# TODO-006 Slice A Merge Validation

## Merge

- Branch merged: `architecture/todo-006-framework-entrypoint-object-graph`
- Merge commit: `merge(architecture): integrate TODO-006 framework entrypoint assembly slice A`
- Main validation location: `/home/shomsy/projects/avax`
- Push status: not pushed

## Commands

### Composer Validate

```bash
composer validate --no-check-publish
```

Result:

```text
./composer.json is valid
```

Status: GREEN.

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

Status: GREEN_WITH_PRE_EXISTING_WARNING.

### Focused PHPUnit

```bash
vendor/bin/phpunit --filter "AvaxCreateTest|CreateApplicationTest|AppTest|BootDslTest|V4AppDoesNotDuplicateComponentsTest" --no-coverage
```

Result:

```text
OK (122 tests, 234 assertions)
```

Status: GREEN.

### Broad PHPStan

```bash
vendor/bin/phpstan analyse framework tests --memory-limit=1G
```

Result:

```text
[ERROR] Found 64 errors
```

Status: PRE_EXISTING_YELLOW.

Changed-file proof:

```bash
vendor/bin/phpstan analyse framework/System/Configuration/Builders/BuildRunApplication.php framework/System/Configuration/BootDsl/BootDslEngine.php framework/System/Flows/CreateApplication/CreateApplication.php framework/System/Flows/RunApplication/RunApplication.php framework/System/PublicSurface/App.php tests/Contract/V4Architecture/V4AppDoesNotDuplicateComponentsTest.php --memory-limit=1G
```

Result:

```text
[OK] No errors
```

Status: GREEN.

### Public Surface

```bash
php tooling/refactor/check-public-surface.php
```

Result:

```text
PASS
```

Status: GREEN.

### Direct Instantiation

```bash
php tooling/refactor/check-direct-instantiation.php
```

Result:

```text
FAIL
```

Relevant TODO-006 residual findings remain:

```text
framework/System/PublicSurface/App.php:199
framework/System/PublicSurface/App.php:203
framework/System/PublicSurface/App.php:282
framework/System/PublicSurface/App.php:316
framework/System/PublicSurface/BootDsl.php:150
framework/System/PublicSurface/BootDsl.php:151
framework/System/PublicSurface/BootDsl.php:155
framework/System/PublicSurface/BootDsl.php:156
framework/System/PublicSurface/BootDsl.php:159
framework/System/PublicSurface/BootDsl.php:164
```

Status: ACCEPTED_YELLOW / TODO-006_REMAINDER.

### Runtime Composition Leaks

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

Status: PRE_EXISTING_YELLOW / OUT_OF_SCOPE_FOR_SLICE_A.

### Namespace Drift

```bash
php tooling/refactor/check-namespace-drift.php
```

Result:

```text
PASS
```

Status: GREEN.

### Governance Index

```bash
php tooling/governance/check-governance-index-current.php
```

Result:

```text
GREEN: Governance index is current.
```

Status: GREEN.

### Root Evidence Hygiene

```bash
php tooling/governance/check-root-evidence-hygiene.php
```

Result:

```text
GREEN: Root evidence hygiene PASSED.
```

Status: GREEN.

## Decision

TODO-006 Slice A merge status: MERGED_WITH_ACCEPTED_YELLOW.

No new changed-file blocker was found after merge.

TODO-006 remains PARTIAL. Next allowed action is Slice B analysis from latest `main`.
