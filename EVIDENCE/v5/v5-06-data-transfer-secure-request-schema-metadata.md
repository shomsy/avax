# V5-06 Evidence: DataTransfer/SecureRequest/Schema Metadata Compilation

**Date:** 2026-05-11
**Status:** GREEN_BY_EVIDENCE

## What Was Implemented

### 1. CompiledSchemaMetadata Model
**File:** `components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/CompiledSchemaMetadata.php`
- Serializable metadata model with schema version (v1), format identifier, config hash, fingerprint
- Checksum validation (SHA-256 over JSON body)
- `fromArray()`, `toArray()`, `toJson()`, `isValidForConfig()`, `hasSchemaVersionMismatch()`, `isChecksumValid()`

### 2. CompileDataShapeSchema — Disk Compilation
**File:** `components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/CompileDataShapeSchema.php`
- Compiles DataShape inspections to disk-based JSON metadata
- Atomic writes (temp file + rename pattern)
- Corruption detection: format mismatch, schema version mismatch, checksum mismatch → quarantine
- Source-change invalidation via file mtime tracking
- Quarantine of corrupt artifacts with timestamped names

### 3. DataShapeCompiler — 3-Tier Resolution
**File:** `components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/DataShapeCompiler.php`
- Tier 1: Compiled disk metadata (if configured + valid + not stale)
- Tier 2: In-memory InspectDataShape cache
- Tier 3: Live ReadClassDataShape reflection (ultimate fallback)
- Static cache with `reset()` for long-lived worker safety
- Attribute deserialization: reconstructs attribute objects from serialized form

### 4. CreateDataObject Refactored to Use DataShape
**File:** `components/DataStack/DataTransfer/System/Flows/CreateDataObject/CreateDataObject.php`
- Constructor accepts optional `DataShapeCompiler`
- `create()` resolves `DataShape` via compiler instead of per-request reflection
- `hydrateInto()` resolves `DataShape` via compiler
- All reflection calls replaced with `DataField`/`DataFieldType` helpers:
  - `$field->isRequired()` instead of `$property->getAttributes(Required::class)`
  - `$field->inputName` instead of `$property->getAttributes(MapFrom::class)->newInstance()->name`
  - `$field->casterClass()` instead of `$property->getAttributes(CastWith::class)`
  - `$field->listItemClass()` instead of `$property->getAttributes(ListOf::class)`
  - `$field->dataFieldType` instead of `$property->getType()`
  - `$field->attributes` (pre-instantiated) instead of `$property->getAttributes()->newInstance()`
- All 41 existing CreateDataObjectTest tests pass unchanged

### 5. DataTransfer Warmup + Config
**Files:** `DataTransfer.php`, `DataTransferConfig.php`
- `DataTransfer::warmupSchemaCache(array $classes, ?string $cacheDir)` — production warmup
- `DataTransferConfig::$schemaCacheDir` — optional cache directory config
- `resetState()` now clears CacheDataShape and DataShapeCompiler caches

### 6. Tests
- `CompileDataShapeSchemaTest` — 14 tests: compilation, atomic write, corruption detection, quarantine, source-change invalidation, checksum validation, round-trip
- `DataShapeCompilerTest` — 7 tests: resolution from compiled metadata, fallback to InspectDataShape, corrupt fallback, source-change invalidation, static cache, reset
- `CreateDataObjectWithDataShapeTest` — 16 regression tests: all major CreateDataObject scenarios produce identical results with DataShapeCompiler

## Validation Evidence

| Command | Result |
|---------|--------|
| `vendor/bin/phpunit --no-coverage` | 7557 tests, 21876 assertions — GREEN |
| `vendor/bin/phpstan analyse framework components tests` | 0 errors — GREEN |
| `php tooling/security/check-security-blockers.php` | PASS |
| `php tooling/refactor/check-component-suite-structure.php` | PASS |
| `php tooling/refactor/check-duplicate-owners.php` | PASS |
| `php tooling/refactor/check-namespace-drift.php` | PASS |
| `php tooling/refactor/check-public-surface.php` | PASS |
| `php tooling/refactor/check-runtime-leaks.php` | PASS |
| `php tooling/refactor/check-component-canonical-shape.php` | GREEN |
| `php tooling/refactor/check-advanced-pattern-folder-violations.php` | GREEN |

## Hot-Path Reflection Elimination

Before V5-06, `CreateDataObject::create()` performed these reflection calls per request:
- `new ReflectionClass($class)` — every call
- `$property->getAttributes(Required::class)` — every property
- `$property->getAttributes(Optional::class)` — every property
- `$property->getAttributes(DefaultValue::class)` — every missing property
- `$property->getAttributes(MapFrom::class)` — every property
- `$property->getAttributes(CastWith::class)` — every non-null value
- `$property->getAttributes(ListOf::class)` — every non-null value
- `$property->getType()` — every non-null value
- `$property->getAttributes()` + `$attribute->newInstance()` — every non-null value (validation)

After V5-06, all metadata is resolved once via DataShapeCompiler (compiled → cached → reflection fallback).
Subsequent calls use pre-compiled `DataField` objects with instantiated attributes and type info.

## Remaining Risk

- Compiled disk metadata is not yet used in the hot path by default (requires explicit `schemaCacheDir` config + warmup). The in-memory cache (`InspectDataShape` + `CacheDataShape`) is always active.
- No CLI command for warmup yet (`cache:warm` or `metadata:compile`).
- ValueCasterInterface full integration (DataField + ValueConversionContext) not yet wired into hot path.
