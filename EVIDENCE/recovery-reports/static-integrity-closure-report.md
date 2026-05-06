# Static Integrity Closure Report - V1-03 Validation

Date: 2026-05-06
Stage: V1-03 - Static Integrity Closure
Status: RED / VALIDATION FAILING

## Scope

This continuation performed V1-03 validation only.

No V2, V3, V4, MigrationRunner, new framework feature, broad cleanup, or optimistic truth update was performed.

All captured command output is under:

`EVIDENCE/recovery-reports/v1-03-validation/`

## Minimal Repairs During Validation

Two validation-blocking static integrity drifts were repaired before rerunning the affected gate:

- `components/compat.php`
  - corrected the stale `AbstractDTO` namespace alias to the existing DataTransfer foundation class
  - converted two missing alias keys to string literal legacy names so class constants are not evaluated for missing classes
- `phpunit.xml`
  - replaced stale missing suite directories with the existing `tests` directory so PHPUnit can execute the real suite

These are V1-03 static/test-configuration repairs only.

## Commands Run And Exit Codes

| Order | Command | Latest log | Exit | Result |
| --- | --- | --- | ---: | --- |
| 1 | `composer validate --no-check-publish` | `01-composer-validate.log` | 0 | PASS |
| 2 | `composer dump-autoload -o` | `02-composer-dump-autoload.log` | 0 | PASS |
| 3 | `php tooling/audit_broken_refs.php` | `03b-broken-refs-audit-after-compat.log` | 0 | PASS |
| 4 | `php tooling/refactor/categorize-broken-refs.php` | `04b-broken-refs-categorize-after-compat.log` | 0 | PASS |
| 5 | `php tooling/refactor/check-component-suite-structure.php` | `05-check-component-suite-structure.log` | 0 | PASS |
| 6 | `php tooling/refactor/check-duplicate-owners.php` | `06-check-duplicate-owners.log` | 0 | PASS |
| 7 | `php tooling/refactor/check-namespace-drift.php` | `07-check-namespace-drift.log` | 0 | PASS |
| 8 | `php tooling/refactor/check-public-surface.php` | `08-check-public-surface.log` | 0 | PASS |
| 9 | `php tooling/refactor/check-runtime-leaks.php` | `09-check-runtime-leaks.log` | 0 | PASS |
| 10 | `php avax runtime:doctor` | `10-runtime-doctor.log` | 0 | PASS |
| 11 | `vendor/bin/phpunit --no-coverage` | `11b-phpunit-no-coverage-after-config.log` | 2 | FAIL |
| 12 | `vendor/bin/phpstan analyse framework/System --memory-limit=1G --error-format=raw --no-progress` | `12-phpstan-framework-system.log` | 1 | FAIL |
| 13 | `vendor/bin/phpstan analyse components/Application/Cache --memory-limit=1G --error-format=raw --no-progress` | `13-phpstan-application-cache.log` | 1 | FAIL |
| 14 | `vendor/bin/phpstan analyse components/HTTP/Request components/HTTP/Response --memory-limit=1G --error-format=raw --no-progress` | `14-phpstan-http-request-response.log` | 1 | FAIL |
| 15 | `vendor/bin/phpstan analyse components/DataStack/Database --memory-limit=1G --error-format=raw --no-progress` | `15-phpstan-datastack-database.log` | 1 | FAIL |

Additional captured reruns:

- `03-broken-refs-audit.log`: pre-compat-fix broken ref audit, exit 0.
- `04-broken-refs-categorize.log`: pre-compat-fix categorization, exit 0.
- `11-phpunit-no-coverage.log`: pre-phpunit-config-fix PHPUnit run, exit 2, blocked by missing configured test directory.

## Pass Fail Summary

Passing gates:

- Composer validation
- Composer optimized autoload generation
- Broken reference audit command
- Broken reference categorization command
- Component suite structure checker
- Duplicate owners checker
- Namespace drift checker
- Public surface checker
- Runtime leaks checker
- Runtime doctor

Failing gates:

- PHPUnit
- PHPStan: `framework/System`
- PHPStan: `components/Application/Cache`
- PHPStan: `components/HTTP/Request components/HTTP/Response`
- PHPStan: `components/DataStack/Database`

## Broken Reference Counts

Latest audit summary from `03b-broken-refs-audit-after-compat.log`:

- Defined: 3442
- Missing: 214
- Audit CRITICAL: 124
- Audit MINOR: 90

Latest categorization from `EVIDENCE/v1-integrity/broken-reference-groups.md`:

