# Phase B Correction Validation

**Date:** 2026-05-15

## Validation Results

| Command                                                 | Result |                        Count | Blocks status? | Notes                         |
|---------------------------------------------------------|--------|-----------------------------:|---------------:|-------------------------------|
| `composer validate --no-check-publish`                  | GREEN  |                            1 |             NO | Valid                         |
| `composer dump-autoload -o`                             | GREEN  |                 9330 classes |             NO | 1 pre-existing warning (xhp_) |
| `vendor/bin/phpunit --no-coverage`                      | GREEN  | 8373 tests, 24066 assertions |             NO | 0 errors, 0 failures          |
| `vendor/bin/phpstan analyse framework components tests` | GREEN  |                     0 errors |             NO | Clean                         |
| `check-runtime-composition-leaks.php`                   | PASS   |                   3126 files |             NO | 0 findings                    |
| `check-component-runtime-assembly.php`                  | PASS   |                   3126 files |             NO | 0 findings                    |
| `check-public-surface.php`                              | PASS   |                          N/A |             NO | 0 findings                    |
| `check-hollow-public-surfaces.php`                      | PASS   |                    227 files |             NO | 0 findings                    |

## Changes Summary

- ApiVersion.php: Removed lazy `new VersionRegistry()`, added `setInstance()` + fail-if-not-configured, removed
  duplicate `ApiVersionResolved` inline class
- ApiVersionResolved.php: Kept as single source of truth
- ApiVersioningServiceProvider.php: NEW — registers VersionRegistry, wires into ApiVersion facade during boot
- Pipeline.php: Removed lazy `new HookRegistry()`, added `setInstance()` + fail-if-not-configured
- HookRegistry.php: MOVED from `PublicSurface/` to `Capabilities/PipelineHooks/`
- PipelineServiceProvider.php: Updated to import from new location, wires HookRegistry into Pipeline facade during boot
- check-runtime-composition-leaks.php: `isStaticFacadeFile()` now rejects facades with lazy `new` patterns
- 3 test files updated for provider-wired model
- GoldenPathTest.php: Updated to configure Pipeline facade in setUp
