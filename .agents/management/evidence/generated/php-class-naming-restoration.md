# PHP Class Naming Restoration

**Date:** 2026-05-19
**Status:** GREEN

## Problem

During the `tooling/Refactor` → `tooling/refactor` migration, PHP class names were incorrectly renamed from PascalCase to snake_case to match kebab-case filenames.

This violated PHP ecosystem conventions and PSR expectations.

## Convention Clarified

| Aspect | Convention | Example |
|---|---|---|
| Filename | kebab-case | `autoload-fixer.php` |
| PHP class | PascalCase | `AutoloadFixer` |
| Namespace | PascalCase segments | `Avax\Tooling\Refactor` |

The filesystem uses kebab-case for portability and consistency with other tooling directories.
PHP types use PascalCase per PSR-1 and ecosystem convention.
These are independent concerns — filename ≠ class name.

## Files Fixed

| File | Before (snake_case) | After (PascalCase) |
|---|---|---|
| `autoload-fixer.php` | `autoload_fixer` | `AutoloadFixer` |
| `ensure-psr4-namespaces.php` | `ensure_psr4_namespaces` | `EnsurePsr4Namespaces` |
| `finish-stage02-taxonomy.php` | `finish_stage02_taxonomy` | `FinishStage02Taxonomy` |
| `fix-broken-imports.php` | `fix_broken_imports` | `FixBrokenImports` |
| `normalize-tooling.php` | `normalize_tooling` | `NormalizeTooling` |

Note: `namespace-reconstructor.php` has no class declaration (procedural script) — no change needed.

## Validation

| Check | Result |
|---|---|
| composer dump-autoload | 0 warnings |
| PHPUnit | 8458 tests, 0 failures |
| PHPStan | 0 errors |
| Runtime composition | PASS |

## Governance Principle

AvaX follows the PHP ecosystem standard:
- **Filesystem**: kebab-case for portability, consistency, and tooling compatibility
- **PHP types**: PascalCase for classes, interfaces, traits, and enums
- **Namespaces**: PascalCase segments matching directory structure

These conventions are orthogonal and must not be conflated.
