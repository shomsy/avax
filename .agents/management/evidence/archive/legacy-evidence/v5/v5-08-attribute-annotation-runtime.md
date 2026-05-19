# V5-08 Evidence: Attribute/Annotation Runtime

**Date:** 2026-05-11
**Status:** GREEN_BY_EVIDENCE

## What Was Implemented

### 1. CompiledAttributeMetadata Model

**File:** `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/CompiledAttributeMetadata.php`

- Serializable metadata model with metadata version (v1), format identifier (`attribute-metadata`), config hash
- Captures class-level, property-level, and method-level attributes
- Checksum validation (SHA-256 over JSON body)
- `fromArray()`, `toArray()`, `toJson()`, `isValidForConfig()`, `hasVersionMismatch()`, `isChecksumValid()`

### 2. CompileClassAttributes — Disk Compilation

**File:** `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/CompileClassAttributes.php`

- Compiles all attributes on a class (class-level, property-level, method-level) to disk-based JSON metadata
- Atomic writes (temp file + rename pattern)
- Corruption detection: format mismatch, version mismatch, checksum mismatch → quarantine
- Source-change invalidation via file mtime tracking
- Quarantine of corrupt artifacts with timestamped names
- `compile()`, `compileMany()`, `loadMetadata()`, `hasSourceChanged()`

### 3. AttributeCompiler — 2-Tier Resolution Orchestrator

**File:** `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/AttributeCompiler.php`

- Tier 1: Compiled disk metadata (if configured + valid + not stale)
- Tier 2: Live reflection fallback (caller handles null return)
- Static in-memory cache with `reset()` for long-lived worker safety
- `resolve()`, `compile()`, `compileMany()`, `hasCompiled()`, `reset()`

### 4. ORM AttributeMetadataReader Wired to Compiled Metadata

**Modified file:** `components/DataStack/Database/System/Capabilities/ORM/Metadata/AttributeMetadataReader.php`

- Constructor accepts optional `?AttributeCompiler`
- `read()` tries compiled metadata first, falls back to reflection
- `readFromCompiled()` reconstructs EntityMetadata from compiled attribute data
- `readFromReflection()` preserves original behavior (backward compatible)

### 5. Tests

**New file:** `tests/Unit/Components/DataStack/DataTransfer/CompiledAttributeMetadataTest.php` — 25 tests

- CompiledAttributeMetadata model: constants, fromArray, toArray, toJson, validation, checksum
- CompileClassAttributes: class/property extraction, disk writes, loading, config mismatch, many compile, quarantine
- AttributeCompiler: resolve without compiler, resolve with compiled, in-memory caching, compile many, reset, error
  handling

## Attribute Inventory

43 PHP 8 attributes discovered across 5 lanes:

| Lane                    | Component Area                                 | Count | Examples                                                                                          |
|-------------------------|------------------------------------------------|-------|---------------------------------------------------------------------------------------------------|
| DataTransfer Validation | `DataStack/DataTransfer/.../AttributeReading/` | 19    | Required, Optional, MapFrom, CastWith, ListOf, Min, Max, Between, Email, StringType, Hidden       |
| App Validation          | `Application/Validation/.../Attributes/`       | 10    | Required, Email, Min, MinLength, PasswordComplexity, IntegerRule, EmailRule                       |
| ORM                     | `DataStack/Database/.../ORM/Attributes/`       | 10    | Entity, Table, Column, Id, GeneratedValue, ManyToOne, OneToMany, OneToOne, ManyToMany, JoinColumn |
| DI/Container            | `Application/Container/.../Attributes/`        | 3     | Inject, RuntimeInput, Singleton                                                                   |
| Query Projections       | `DataStack/Database/.../Query/Projections/`    | 1     | Column (projection-specific)                                                                      |

## Hot-Path Reflection Status

| Consumer                      | Before V5-08                            | After V5-08                               |
|-------------------------------|-----------------------------------------|-------------------------------------------|
| DataTransfer CreateDataObject | Per-request reflection                  | Compiled via V5-06 DataShapeCompiler      |
| ORM AttributeMetadataReader   | Per-class reflection (cached in-memory) | Compiled disk metadata + in-memory cache  |
| App Validation ValidateDto    | Per-request reflection                  | Not yet compiled (narrow scope)           |
| Container Blueprint Creation  | One-time reflection at boot             | Not compiled (boot-time only, acceptable) |
| Query ResultMapper            | Per-query reflection                    | Not yet compiled (narrow scope)           |

## Validation Evidence

| Command                                                                   | Result                               |
|---------------------------------------------------------------------------|--------------------------------------|
| `composer validate --no-check-publish`                                    | PASS                                 |
| `composer dump-autoload -o`                                               | PASS                                 |
| `vendor/bin/phpunit --no-coverage`                                        | 7607 tests, 21962 assertions — GREEN |
| `vendor/bin/phpstan analyse framework components tests --memory-limit=1G` | 0 errors — GREEN                     |
| `php tooling/refactor/check-component-suite-structure.php`                | PASS                                 |
| `php tooling/refactor/check-duplicate-owners.php`                         | PASS                                 |
| `php tooling/refactor/check-namespace-drift.php`                          | PASS                                 |
| `php tooling/refactor/check-public-surface.php`                           | PASS                                 |
| `php tooling/refactor/check-runtime-leaks.php`                            | PASS                                 |
| `php tooling/refactor/check-component-canonical-shape.php`                | GREEN                                |
| `php tooling/refactor/check-advanced-pattern-folder-violations.php`       | GREEN                                |
| `php tooling/security/check-security-blockers.php`                        | PASS                                 |

## Remaining Risk

- App Validation `ValidateDto` still reads attributes per-request (narrow scope, not hot-path critical)
- Query `ResultMapper` still reads attributes per-query (narrow scope)
- No CLI command for attribute warmup yet
- No `#[Route]`, `#[Controller]`, `#[Validate]`, `#[Audit]`, `#[Trace]`, `#[Transactional]`, `#[MessageConsumer]`
  attributes exist in the framework (not gaps — they simply don't exist yet)
