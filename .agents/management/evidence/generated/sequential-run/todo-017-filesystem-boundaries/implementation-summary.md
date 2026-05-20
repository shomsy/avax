# Implementation Summary

## Problem

Several production code paths used raw PHP filesystem functions (`filemtime`, `file_exists`, `file_get_contents`, `is_file`, `is_dir`, `mkdir`, `file_put_contents`) instead of the first-party Filesystem component boundary.

## Changes

### 1. FileSessionStore::gc() — HIGH (line 99)
- Replaced raw `filemtime($file)` with `$this->filesystem->modificationTime($file)`
- Class already had Filesystem injected and used it for 10+ other operations

### 2. WriteCompiledFailurePolicies — MEDIUM (lines 30-34)
- Injected `Filesystem $filesystem` into constructor
- Replaced `is_dir()` → `$this->filesystem->isDirectory()`
- Replaced `mkdir()` → `$this->filesystem->createDirectory()`
- Replaced `file_put_contents(..., LOCK_EX)` → `$this->filesystem->write()` (WriteFile flow already uses LOCK_EX)

### 3. ReadCompiledFailurePolicies — MEDIUM (lines 25-29)
- Injected `Filesystem $filesystem` into constructor
- Replaced `file_exists()` → `$this->filesystem->exists()`
- Replaced `file_get_contents()` → `$this->filesystem->read()`

### 4. BuildDispatchConfiguredRoute — MEDIUM (line 46)
- Added optional `Filesystem|null $filesystem = null` constructor parameter
- Replaced raw `is_file()` with `$filesystem->isFile()`
- Backward compatible: defaults to `new Filesystem()` if none provided

## Files Changed

| File | Change |
|------|--------|
| components/HTTP/Session/System/Capabilities/Storage/FileSessionStore.php | filemtime → Filesystem::modificationTime |
| framework/System/Capabilities/FailureBoundary/Capabilities/WriteCompiledFailurePolicies/WriteCompiledFailurePolicies.php | Injected Filesystem, replaced 3 raw ops |
| framework/System/Capabilities/FailureBoundary/Capabilities/ReadCompiledFailurePolicies/ReadCompiledFailurePolicies.php | Injected Filesystem, replaced 2 raw ops |
| framework/System/Configuration/Builders/BuildDispatchConfiguredRoute.php | Added optional Filesystem, replaced is_file |
