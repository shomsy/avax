# TODO-006 Slice C Merge Validation

Date: 2026-05-20
Branch: main
Merge commit: 18eef0744 merge(architecture): integrate TODO-006 App delegation slice C
Status: MERGE_READY_WITH_ACCEPTED_YELLOW

## Scope

Slice C moved remaining `App` public entrypoint runtime construction behind explicit collaborators:

- `FrameworkRouteRegistrar`
- `OpenHttpRequestScope`
- `CloseHttpRequestScope`
- `CreateRuntimeRequestFromHttpRequest`

The public `App` entrypoint now delegates framework route registration, request-scope lifecycle, and runtime request conversion instead of constructing those runtime/internal objects directly.

## Post-Merge Validation

| Command | Result | Classification |
| --- | --- | --- |
| `composer validate --no-check-publish` | `./composer.json is valid` | GREEN |
| `composer dump-autoload -o` | Optimized autoload generated; pre-existing `xhp_` PSR-4 warning remains | ACCEPTED_YELLOW |
| `vendor/bin/phpunit --filter "AppTest\|CreateApplicationTest\|BootDslTest\|V4AppDoesNotDuplicateComponentsTest" --no-coverage` | OK, 114 tests, 242 assertions | GREEN |
| `vendor/bin/phpstan analyse <slice-c changed files/tests> --memory-limit=1G` | No errors | GREEN |
| `php tooling/refactor/check-public-surface.php` | PASS | GREEN |
| `php tooling/refactor/check-direct-instantiation.php` | FAIL globally; `framework/System/PublicSurface/App.php` is absent from findings | ACCEPTED_YELLOW |
| `php tooling/refactor/check-runtime-composition-leaks.php` | FAIL with four pre-existing HIGH findings outside Slice C | ACCEPTED_YELLOW |
| `php tooling/refactor/check-namespace-drift.php` | PASS | GREEN |
| `php tooling/governance/check-governance-index-current.php` | GREEN | GREEN |
| `php tooling/governance/check-root-evidence-hygiene.php` | GREEN | GREEN |
| `git diff --check` | no output | GREEN |

## Remaining Accepted Yellow

- `composer dump-autoload -o` still reports the pre-existing `xhp_` PSR-4 warning for `framework/System/Foundation/compat.php`.
- `check-direct-instantiation.php` still reports the broader existing backlog. Slice C removed the changed public entrypoint file `framework/System/PublicSurface/App.php` from the output.
- `check-runtime-composition-leaks.php` still reports four pre-existing HIGH findings:
  - `components/DataStack/Database/System/Capabilities/Migrations/Migrations.php:174`
  - `components/DataStack/Database/System/Capabilities/Migrations/CLI/SeederCommand.php:31`
  - `components/DataStack/Database/System/Capabilities/Migrations/SeedDatabase/Seeder.php:25`
  - `components/Application/Container/System/Capabilities/Providers/ProviderRegistry.php:26`

## Changed-File Impact

No new changed-file failure was introduced by the Slice C merge.

`App` and `BootDsl` are no longer direct-instantiation findings in the public entrypoint object-graph scope. Remaining TODO-006 analysis must decide whether `framework/System/PublicSurface/Avax.php` public entrypoint assembly is remediated in a next slice or accepted with explicit evidence.

## Decision

Slice C is merged and validated with accepted yellow. TODO-006 remains PARTIAL until the remaining public entrypoint object-graph scope, especially `Avax`, is inventoried and either remediated or explicitly classified.
