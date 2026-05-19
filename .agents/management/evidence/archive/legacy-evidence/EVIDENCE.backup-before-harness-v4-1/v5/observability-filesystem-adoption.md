# Part 3: Migrate Logging/Observability File Writers to Filesystem

**Status:** GREEN
**Date:** 2026-05-10

## Summary

Migrated all Logging and Observability file writers to use `Application/Filesystem` as the canonical owner of file I/O.

## Components Migrated

### 1. RotatingFileWriter

- **Path:** `components/Operations/Logging/System/Capabilities/Writers/RotatingFileWriter.php`
- **Changes:** Uses `Filesystem` for `exists()`, `createDirectory()`, `append()`, `delete()`, `listDirectory()`
- **Rotation:** Uses `listDirectory()` + native `filemtime()` on listed paths (metadata query, not raw I/O)
- **Constructor:** Accepts optional `?Filesystem`

### 2. FileAuditWriter

- **Path:** `components/Operations/Observability/System/Capabilities/Audit/FileAuditWriter/FileAuditWriter.php`
- **Changes:** Uses `Filesystem::append()`, `Filesystem::read()`, `Filesystem::write()`, `Filesystem::exists()`
- **Redaction:** Preserved before all writes

### 3. FileMetricWriter

- **Path:**
  `components/Operations/Observability/System/Capabilities/MetricsCollector/FileMetricExporter/FileMetricWriter.php`
- **Changes:** Uses `Filesystem::append()`, `Filesystem::read()`, `Filesystem::write()`, `Filesystem::exists()`
- **Redaction:** Preserved before all writes

### 4. FileTraceWriter

- **Path:** `components/Operations/Observability/System/Capabilities/Tracing/FileTraceExporter/FileTraceWriter.php`
- **Changes:** Uses `Filesystem::append()`, `Filesystem::read()`, `Filesystem::write()`, `Filesystem::exists()`
- **Redaction:** Preserved before all writes

### 5. FileLogWriter

- **Path:** `components/Operations/Observability/System/Capabilities/Logging/FileLogWriter.php`
- **Changes:** Uses `Filesystem::exists()` and `Filesystem::createDirectory()` for directory creation
- **Tradeoff:** Keeps native `fopen()`/`fwrite()` for batch write performance (handle stays open)
- **Constructor:** Accepts optional `?Filesystem`

## Self-Healing: CreateDirectory Bug Fix

- **Path:** `components/Application/Filesystem/System/Flows/CreateDirectory/CreateDirectory.php`
- **Bug:** `mkdir()` used `recursive: false`, failing nested directory creation
- **Fix:** Changed to `recursive: true`
- **Impact:** `FileLogWriterTest::creates_directory_if_missing` now passes

## Validation

- All Queue tests (114) pass
- All Logging/Observability tests (482) pass
- PHPStan clean on all touched files

## Dogfooding

All 5 writers now dogfood `Application/Filesystem` for file I/O operations, reducing raw file operation violations.
