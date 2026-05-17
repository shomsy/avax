# Raw File Operations Migration Report — V5 Dogfooding Closure Pass 01

**Date:** 2026-05-10
**Status:** MIGRATED — 3 priority production runtime violations fixed

## Migrations

### 1. BladeTemplateEngine.php — `unlink()` → `Filesystem::delete()`

**File:** `components/Presentation/View/System/Capabilities/Engines/BladeTemplateEngine.php:44`
**Before:** `unlink($compiledView)` for compiled view cache clearing
**After:** `Filesystem::delete(path: $compiledView)`
**Impact:** Compiled view cache deletion now goes through canonical Filesystem owner

### 2. DatabaseExporter.php — `mkdir()` + `file_put_contents()` → `Filesystem::write()`

**File:** `components/DataStack/Database/System/Capabilities/Migrations/ExportDatabase/DatabaseExporter.php`
**Before:**

- `mkdir($path, 0o755, true)` to create export directory
- `file_put_contents($fullPath, $output)` to write SQL dump
  **After:** `Filesystem::write(path: $fullPath, content: $output)`
  **Impact:** `Filesystem::write()` auto-creates parent directories, eliminating the need for explicit `mkdir()`. SQL
  dump writes now go through canonical Filesystem owner.

### 3. MigrationGenerator.php — `mkdir()` + `file_put_contents()` + `file_get_contents()` → `Filesystem::write()` +

`Filesystem::read()`

**File:** `components/DataStack/Database/System/Capabilities/Migrations/CreateMigration/MigrationGenerator.php`
**Before:**

- `mkdir($path, 0o755, true)` to create migration directory
- `file_put_contents($filepath, $content)` to write migration file
- `file_get_contents($stubPath)` to read stub template
  **After:**
- `Filesystem::write(path: $filepath, content: $content)` — auto-creates directories
- `Filesystem::read(path: $stubPath)` — reads stub template
  **Impact:** Migration file generation now uses canonical Filesystem for all I/O. Directory creation is handled by
  `Filesystem::write()`.

## Overall Status: MIGRATED (gate reduced 241 → 235)

These 3 priority production runtime files no longer appear in the raw file operations gate output. The gate still
reports ~235 violations from other production runtime paths (container compilation, observability writers, route cache,
etc.) that remain classified as NEEDS MIGRATION.

## Violations Removed

| Violation                                   | Before | After |
|---------------------------------------------|-------:|------:|
| `unlink()` in BladeTemplateEngine           |      1 |     0 |
| `mkdir()` in DatabaseExporter               |      1 |     0 |
| `file_put_contents()` in DatabaseExporter   |      1 |     0 |
| `mkdir()` in MigrationGenerator             |      1 |     0 |
| `file_put_contents()` in MigrationGenerator |      1 |     0 |
| `file_get_contents()` in MigrationGenerator |      1 |     0 |
| **Total**                                   |  **6** | **0** |

## Remaining Risk

The raw file operations gate still reports ~235 violations, but these are classified as:

- **Filesystem component internals** (canonical owner) — allowed
- **Container compilation/cache** — should migrate but not urgent
- **PreCommit/tooling** — allowed as tooling context
- **Observability file writers** — should migrate in future pass
- **Route cache, config setup, dev server** — lower priority

The 3 highest-priority production runtime violations (Blade cache, SQL export, migration generation) are now resolved.