- TEST-ONLY: 71
- DOCS-ONLY: 0
- NON-PRODUCTION: 138
- VENDOR-EXTERNAL: 5
- STALE-NAMESPACE: 0
- REAL-PRODUCTION: 0

Current CRITICAL production refs count:

- REAL-PRODUCTION: 0

## Autoload Result

`composer dump-autoload -o` generated optimized autoload files containing 6520 classes.

Current PSR-4 skip count:

- 0

No `does not comply`, `Skipping`, or PSR-4 skip diagnostics were present in `02-composer-dump-autoload.log`.

## Runtime Doctor Result

`php avax runtime:doctor` passed.

Summary:

- Mode: development
- No runtime safety issues detected.

## PHPUnit Result

Latest run:

- Command: `vendor/bin/phpunit --no-coverage`
- Log: `11b-phpunit-no-coverage-after-config.log`
- Exit: 2
- Tests: 213
- Assertions: 323
- Errors: 47
- Failures: 3
- Skipped: 1

Primary failure families:

- Runtime named-argument drift: `$builder`, `$store`, `$requestScopes`, `$id`, `$config`, `$maxConcurrent`.
- Missing legacy or external classes in tests: `Nyholm\Psr7\Factory\Psr17Factory`, `Avax\Components\Performance\System\PublicSurface\Performance`, `Avax\Components\Server\System\Capabilities\PhpBuiltInServer`, `Avax\Components\Operations\Tasks\System\Capabilities\Queue\Queue`, `Avax\Components\Operations\Resilience\System\Capabilities\Fallback\System\PublicSurface\Fallback`, `Avax\Components\Security\System\System\PublicSurface\Security`, `Avax\Components\Application\Container\Core\AppFactory`.
- Route and filesystem API drift: `FrameworkRouteRegistrar` type mismatch, missing `Presentation/HTTP/routes/web.routes.php`, undefined `LocalStorageAdapter::put()` and `LocalStorageAdapter::url()`.
- Architecture failures: duplicate class/interface definition assertions are still red in PHPUnit.

## Targeted PHPStan Results

`framework/System`

- Exit: 1
- Error lines: 68
- Main blockers:
  - missing ResourceGovernance, GracefulShutdown, and StatelessBoundary dependencies
  - `BootApplication` / `BuildApplicationState` named parameter drift
  - `Runtime` constructor named parameter drift
  - ContainerAnalyzer constructor and return-type drift
  - component manifest discovery/listing drift
  - route intelligence iterable type drift
  - console command typing/readonly errors

`components/Application/Cache`

- Exit: 1
- Error lines: 1880
- Main blockers:
  - public facade named-argument drift around `cacheKey` vs `key`
  - provider mixed container typing and iterable value types
  - unknown `Psr\SimpleCache\CacheInterface`
  - `CacheResult`, compiled cache freshness, manifest, and target constructor drift
  - component-local test named-argument errors against PHPUnit no-named-arguments APIs

`components/HTTP/Request components/HTTP/Response`

- Exit: 1
- Error lines: 163
- Main blockers:
  - missing iterable value types
  - PSR method signature drift where parent parameter types were removed
  - parsed body and request data type mismatches
  - final failure classes being extended
  - response constructor named-parameter drift around `body`
  - `ResponseProvider` missing required component provider methods

`components/DataStack/Database`

- Exit: 1
- Error lines: 648
- Main blockers:
  - missing iterable value types
  - connection pool constructor and named-argument drift
  - pool inheritance and override drift
  - stale `AbstractDTO` namespace in `ConnectionPoolMetrics`
  - database provider classes missing required `boot()` method
  - transaction, migration, telemetry, and public surface method drift

## Remaining Blockers

V1-03 is not green.

Current blockers:

- PHPUnit is red after real suite execution.
- All targeted PHPStan areas are red.
- Duplicate owner architecture assertions still fail in PHPUnit, even though the standalone duplicate-owner checker exits 0.
- Broken-reference audit still reports 214 total missing references, but current production categorization has 0 REAL-PRODUCTION refs.
- `CURRENT_TRUTH.md` must not be upgraded.

## Next Allowed Action

Continue Stage V1-03 only.

Exact next smallest repair step:

Repair the first runtime named-argument drift proven by both PHPUnit and PHPStan: align `Avax\Framework\System\Flows\BootApplication\BuildApplicationState::build()` with its callers around `builder` vs `applicationBuilder`, then rerun:

```bash
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework/System --memory-limit=1G --error-format=raw --no-progress
```

No V2/V3 implementation, no MigrationRunner work, no skeleton classes, and no broad cleanup are allowed until V1-03 validation is green.
